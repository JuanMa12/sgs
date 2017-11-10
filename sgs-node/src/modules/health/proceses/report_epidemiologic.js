"use strict"
const co = require('co')
const _ = require('lodash')
const utils = require('./../../utils')
const fs = require('fs')
const path = require('path')

var moment = require('moment')

/**
 * Metodo principal
 * @param  {[type]} application [aplicacion koa]
 * @param  {[type]} objOrder    [objeto de orden de proceso]
 * @param  {[type]} objProcess  [objeto de proceso]
 * @return {[type]}             [description]
 */
module.exports = function*(application,objOrder,objProcess,socket){

	var errorManager = application.errorManager
	
	if(objOrder.meta.endPoint){ 
		var filePath = path.join(application.parameters.publicPath , objOrder.meta.endPoint)
		fs.openSync(filePath,'w')
	}

	let qGroups= [
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


	let zoneByMunicipalityMap = {}
	var result = yield application.entities.municipality.findAll()
	for(let municipality of result){
		zoneByMunicipalityMap[municipality.code] = municipality.zone
	}

	let objResult
	let affiliates
	let diagnosticGroups = {}
	
	try{		
		console.log(`Procesando orden de perfil epidemiologico ${objOrder.id}`)
		objProcess.status = 1
		objProcess = yield objProcess.save()

		let qualityNumbers = objOrder.meta.qualityNumbers
		let arrEps = objOrder.meta.epss
		let percentageYear = 0
		let periods = utils.compilePeriods(objOrder.meta.periods)

		objResult = {zones:[],diagnostics:{}}		
		
		if (!qualityNumbers) {
			qualityNumbers = []
		}

		let years = utils.getArrYears(objOrder.meta.periods)
		
		let filters = {}		
		if(arrEps){
			filters.healthEntityCode = {$in:arrEps}
		}	

		let diagnostics = {}
		let diagnosticsNotFound = []

		for(let year of years){

			let collectionName = yield utils.getCollectionName(application.mongo,objOrder.meta.collection,year)
			let col = application.mongo.collection(collectionName)
			
			objOrder.emitToFront(socket,`Obteniendo datos para el año ${year}.`)
			let cursor = col.find(filters)
			
			let initYear= Number(year) +1
			let referenceDate = moment(`${initYear}-01-01`,'YYYY-MM-DD').format('X')
			let currentProgress = 0
			let currentReg = 0
			let generalProgress = 0
		
			affiliates = yield cursor.count()

			while(yield cursor.hasNext()){
				let affiliate = yield cursor.next()
				currentReg++

				//se actualiza el progreso
				currentProgress = (currentReg/affiliates)*(90/years.length)
				if(currentProgress > generalProgress + 1){
					generalProgress = currentProgress						
					objProcess.progress = currentProgress + percentageYear
					objProcess = yield objProcess.save()
					objOrder.emitToFront(socket,`Procesando datos para el año ${year}.`)
				}

				//Se determina la zona del afiliado como el primer divipola cumpla con los requisitos
				let affiliateDivipola = ''
				let affiliateUseFlag = false
				let affiliateCount = false

				for(let settlement of affiliate.settlements){
					if(utils.validateDateByPeriods(settlement.date,periods)){
						affiliateCount = true
					}

					if(affiliateDivipola == ''){
						affiliateDivipola = settlement.divipola
					}
				}

				//opteniendo divipola del afiliado y seteando bandera de uso
				for(let activity of affiliate.activities){
									
					if(activity.quality && (_.intersection(qualityNumbers,activity.quality).length > 0)){
						continue
					}					

					if([2,3,4,8].indexOf(activity.type) > -1 && utils.validateDateByPeriods(activity.date,periods)){
						affiliateUseFlag = true
						
						if(affiliateDivipola == ''){
							affiliateDivipola = activity.divipola
						}
						break
					}
				}

				//Validacion de estado de afiliacion(liquidaciones) y uso(actividades)
				if(!affiliateCount || !affiliateUseFlag){
					continue
				}
				
				//validacion de divipola
				if(affiliateDivipola == '' || (objOrder.meta.divipola && objOrder.meta.divipola.indexOf(affiliateDivipola) < 0)){
					continue
				}

				let bornYear = parseInt(moment(affiliate.birthdate,'X').format('YYYY'))
				let age = parseInt(moment(referenceDate,'X').format('YYYY')) - bornYear
				if (age < 0 || age == undefined) {
					age = 0
				}

				let qGroup = utils.getQuinquennialGroup(age,affiliate.genre)				
				let zone = zoneByMunicipalityMap[affiliateDivipola]
				
				//validacion de grupos quinquenales
				if(objOrder.meta.qGroup && objOrder.meta.qGroup.indexOf(qGroups.indexOf(qGroup)+1) < 0){
					continue
				}
				

				let diagnosticsAffiliete = {}		
				for(let activity of affiliate.activities){
					if([2,3,4,8].indexOf(activity.type) > -1 && utils.validateDateByPeriods(activity.date,periods)){

						if(activity.quality && (_.intersection(qualityNumbers,activity.quality).length > 0)){
							continue
						}

						if(['A','H','D','U'].indexOf(activity.ambit) < 0){
							continue
						}

						//se añade la zona al resultado si no existe
						if(objResult.zones.indexOf(zone) < 0){
							objResult.zones.push(zone)
						}
						
						let diagnosticCode = activity.diagnosticMainCode
						
						//grupo de diagnosticos
						if(diagnosticGroups[diagnosticCode] == undefined){
							let rdiagnostic = yield application.entities.diagnostic.find({
								where:{
									code:diagnosticCode
								}
							})
							
							if(!rdiagnostic){
								if( diagnosticsNotFound.indexOf(diagnosticCode) < 0){
									diagnosticsNotFound.push(diagnosticCode)
								}
								continue
							}

							diagnosticGroups[diagnosticCode] = rdiagnostic.diagnostic_group_id
						}

						let dGroupId = diagnosticGroups[diagnosticCode]

						//logica de de diagnosticos a 3 digitos (grupos de diagnosticos)
						if(!diagnosticsAffiliete[dGroupId]){							
							diagnosticsAffiliete[dGroupId] = {cost:0,diagnostics:[]}
							if(['R','Z'].indexOf(diagnosticCode[0])> -1){
								diagnosticsAffiliete[dGroupId].notDefined = true
							}
						}

						diagnosticsAffiliete[dGroupId].cost += activity.value

						if(diagnosticsAffiliete[dGroupId].diagnostics.indexOf(diagnosticCode) < 0){
							diagnosticsAffiliete[dGroupId].diagnostics.push(diagnosticCode)
						}
					}
				}

				for(let dGroupId of Object.keys(diagnosticsAffiliete)){
					if(!diagnostics[dGroupId]){
						diagnostics[dGroupId] = {f1:0,f2:0,f3:0,c1:0,c2:0,c3:0,totalCost:0,totalFrec:0}
					}

					diagnostics[dGroupId][`f${zone}`] += diagnosticsAffiliete[dGroupId].diagnostics.length
					diagnostics[dGroupId][`c${zone}`] += diagnosticsAffiliete[dGroupId].cost

					diagnostics[dGroupId]['totalFrec'] += diagnosticsAffiliete[dGroupId].diagnostics.length
					diagnostics[dGroupId]['totalCost'] += diagnosticsAffiliete[dGroupId].cost

					if(diagnosticsAffiliete[dGroupId].notDefined){
						diagnostics[dGroupId].notDefined = true
					}
				}

			}

			percentageYear = currentProgress
		}

		//reporte de diagnosticos no encontrados
		if(diagnosticsNotFound.length > 0){
			errorManager.reportError(`Los siguientes diagnosticos no fueron procesados ya que no se encontraron en la base de datos: ${diagnosticsNotFound.join(',')}`,objProcess.id).next()
		}

		let rzFrec = 0
		let rzCost = 0
		let othersFrec = 0
		let othersCost = 0

		let lastMayorValue = 0
		let lastMayorKey
		let top = 10
		let dTop = {}
		/**
		 * TOP DE DIAGNOSTICOS
		 * Se realiza el top mas la contabilizacion de otros y diagnosticos inconclusos Rs y Zs
		 */
		for(let dGroupId of Object.keys(diagnostics)){
			let diagnosticCandidate = diagnostics[dGroupId]

			if(diagnosticCandidate.notDefined){
				rzFrec += diagnosticCandidate.totalFrec
				rzCost += diagnosticCandidate.totalCost
				continue
			}

			//se inserta el candidato si su frecuencia es mayor al ultimo del top
			if(diagnosticCandidate.totalFrec > lastMayorValue){
				dTop[dGroupId] = diagnosticCandidate
				//elimino el menor
				if(Object.keys(dTop).length > top){
					othersFrec += dTop[lastMayorKey].totalFrec
					othersCost += dTop[lastMayorKey].totalCost
					delete dTop[lastMayorKey]
				}

				if(Object.keys(dTop).length > top-1){

					let lastCandidateFrec
					//calculo del menor diagnostico clasificado
					for(let oldDiagnosticName of Object.keys(dTop)){
						let oldDiagnostic = dTop[oldDiagnosticName]
					
						if(!lastCandidateFrec || oldDiagnostic.totalFrec < lastCandidateFrec){
							lastCandidateFrec = lastMayorValue = oldDiagnostic.totalFrec
							lastMayorKey = oldDiagnosticName
						}
					}

				}
			}else{
				othersFrec += diagnosticCandidate.totalFrec
				othersCost += diagnosticCandidate.totalCost
			}
		}

		objResult.oDiagnostics = {f:othersFrec,c:othersCost}
		objResult.rzDiagnostics = {f:rzFrec,c:rzCost}

		/**
		 * Mapeo de nombres de grupos
		 */
		for(let dGroupId of Object.keys(dTop)){
			let diagnostic = dTop[dGroupId]

			let dGroup = yield application.entities.diagnostic_group.findById(dGroupId)
			diagnostic.code = dGroup.code
			
			let pGroup = yield application.entities.diagnostic_group.findById(dGroup.parent_id)
			diagnostic.codeCap = pGroup.code
			diagnostic.nameCap = pGroup.name
		
			objResult.diagnostics[dGroup.name] = diagnostic
		}

		/**
		 * ESCRITURA EN ARCHIVO PUBLICO
		 */
		var bufer = [['Capitulo','Descripcion Capitulo','Codigo a 3 digitos','Descripcion 3 Digitos']]

		for(let zone of objResult.zones){
			bufer[0].push(`Frecuencia Zona ${zone}`)
			bufer[0].push(`Costo Zona ${zone}`)
		}

		if(filePath){
			for(let groupName of Object.keys(objResult.diagnostics)){
				let objDiagnostic = objResult.diagnostics[groupName]

				let arrRow = [
					objDiagnostic.codeCap,
					objDiagnostic.nameCap,
					objDiagnostic.code,
					groupName
					]

				for(let zone of objResult.zones){
					arrRow.push(objDiagnostic[`f${zone}`])
					arrRow.push(objDiagnostic[`c${zone}`])
				}

				bufer.push(arrRow)
			}

			bufer.push([
				`Frecuencia Rs y Zs:${rzFrec}`,
				`Costo Rs y Zs:${rzCost}`,
				`Frecuencia otros diagnósticos:${othersFrec}`,
				`Costo otros diagnósticos:${othersCost}`
				])

			var resultCSV = yield utils.appendIntoCsv(bufer,filePath)
			bufer = []
		}

		objProcess.status = 1
		objProcess.progress = 90
		objProcess = yield objProcess.save()

		/**
		 * CONSTRUCCION DE GRAFICO DE SALIDA
		 */
		objOrder.emitToFront(socket,'Construyendo grafico')

		objProcess.status = 2
		objProcess.progress = 100
		objProcess = yield objProcess.save()

		return objResult

	}catch(e){

		objProcess.status = 3
		objProcess.progress = 100
		objProcess = yield objProcess.save()

		console.error(e.stack)
		errorManager.reportError(e.message,objProcess.id).next()	
	}
} 
