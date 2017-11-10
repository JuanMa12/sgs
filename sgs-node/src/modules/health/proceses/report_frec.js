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
	
	try{		
		console.log(`Procesando orden de reporte de frecuencias ${objOrder.id}`)
		objProcess.status = 1
		objProcess.progress = 1
		objProcess = yield objProcess.save()

		let periods = utils.compilePeriods(objOrder.meta.periods)
		let arrEpss = objOrder.meta.epss
		let qualityNumbers = objOrder.meta.qualityNumbers

		if (!qualityNumbers) {
			qualityNumbers = []
		}

		objResult = {}
		let filter = {}
		if(arrEpss != undefined){//En caso de que se especifiquen epss
			filter.healthEntityCode = {$in:arrEpss}
		}

		let percentageYear = 0
		let years = utils.getArrYears(objOrder.meta.periods)
		console.log(years)
		for(let year of years){
			/**
			 * BASE DE DATOS
			 */
			
			let collectionName = yield utils.getCollectionName(application.mongo,objOrder.meta.collection,year)
			let col = application.mongo.collection(collectionName)

			objResult[year] = {}
			for(let ageKey of qGroups){
				objResult[year][ageKey] = {c1:0,c2:0,c3:0,a1:0,a2:0,a3:0,p1:0,p2:0,p3:0}
			}
			
			objOrder.emitToFront(socket,`Obteniendo datos para el año ${year}.`)
			let cursor = col.find(filter)
				
			let initYear= Number(year) +1
			let referenceDate = moment(`${initYear}-01-01`,'YYYY-MM-DD').format('X')
			let currentReg = 0
			let generalProgress = 0
			let currentProgress = 0
			let affiliates = yield cursor.count()
			/**
			 * CONSTRUCCION DE REPORTE DESCARGABLE
			 */
			
			objOrder.emitToFront(socket,`Consolidando datos año ${year}`)
			while(yield cursor.hasNext()){
				let affiliate = yield cursor.next()
				currentReg++
			
				let countFlag = false
				let totalCost = 0
				let attentions = 0				
				let affiliateFlag = false
				let divipola = ''

				for(let settlement of affiliate.settlements){
					if(utils.validateDateByPeriods(settlement.date,periods)){
						affiliateFlag = true
						divipola = settlement.divipola
						break
					}
				}

				for(let activity of affiliate.activities){
					//solo se valida en caso de que sean definidos en los parametros
		
					if(!activity || activity.type > 2){//solo 2
						continue
					}

					if(activity.quality && (_.intersection(qualityNumbers,activity.quality).length > 0)){
						continue
					}
					if (divipola == '') {
						divipola = activity.divipola
					}
					if(utils.validateDateByPeriods(activity.date,periods)){
						totalCost += Number(activity.value)
						countFlag = true
						attentions ++
					}

				}

				let bornYear = parseInt(moment(affiliate.birthdate,'X').format('YYYY'))
				let age = parseInt(moment(referenceDate,'X').format('YYYY')) - bornYear
				let qGroup = utils.getQuinquennialGroup(age,affiliate.genre)
				let zone = zoneByMunicipalityMap[divipola]

				if(affiliateFlag){					
					objResult[year][qGroup][`p${zone}`] ++
				}

				if(countFlag){
					objResult[year][qGroup][`a${zone}`] += attentions
					objResult[year][qGroup][`c${zone}`] += totalCost
				}
				
				currentProgress = (currentReg/affiliates)*(90/years.length)
				if(currentProgress > generalProgress + 2){

					generalProgress = currentProgress
					objProcess.progress = currentProgress + percentageYear
					objProcess = yield objProcess.save()
					objOrder.emitToFront(socket)
				}
			}
			percentageYear = currentProgress
		}

		/**
		 * DESCARGABLE
		 */
		var bufer = [['Año','Grupo Quinquenal','Atenciones Zona 1','Población Zona 1','Costos Zona 1','Frecuencia Zona 1','Atenciones Zona 2','Población Zona 2','Costos Zona 2','Frecuencia Zona 2','Atenciones Zona 3','Población Zona 3','Costos Zona 3','Frecuencia Zona 3']]
		if(filePath){
			for(let year of Object.keys(objResult)){
				for(let qGroup of Object.keys(objResult[year])){
					let objQGroup = objResult[year][qGroup]

					bufer.push([
						year,
						qGroup,
						objQGroup.a1,
						objQGroup.p1,						
						objQGroup.c1,						
						utils.getNumberFormat(objQGroup.a1 / objQGroup.p1),
						objQGroup.a2,
						objQGroup.p2,						
						objQGroup.c2,						
						utils.getNumberFormat(objQGroup.a2 / objQGroup.p2),
						objQGroup.a3,
						objQGroup.p3,						
						objQGroup.c3,						
						utils.getNumberFormat(objQGroup.a3 / objQGroup.p3),
						])
				}
			}

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
		//creo las tres series del reporte
		let objResponse = {}

		for(let year of Object.keys(objResult)){
			objResponse[year] = {}
			for(let key of Object.keys(objResult[year])){
				let objQGroup = objResult[year][key]
				
				objResponse[year][key] = {}
				objResponse[year][key].fre1 = {attentions:objQGroup.a1,population:objQGroup.p1}
				objResponse[year][key].fre2 = {attentions:objQGroup.a2,population:objQGroup.p2}
				objResponse[year][key].fre3 = {attentions:objQGroup.a3,population:objQGroup.p3}
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
