"use strict"
const co = require('co')
const _ = require('lodash')
const utils = require('./../../utils')
const fs = require('fs')
const path = require('path')
const moment = require('moment')



/**
 * Metodo principal
 * @param  {[type]} application [aplicacion koa]
 * @param  {[type]} objOrder    [objeto de orden de proceso]
 * @param  {[type]} objProcess  [objeto de proceso]
 * @return {[type]}             [description]
 */
module.exports = function*(application,objOrder,objProcess,socket){

	var errorManager = application.errorManager

	var quantityOutput = [['Año','Lustro']]
	var diagnosticOutput = [['Año','Diagnostico','Descripcion de diagnostico']]

	//inicializacion de datos locales
	var diagnostics = {}
	var result = yield application.entities.diagnostic.findAll()
	for(let diagnostic of result){
		diagnostics[diagnostic.code] = {
			description:diagnostic.description,
		}
	}

	let objQuantity
	let objDiagnostic

	try{		
		console.log(`Procesando orden de reporte de relacion de procedimientos ${objOrder.id}`)
		objProcess.status = 1
		objProcess.progress = 1
		objProcess = yield objProcess.save()

		objQuantity = {}
		objDiagnostic = {}
		
		let periods = utils.compilePeriods(objOrder.meta.periods)
		let arrProcedures = objOrder.meta.procedures
		let qualityNumbers = objOrder.meta.qualityNumbers
		
		if (!qualityNumbers) {
			qualityNumbers = []
		}
		
		let filter = {}
		if(objOrder.meta.epss != undefined){//En caso de que no se especifiquen epss
			filter.healthEntityCode = {$in:objOrder.meta.epss}
		}

		let arrEpss = []//eps encontradas
		let percentageYear = 0
		let years = utils.getArrYears(objOrder.meta.periods)		
		console.log(years)

		for(let year of years){

			let collectionName = yield utils.getCollectionName(application.mongo,objOrder.meta.collection,year)
			let col = application.mongo.collection(collectionName)

			objOrder.emitToFront(socket,`Obteniendo datos para el año ${year}.`)
			let cursor = col.find(filter)
		
			let initYear= Number(year) +1
			let referenceDate = moment(`${initYear}-01-01`,'YYYY-MM-DD').format('X')
			let currentReg = 0
			let generalProgress = 0
			let currentProgress = 0 
			let affiliates = yield cursor.count()

			objQuantity[year] = {}
			objDiagnostic[year] = {}		
		
			objOrder.emitToFront(socket,`Consolidando datos año ${year}.`)
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

				let arrDiagnostic = []				
				let quantityProcedures = 0

				let countFlag = false
				for(let activity of affiliate.activities){
					
					if(activity.quality && (_.intersection(qualityNumbers,activity.quality).length > 0)){
						continue
					}

					if(!utils.validateDateByPeriods(activity.date,periods)){
						continue
					}

					if([2].indexOf(activity.type) > -1 && (!arrProcedures || arrProcedures.indexOf(activity.activityCode) >-1)){
						
						countFlag = true
						quantityProcedures ++

						//diagnosticos
						arrDiagnostic.push(activity)

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
				let age = 'NA'		
				if (affiliate.birthdate != 'NA') {
					let bornYear = parseInt(moment(affiliate.birthdate,'X').format('YYYY'))
					let year = parseInt(moment(referenceDate,'X').format('YYYY'))
					age = year - bornYear
				}

				let ageKey = 'NA'
				if (age != 'NA') {
					ageKey = utils.getQuinquennialGroup(age,affiliate.genre)

				}

				if(!objQuantity[year][ageKey]){
					objQuantity[year][ageKey] = {}
				}

				if(!objQuantity[year][ageKey][affiliate.healthEntityCode]){
					objQuantity[year][ageKey][affiliate.healthEntityCode] = {q:0,aten:0}
				}

				objQuantity[year][ageKey][affiliate.healthEntityCode].q ++
				objQuantity[year][ageKey][affiliate.healthEntityCode].aten += quantityProcedures

				//procesamiento de diagnosticos
				for(let diagnostic of arrDiagnostic){
					if(!objDiagnostic[year][diagnostic.diagnosticMainCode]){
						objDiagnostic[year][diagnostic.diagnosticMainCode] = {}
					}

					if(!objDiagnostic[year][diagnostic.diagnosticMainCode][affiliate.healthEntityCode]){
						objDiagnostic[year][diagnostic.diagnosticMainCode][affiliate.healthEntityCode] = 0	
					}

					objDiagnostic[year][diagnostic.diagnosticMainCode][affiliate.healthEntityCode] += diagnostic.value
				}

			}
			percentageYear = currentProgress
		}

		//construccion de salidas
		for(let foundEPS of arrEpss){
			quantityOutput[0].push('Cantidad '+foundEPS)
			quantityOutput[0].push('Atenciones '+foundEPS)
			diagnosticOutput[0].push('Costo '+foundEPS)			
		}

		//conslidacion salida de conteos
		for(let year of Object.keys(objQuantity)){
			for(let ageRange of Object.keys(objQuantity[year])){
				let quantityRow = [year]
				quantityRow.push(ageRange)
				for(let foundEPS of arrEpss){
					let quantityValue = 0
					let attentions = 0
					if(objQuantity[year][ageRange][foundEPS]){
						quantityValue = objQuantity[year][ageRange][foundEPS].q
						attentions = objQuantity[year][ageRange][foundEPS].aten
					}
					quantityRow.push(quantityValue)
					quantityRow.push(attentions)
				}
				quantityOutput.push(quantityRow)
			}
		}
		
		var quantityPath = path.join(application.parameters.publicPath , `usuarios2_${moment().format('X')}.csv`)
		fs.openSync(quantityPath,'w')
		yield utils.appendIntoCsv(quantityOutput,quantityPath)

		//consolidacion salida de procedimientos
		for(let year of Object.keys(objDiagnostic)){
			for(let diagnosticCode of Object.keys(objDiagnostic[year])){
				let diagnosticRow = [year]
				diagnosticRow.push(diagnosticCode)
				diagnosticRow.push((diagnostics[diagnosticCode])?diagnostics[diagnosticCode].description:'CIE 10 No encontrado')
				for(let foundEPS of arrEpss){
					let cost = 0
					if(objDiagnostic[year][diagnosticCode][foundEPS]){
						cost = objDiagnostic[year][diagnosticCode][foundEPS]
					}
					diagnosticRow.push(cost)
				}

				diagnosticOutput.push(diagnosticRow)
			}
		}

		var procedurePath = path.join(application.parameters.publicPath , `diagnosticos_${moment().format('X')}.csv`)
		fs.openSync(procedurePath,'w')
		yield utils.appendIntoCsv(diagnosticOutput,procedurePath)

		//Se genera un archivo comprimido
		if(objOrder.meta.endPoint){
			var filePath = path.join(application.parameters.publicPath , objOrder.meta.endPoint)
			fs.openSync(filePath,'w')

			yield utils.compressFiles(filePath,[procedurePath,quantityPath])
			
			//se eliminan los temporales
			fs.unlinkSync(procedurePath)
			fs.unlinkSync(quantityPath)
		}

		objProcess.status = 1
		objProcess.progress = 90
		objProcess = yield objProcess.save()

		/**
		 * PROCESAMIENTO DE GRAFICOS
		 */

		objOrder.emitToFront(socket,'Construyendo grafico')					

		//creo las tres series del reporte
		let objResponse = {}	
		
		for(let year of years){
			objResponse[year] = {epss:arrEpss,quantity:[],diagnostic:[]}

			for(let eps of arrEpss){
				let qValue = 0
				for(let lustro of Object.keys(objQuantity[year])){
					if(objQuantity[year][lustro][eps]){
						qValue += objQuantity[year][lustro][eps].q
					}
				}
				objResponse[year].quantity.push(qValue)
				
				let dValue = 0
				for(let diagnostic of Object.keys(objDiagnostic[year])){
					if(objDiagnostic[year][diagnostic][eps]){
						dValue += objDiagnostic[year][diagnostic][eps]
					}
				}
				objResponse[year].diagnostic.push(dValue)			
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
