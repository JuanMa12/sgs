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

	var bufer = [['Año','Ambito','Costo Zona 1','Costo Zona 2','Costo Zona 3']]
	var filePath = path.join(application.parameters.publicPath , objOrder.meta.endPoint)
	
	fs.openSync(filePath,'w')
	let objResult

	let zoneByMunicipalityMap = {}
	var result = yield application.entities.municipality.findAll()
	for(let municipality of result){
		zoneByMunicipalityMap[municipality.code] = municipality.zone
	}

	try{

		console.log(`Procesando orden de reporte por ambito ${objOrder.id}`)
		objProcess.status = 1
		objProcess.progress = 1
		objProcess = yield objProcess.save()

		let arrDivipola = objOrder.meta.divipola
		let qualityNumbers = objOrder.meta.qualityNumbers
		let arrEpss = objOrder.meta.epss
		let periods = utils.compilePeriods(objOrder.meta.periods)
		
		let filter = {}
		if(arrEpss != undefined){//En caso de que se especifiquen epss
			filter.healthEntityCode = {$in:arrEpss}
		}

		objResult = {}
		if (!qualityNumbers) {
			qualityNumbers = []
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
	
			objOrder.emitToFront(socket,`Obteniendo datos año ${year}.`)
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
				let firstActivity = true
				let zone = ''
				
				for(let activity of affiliate.activities){

					if(activity.quality && (_.intersection(qualityNumbers,activity.quality).length > 0)){
						continue
					}					
					
					if([2,3,4,8].indexOf(activity.type) < 0 || !utils.validateDateByPeriods(activity.date,periods)){
						continue
					}
					
					//solo se valida en caso de que sean definidos en los parametros
					if(arrDivipola != undefined && arrDivipola.indexOf(activity.divipola) == -1){
						continue
					}

					if(firstActivity){
						zone = zoneByMunicipalityMap[activity.divipola]
						firstActivity = false
					}

					if(!objResult[year][activity.ambit]){
						objResult[year][activity.ambit] = {cost1:0,cost2:0,cost3:0}
					}

					objResult[year][activity.ambit][`cost${zone}`] += Number(activity.value)

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

		let objConsolidate = {}

		for(let year of Object.keys(objResult)){
			objConsolidate[year] = {}
			for(let ambit of Object.keys(objResult[year])){	
				let name = ambit
				switch(ambit){
					case 'A':
						name = 'Ambulatorio'
						break
					case 'U':
						name = 'Urgencias'
						break
					case 'D':
						name = 'Domiciliario'						
						break						
					case 'H':
						name = 'Hospitalario'
						break						
				}
				if(!objConsolidate[year][name]){
					objConsolidate[year][name] = {cost1:0,cost2:0,cost3:0}
				}

				objConsolidate[year][name].cost1 = objResult[year][ambit].cost1
				objConsolidate[year][name].cost2 = objResult[year][ambit].cost2
				objConsolidate[year][name].cost3 = objResult[year][ambit].cost3
			}
		}


		if (filePath) {
			for(let year of Object.keys(objConsolidate)){
				for(let ambit of Object.keys(objConsolidate[year])){					
					bufer.push([
						year,
						ambit,
						objConsolidate[year][ambit].cost1,
						objConsolidate[year][ambit].cost2,
						objConsolidate[year][ambit].cost3,
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


		objProcess.status = 2
		objProcess.progress = 100
		objProcess = yield objProcess.save()		

		return objConsolidate

	}catch(e){

		objProcess.status = 3
		objProcess.progress = 100
		objProcess = yield objProcess.save()

		console.error(e.stack)
		errorManager.reportError(e.message,objProcess.id).next()	
	}
} 
