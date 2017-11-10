"use strict"
const co = require('co')
const _ = require('lodash')
const utils = require('./../../utils')
const fs = require('fs')
const path = require('path')
const moment = require('moment')



/**
 * Metodo principal
 * @param  {Object} application [aplicacion koa]
 * @param  {Object} objOrder    [objeto de orden de proceso]
 * @param  {Object} objProcess  [objeto de proceso]
 * @param  {Object} socket  	[socket de comunicacion]
 * @return {Object}             [Datos del proceso]
 */
module.exports = function*(application,objOrder,objProcess,socket){

	var errorManager = application.errorManager

	var quantityOutput = [['Año','Grupo Quinquenal']]
	var procedureOutput = [['Año','CUPS','Descripción del CUPS']]
	var medicineOutput = [['Año','CUMS/ATC']]
	var suppliesOutput = [['Año','Insumos']]

	let qGropus= [
		'Menor a 1',		
		'Entre 1 y 4',		
		'Entre 5 y 14',			
		'Entre 15 y 18 M',
		'Entre 15 y 18 H',			
		'Entre 19 y 44 M',
		'Entre 19 y 44 H',		
		'Entre 45 y 49',		
		'Entre 50 y 54',		
		'Entre 55 y 59',		
		'Entre 60 y 64',		
		'Entre 65 y 69',		
		'Entre 70 y 74',		
		'De 75 y más'
	]

	//inicializacion de datos locales
	var procedures = {}
	var result  = yield application.entities.health_procesedure.findAll({
		where:{
			type:'SUBCATEGORIA'
		},
		include:{all: true}
	})
	for(let procedure of result){
		procedures[procedure.code] = {
			description:procedure.description,
		}
	}

	let objQuantity,objProcedures,objMedicines,objSupplies,arrEpss

	try{		
		console.log(`Procesando orden de reporte de relacion de diagnostico ${objOrder.id}`)
		objProcess.status = 1
		objProcess.progress = 1
		objProcess = yield objProcess.save()

		objQuantity = {}
		objProcedures = {}
		objSupplies = {}
		objMedicines = {}

		let qualityNumbers = objOrder.meta.qualityNumbers

		if (!qualityNumbers) {
			qualityNumbers = []
		}

		let arrDiagnostics = objOrder.meta.diagnostics
		let periods = utils.compilePeriods(objOrder.meta.periods)
		
		let filters = {}
		if(objOrder.meta.epss != undefined){//En caso de que no se especifiquen epss
			filters.healthEntityCode = {$in:objOrder.meta.epss}
		}
		arrEpss = []//eps encontradas
		let percentageYear = 0
		let years = utils.getArrYears(objOrder.meta.periods)		
		console.log(years)

		for(let year of years){

			let collectionName = yield utils.getCollectionName(application.mongo,objOrder.meta.collection,year)
			let col = application.mongo.collection(collectionName)

			objOrder.emitToFront(socket,`Obteniendo datos para el año ${year}.`)
			let cursor = col.find(filters)
		
			let initYear= Number(year) +1
			let referenceDate = moment(`${initYear}-01-01`,'YYYY-MM-DD').format('X')
			let currentReg = 0
			let generalProgress = 0
			let currentProgress = 0 
			let affiliates = yield cursor.count()

			objQuantity[year] = {}
			objProcedures[year] = {}
			objMedicines[year] = {}
			objSupplies[year] = {}
			
			objOrder.emitToFront(socket,`Consolidando datos ${year}.`)
			while(yield cursor.hasNext()){
				let affiliate = yield cursor.next()
				currentReg++

				//control de proceso
				currentProgress = (currentReg/affiliates)*(90/years.length)
				if(currentProgress > generalProgress + 2){

					generalProgress = currentProgress
					objProcess.progress = currentProgress +  percentageYear
					objProcess = yield objProcess.save()
					objOrder.emitToFront(socket)
				}

				let arrProcedures = []
				let arrMedicines = []
				let arrSupplies = []

				let countFlag = false
				for(let activity of affiliate.activities){
					
					if(activity.quality && (_.intersection(qualityNumbers,activity.quality).length > 0)){
						continue
					}

					if(!utils.validateDateByPeriods(activity.date,periods)){
						continue
					}

					if([2,3,4,8].indexOf(activity.type) > -1 && (!arrDiagnostics || arrDiagnostics.indexOf(activity.diagnosticMainCode) >-1)){
						countFlag = true						
						switch(activity.type){
					    	case 2://Procedimientos
								arrProcedures.push(activity)					    		
					    		break
					    	case 8://Insumos			
								arrSupplies.push(activity)
					    		break
					    	case 3://Medicinas			
					    	case 4:			
								arrMedicines.push(activity)
					    		break
					    }

					}
				}

				if(!countFlag){
					continue
				}

				//asigancion de eps encontradas
				if(arrEpss.indexOf(affiliate.healthEntityCode) < 0){
					arrEpss.push(affiliate.healthEntityCode)
				}
				
				//procesamiento de cantidades
				let bornYear = parseInt(moment(affiliate.birthdate,'X').format('YYYY'))
				let age = parseInt(moment(referenceDate,'X').format('YYYY')) - bornYear
				if (age < 0 || age == undefined) {
					age = 0
				}

				let qGroup = utils.getQuinquennialGroup(age,affiliate.genre)				
			
				if(!objQuantity[year][qGroup]){
					objQuantity[year][qGroup] = {}
				}

				if(!objQuantity[year][qGroup][affiliate.healthEntityCode]){
					objQuantity[year][qGroup][affiliate.healthEntityCode] = {q:0,aten:0}
				}

				objQuantity[year][qGroup][affiliate.healthEntityCode].q ++
				objQuantity[year][qGroup][affiliate.healthEntityCode].aten += arrProcedures.length

				//procesamiento de procedimeintos
				for(let procedure of arrProcedures){

					if(!objProcedures[year][procedure.activityCode]){
						objProcedures[year][procedure.activityCode] = {}
					}

					if(!objProcedures[year][procedure.activityCode][affiliate.healthEntityCode]){
						objProcedures[year][procedure.activityCode][affiliate.healthEntityCode] = 0	
					}

					objProcedures[year][procedure.activityCode][affiliate.healthEntityCode] += procedure.value
				}

				//procesamiento de medicamentos
				for(let medicine of arrMedicines){
					if(!objMedicines[year][medicine.activityCode]){
						objMedicines[year][medicine.activityCode] = {}
					}

					if(!objMedicines[year][medicine.activityCode][affiliate.healthEntityCode]){
						objMedicines[year][medicine.activityCode][affiliate.healthEntityCode] = 0	
					}

					objMedicines[year][medicine.activityCode][affiliate.healthEntityCode] += medicine.value
				}

				//procesamiento de insumos
				for(let supplie of arrSupplies){
					if(!objSupplies[year][supplie.activityCode]){
						objSupplies[year][supplie.activityCode] = {}
					}

					if(!objSupplies[year][supplie.activityCode][affiliate.healthEntityCode]){
						objSupplies[year][supplie.activityCode][affiliate.healthEntityCode] = 0	
					}

					objSupplies[year][supplie.activityCode][affiliate.healthEntityCode] += supplie.value
				}

			}
			percentageYear = currentProgress
		}

		//construccion de salidas
		for(let foundEPS of arrEpss){
			quantityOutput[0].push('Cantidad '+foundEPS)
			quantityOutput[0].push('Atenciones '+foundEPS)
			procedureOutput[0].push('Costo '+foundEPS)
			medicineOutput[0].push('Costo '+foundEPS)
			suppliesOutput[0].push('Costo '+foundEPS)
		}

		//conslidacion salida de conteos
		for(let year of Object.keys(objQuantity)){
			for(let ageRange of Object.keys(objQuantity[year])){
				let quantityRow = [year]
				quantityRow.push(ageRange)
				for(let foundEPS of arrEpss){
					let quantity = 0
					let attentions = 0
					if(objQuantity[year][ageRange][foundEPS]){
						quantity = objQuantity[year][ageRange][foundEPS].q
						attentions = objQuantity[year][ageRange][foundEPS].aten
					}
					quantityRow.push(quantity)
					quantityRow.push(attentions)
				}
				quantityOutput.push(quantityRow)
			}
		}

		/**
		 * CONSTRUCCION DE REPORTE DESCARGABLE
		 */
		
		var quantityPath = path.join(application.parameters.publicPath , `usuarios_${moment().format('X')}.csv`)
		fs.openSync(quantityPath,'w')
		yield utils.appendIntoCsv(quantityOutput,quantityPath)

		//consolidacion salida de procedimientos
		for(let year of Object.keys(objProcedures)){
			for(let procedureCode of Object.keys(objProcedures[year])){
				let procedureRow = [year]
				procedureRow.push(procedureCode)
				procedureRow.push((procedures[procedureCode])?procedures[procedureCode].description:'CUP No encontrado')
				for(let foundEPS of arrEpss){
					let cost = 0
					if(objProcedures[year][procedureCode][foundEPS]){
						cost = objProcedures[year][procedureCode][foundEPS]
					}
					procedureRow.push(cost)
				}
				procedureOutput.push(procedureRow)
			}
		}

		var procedurePath = path.join(application.parameters.publicPath , `procedimientos_${moment().format('X')}.csv`)
		fs.openSync(procedurePath,'w')
		yield utils.appendIntoCsv(procedureOutput,procedurePath)

		//consolidacion salida de medicamentos
		for(let year of Object.keys(objMedicines)){
			for(let medicineCode of Object.keys(objMedicines[year])){
				let medicineRow = [year]
				medicineRow.push(medicineCode)
				for(let foundEPS of arrEpss){
					let cost = 0
					if(objMedicines[year][medicineCode][foundEPS]){
						cost = objMedicines[year][medicineCode][foundEPS]
					}
					medicineRow.push(cost)
				}

				medicineOutput.push(medicineRow)
			}
		}

		var medicinePath = path.join(application.parameters.publicPath , `medicamentos_${moment().format('X')}.csv`)
		fs.openSync(medicinePath,'w')
	
		yield utils.appendIntoCsv(medicineOutput,medicinePath)

		//consolidacion salida de insumos
		for(let year of Object.keys(objSupplies)){
			for(let supplieCode of Object.keys(objSupplies[year])){
				let supplieRow = [year]
				supplieRow.push(supplieCode)
				for(let foundEPS of arrEpss){
					let cost = 0
					if(objSupplies[year][supplieCode][foundEPS]){
						cost = objSupplies[year][supplieCode][foundEPS]
					}
					supplieRow.push(cost)
				}

				suppliesOutput.push(supplieRow)
			}
		}

		var suppliePath = path.join(application.parameters.publicPath , `insumos_${moment().format('X')}.csv`)
		fs.openSync(suppliePath,'w')
		yield utils.appendIntoCsv(suppliesOutput,suppliePath)

		//Se genera un archivo comprimido
		if(objOrder.meta.endPoint){
			var filePath = path.join(application.parameters.publicPath , objOrder.meta.endPoint)
			fs.openSync(filePath,'w')
			
			yield utils.compressFiles(filePath,[medicinePath,procedurePath,quantityPath,suppliePath])
			
			//se eliminan los temporales
			fs.unlinkSync(medicinePath)
			fs.unlinkSync(procedurePath)
			fs.unlinkSync(quantityPath)
			fs.unlinkSync(suppliePath)
		}

		objProcess.status = 1
		objProcess.progress = 90
		objProcess = yield objProcess.save()

		/**
		 * CONSTRUCCION DE GRAFICO DE SALIDA
		 */
		
		objOrder.emitToFront(socket,'Construyendo grafico')

		//creo las tres series del reporte
		let objResponse = {}	
		
		for(let year of years){
			objResponse[year] = {epss:arrEpss,quantity:[],procedure:[],medicine:[],supplie:[]}

			for(let eps of arrEpss){
				let qValue = 0	
				for(let lustro of Object.keys(objQuantity[year])){
					if(objQuantity[year][lustro][eps]){
						qValue += objQuantity[year][lustro][eps].q
					}
				}
			
				objResponse[year].quantity.push(qValue)
				
				let pValue = 0

				for(let procedure of Object.keys(objProcedures[year])){
					if(objProcedures[year][procedure][eps]){
						pValue += objProcedures[year][procedure][eps]
					}
				}
			
				objResponse[year].procedure.push(pValue)
				
				let mValue = 0
				
				for(let medicine of Object.keys(objMedicines[year])){
					if(objMedicines[year][medicine][eps]){
						mValue += objMedicines[year][medicine][eps]
					}
				}
			
				objResponse[year].medicine.push(mValue)

				let sValue = 0
				
				for(let supplie of Object.keys(objSupplies[year])){
					if(objSupplies[year][supplie][eps]){
						sValue += objSupplies[year][supplie][eps]
					}
				}
			
				objResponse[year].supplie.push(sValue)
			}
		}
		
		objProcess.status = 2
		objProcess.progress = 100
		objProcess = yield objProcess.save()		

		return objResponse

	}catch(e){

		objProcess.status = 3
		objProcess.progress = 100
		objProcess = yield objProcess.save()

		console.error(e.stack)
		errorManager.reportError(e.message,objProcess.id).next()	
	}

} 
