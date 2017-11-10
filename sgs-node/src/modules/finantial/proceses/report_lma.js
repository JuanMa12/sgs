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

	var bufer = [['Año','Grupo Quinquenal','Cantidad','Ingresos LMA','% del GQ']]
	var filePath = path.join(application.parameters.publicPath , objOrder.meta.endPoint)
	
	fs.openSync(filePath,'w')

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

		console.log(`Procesando orden de reporte de ingresos LMA ${objOrder.id}`)
		objProcess.status = 1
		objProcess.progress = 1
		objProcess = yield objProcess.save()

		let arrDivipola = objOrder.meta.divipola
		let arrEpss = objOrder.meta.epss
		let periods = utils.compilePeriods(objOrder.meta.periods)
		
		let filter = {}
		if(arrEpss != undefined){//En caso de que se especifiquen epss
			filter.healthEntityCode = {$in:arrEpss}
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

			objResult[year] = {total:0}			
			for(let ageKey of qGropus){
				objResult[year][ageKey] = {lma:0,q:0}
			}
			
			objOrder.emitToFront(application.finantialReports,`Obteniendo datos año ${year}.`)
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
				let totalLMA = 0
				for(let settlement of affiliate.settlements){
					//solo se valida en caso de que sean definidos en los parametros
					if(arrDivipola != undefined && arrDivipola.indexOf(settlement.divipola) == -1){
						continue
					}

					if(utils.validateDateByPeriods(settlement.date,periods)){
						totalLMA += Number(settlement.value)
						countFlag = true
					}
				}

				
				if(countFlag){
					let bornYear = parseInt(moment(affiliate.birthdate,'X').format('YYYY'))
					let age = parseInt(moment(referenceDate,'X').format('YYYY')) - bornYear
					if (age < 0 || age == undefined) {
						age = 0
					}
					
					let qGroup = utils.getQuinquennialGroup(age,affiliate.genre)
				
					objResult[year].total ++
					objResult[year][qGroup].q ++
					objResult[year][qGroup].lma += totalLMA
				}
				
				currentProgress = (currentReg/affiliates)*(90/years.length)
				if(currentProgress > generalProgress + 2){

					generalProgress = currentProgress
					objProcess.progress = currentProgress + percentageYear
					objProcess = yield objProcess.save()
					objOrder.emitToFront(application.finantialReports)
				}
			}
			percentageYear = currentProgress
		}

		if (filePath) {
			for(let year of Object.keys(objResult)){
				for(let qGroup of Object.keys(objResult[year])){
					let percentage = 0
					if (objResult[year].total > 0) {
						percentage = (objResult[year][qGroup].q / objResult[year].total) * 100
					}
					if (qGroup != 'total') {						
						bufer.push([
							year,
							qGroup,
							objResult[year][qGroup].q,
							utils.getNumberFormat(objResult[year][qGroup].lma),
							utils.getNumberFormat(percentage.toFixed(2)),				
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

		objOrder.emitToFront(application.finantialReports,'Construyendo grafico')

		//creo las tres series del reporte
		
		let objResponse = {}

		for(let year of Object.keys(objResult)){
			objResponse[year] = {ageRanges:qGropus,q:[],lma:[],total:objResult[year].total}
			for(let key of Object.keys(objResult[year])){
				if (key != 'total') {
					objResponse[year].q.push(objResult[year][key].q)
					objResponse[year].lma.push(objResult[year][key].lma)					
				}
			}
		}

		objProcess.status = 2
		objProcess.progress = 100
		objProcess = yield objProcess.save()		

		console.log(objResponse)

		return objResponse

	}catch(e){

		objProcess.status = 3
		objProcess.progress = 100
		objProcess = yield objProcess.save()

		console.error(e.stack)
		errorManager.reportError(e.message,objProcess.id).next()	
	}
} 
