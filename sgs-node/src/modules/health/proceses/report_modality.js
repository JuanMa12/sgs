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

	var bufer = [['Año','Modalidad','Costo Zona 1','Costo Zona 2','Costo Zona 3']]
	var filePath = path.join(application.parameters.publicPath , objOrder.meta.endPoint)
	
	fs.openSync(filePath,'w')
	let objResult

	let zoneByMunicipalityMap = {}
	var result = yield application.entities.municipality.findAll()
	for(let municipality of result){
		zoneByMunicipalityMap[municipality.code] = municipality.zone
	}

	try{

		console.log(`Procesando orden de reporte por modalidad ${objOrder.id}`)
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
			if (!qualityNumbers) {
				qualityNumbers = []
			}			
	
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

					if(!objResult[year][activity.paymentMethod]){
						objResult[year][activity.paymentMethod] = {cost1:0,cost2:0,cost3:0}
					}

					objResult[year][activity.paymentMethod][`cost${zone}`] += Number(activity.value)

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
			for(let paymentMethod of Object.keys(objResult[year])){	
				let name = paymentMethod
				switch(paymentMethod){
					case 'C':
						name = 'Capitación'
						break
					case 'S':
						name = 'Evento'
						break
					case 'P':
						name = 'Por caso'						
						break						
					case 'A':
						name = 'Autorizado'
						break
					case 'I':
						name = 'Directo'
						break						
				}
				if(!objConsolidate[year][name]){
					objConsolidate[year][name] = {cost1:0,cost2:0,cost3:0}
				}

				objConsolidate[year][name].cost1 = objResult[year][paymentMethod].cost1
				objConsolidate[year][name].cost2 = objResult[year][paymentMethod].cost2
				objConsolidate[year][name].cost3 = objResult[year][paymentMethod].cost3
			}
		}


		if (filePath) {
			for(let year of Object.keys(objConsolidate)){
				for(let paymentMethod of Object.keys(objConsolidate[year])){					
					bufer.push([
						year,
						paymentMethod,
						objConsolidate[year][paymentMethod].cost1,
						objConsolidate[year][paymentMethod].cost2,
						objConsolidate[year][paymentMethod].cost3,
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
