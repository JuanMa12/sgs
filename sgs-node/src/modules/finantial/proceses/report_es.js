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

	var bufer = [['Año','Grupo','Genero','LMA','Dias','Costos']]
	
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

	let objResult

	try{		

		console.log(`Procesando orden de reporte de suficiencia ${objOrder.id}`)
		objProcess.status = 1
		objProcess.progress = 1
		objProcess = yield objProcess.save()
		
		
		let arrEps = objOrder.meta.epss
		let periods = utils.compilePeriods(objOrder.meta.periods)
		let qualityNumbers = objOrder.meta.qualityNumbers
		
		if (!qualityNumbers) {
			qualityNumbers = []
		}
		
		let filters = {}
		if(arrEps){
			filters.healthEntityCode = {$in:arrEps}
		}

		objResult = {}
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
			for(let ageKey of qGropus){
				objResult[year][ageKey] = {}
			}
			
			objOrder.emitToFront(application.finantialReports,`Obteniendo datos año ${year}.`)
			let cursor = col.find(filters)
				
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

				let cost = 0
				let lma = 0
				let days = 0
				
				for(let activity of affiliate.activities){
					
					if([2,3,4,8].indexOf(activity.type) < 0){//solo 2 3 4 8
						continue
					}

					if(activity.quality && (_.intersection(qualityNumbers,activity.quality).length > 0)){
						continue					
					}

					if(utils.validateDateByPeriods(activity.date,periods)){
						cost += Number(activity.value)
					}
				}

				for(let settlement of affiliate.settlements){
					if(utils.validateDateByPeriods(settlement.date,periods)){
						lma += Number(settlement.value)
						days += Number(settlement.days)
					}
				}


				//guardo la informacion del afiliado si tiene un genero
				if (affiliate.genre != 'NA') {					
					let bornYear = parseInt(moment(affiliate.birthdate,'X').format('YYYY'))
					let age = parseInt(moment(referenceDate,'X').format('YYYY')) - bornYear
					if (age < 0 || age == undefined) {
						age = 0
					}
					
					let qGroup = utils.getQuinquennialGroup(age,affiliate.genre)

					//procesamiento de la informacion
					if (!objResult[year][qGroup][affiliate.genre]) {
						objResult[year][qGroup][affiliate.genre] = {lma:0,cost:0,suf:0,days:0}
					}
					objResult[year][qGroup][affiliate.genre].lma += Number(lma)
					objResult[year][qGroup][affiliate.genre].cost += Number(cost)
					objResult[year][qGroup][affiliate.genre].days += Number(days)
					objResult[year][qGroup][affiliate.genre].suf += Number(lma)-Number(cost)
				}

				//control del proceso
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

		if(filePath){
			for(let year of Object.keys(objResult)){
				for(let qGroup of Object.keys(objResult[year])){
					for(let genre of Object.keys(objResult[year][qGroup])){
						bufer.push([
							year,
							qGroup,
							genre,
							utils.getNumberFormat(objResult[year][qGroup][genre].lma),
							utils.getNumberFormat(objResult[year][qGroup][genre].days),
							utils.getNumberFormat(objResult[year][qGroup][genre].cost)
						])
					}
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

		let objResponse = {}
		let qGTotal = {}

		for(let year of Object.keys(objResult)){
			objResponse[year] = {ageRanges:qGropus,lma:[],cost:[],suf:[]}
			for(let key of Object.keys(objResult[year])){
				qGTotal[key] = {lma:0,cost:0}
				for(let genre of Object.keys(objResult[year][key])){
					qGTotal[key].lma += objResult[year][key][genre].lma
					qGTotal[key].cost += objResult[year][key][genre].cost
				}
				objResponse[year].lma.push(qGTotal[key].lma)
				objResponse[year].cost.push(qGTotal[key].cost)
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
