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


	let zoneByMunicipalityMap = {}
	var result = yield application.entities.municipality.findAll()
	for(let municipality of result){
		zoneByMunicipalityMap[municipality.code] = municipality.zone
	}

	let objResult
	let affiliates
	
	try{		
		console.log(`Procesando orden de reporte de extension ${objOrder.id}`)
		objProcess.status = 1
		objProcess.progress = 1
		objProcess = yield objProcess.save()

		let qualityNumbers = objOrder.meta.qualityNumbers
		let arrEps = objOrder.meta.epss
		let percentageYear = 0
		let periods = utils.compilePeriods(objOrder.meta.periods)

		objResult = {}		
		if (!qualityNumbers) {
			qualityNumbers = []
		}
		let years = utils.getArrYears(objOrder.meta.periods)
		console.log(years)
		
		let filters = {}		
		if(arrEps){
			filters.healthEntityCode = {$in:arrEps}
		}		

		for(let year of years){
			let collectionName = yield utils.getCollectionName(application.mongo,objOrder.meta.collection,year)
			let col = application.mongo.collection(collectionName)

			objResult[year] = {}
			for(let ageKey of qGropus){
				objResult[year][ageKey] = {ext1:0,ext2:0,ext3:0,q:0,q1:0,q2:0,q3:0}
			}
			
			objOrder.emitToFront(socket,`Obteniendo datos para el año ${year}.`)
			let cursor = col.find(filters)
			
			let initYear= Number(year) +1
			let referenceDate = moment(`${initYear}-01-01`,'YYYY-MM-DD').format('X')
			let currentProgress = 0
			let currentReg = 0
			let generalProgress = 0
		
			affiliates = yield cursor.count()

			objOrder.emitToFront(socket,`Consolidando datos ${year}.`)
			while(yield cursor.hasNext()){
				let affiliate = yield cursor.next()
				currentReg++

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
				
				if(affiliateDivipola == ''){
					continue
				}

				let bornYear = parseInt(moment(affiliate.birthdate,'X').format('YYYY'))
				let age = parseInt(moment(referenceDate,'X').format('YYYY')) - bornYear
				if (age < 0 || age == undefined) {
					age = 0
				}

				let qGroup = utils.getQuinquennialGroup(age,affiliate.genre)				
				let zone = zoneByMunicipalityMap[affiliateDivipola]
				
				if(affiliateCount){
					objResult[year][qGroup][`q${zone}`] ++
				}
				
				if(!affiliateUseFlag){//si no uso el servicio continua
					continue
				}

				objResult[year][qGroup][`ext${zone}`] ++ 

				//se actualiza el progreso
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
		 * ESCRITURA EN ARCHIVO PUBLICO
		 */
		var bufer = [['Año','Grupo Quinquenal','Afiliados que consultaron zona 1','Personas zona 1','Afiliados que consultaron zona 2','Personas zona 2','Afiliados que consultaron zona 3','Personas zona 3','Cantidad de Afiliados']]

		if(filePath){
			for(let year of Object.keys(objResult)){
				for(let qGroup of Object.keys(objResult[year])){

					let objQGroup = objResult[year][qGroup]

					bufer.push([
						year,
						qGroup,
						objQGroup.ext1,
						objQGroup.q1,
						objQGroup.ext2,
						objQGroup.q2,
						objQGroup.ext3,
						objQGroup.q3,
						objQGroup.q1+objQGroup.q2+objQGroup.q3,
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
				objResponse[year][key] = {}
				objResponse[year][key].ext1 = {affiliateConsulted:objResult[year][key].ext1,affiliates:objResult[year][key].q1}
				objResponse[year][key].ext2 = {affiliateConsulted:objResult[year][key].ext2,affiliates:objResult[year][key].q2}
				objResponse[year][key].ext3 = {affiliateConsulted:objResult[year][key].ext3,affiliates:objResult[year][key].q3}
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
