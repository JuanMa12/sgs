"use strict"
const co = require('co')
const moment = require('moment')
const LineByLineReader = require('line-by-line')
const utils = require('./../../utils')
const ActivityManager = require('./../ES_activity_manager')

const fs = require('fs')
const crypto = require('crypto')

/**
 * Metodo principal
 * @param  {[type]} application [description]
 * @param  {[type]} objProcess  [description]
 * @return {[type]}             [description]
 */
module.exports = function*(application,objOrder,objProcess,socket){

	let errorManager = application.errorManager
	let collectionName = yield utils.getCollectionName(application.mongo,objOrder.meta.source,objOrder.meta.year)
	let col = application.mongo.collection(collectionName)
	

	console.log(`[${moment().format('YYYY-MM-DD HH:mm:ss')}] Procesando calidad`)

	try{
		let updateBuffer = []
		let voidBuffer = []
		
		let eps = objOrder.meta.eps

		let cursor = col.find({healthEntityCode: eps})
		let affiliates = yield cursor.count()

		let currentAffiliate = 0
		let progress = 0
		
		objOrder.emitToFront(socket,'Procesando Calidad')

		while(yield cursor.hasNext()){
			var affiliate = yield cursor.next()
			currentAffiliate++
			let currentProgress = currentAffiliate / affiliates * 100
			
			if(parseInt(currentProgress)>progress){
				progress = parseInt(currentProgress)
				objProcess.status = 1
				objProcess.progress = progress
				objProcess = yield objProcess.save()

				objOrder.emitToFront(socket)


				console.log(`${moment().format('YYYY-MM-DD HH:mm:ss')}] ${eps} - ${progress}%`)
			}

			let activityCount = 0
			let activityTotal = 0
			
			let arrDuplicatesHash = []

			//proceso de evaluacion y calificacion de calidad 5
			for(let activity of affiliate.activities){
				
				if( [5,6,7].indexOf(activity.type) > -1){
					continue
				}

				let objQuality = []

				activityTotal += activity.value 

				if(activity.type == 2){//calidad 5
					//se suma la cantidad de actividadea
					activityCount ++
					let strCriteria = `${activity.type}${activity.date}${activity.diagnosticMainCode}${activity.diagnosticSecondCode}${activity.value}${activity.serviceProvider}${activity.activityCode}${activity.ambit}${activity.paymentMethod}${activity.days}`
					let hash = crypto.createHash('md5').update(strCriteria).digest("hex")
					
					if(arrDuplicatesHash.indexOf(hash)> -1){//duplicado

						for(let activitySearch of affiliate.activities){
							
							let strCriteriaSearch = `${activitySearch.type}${activitySearch.date}${activitySearch.diagnosticMainCode}${activitySearch.diagnosticSecondCode}${activitySearch.value}${activitySearch.serviceProvider}${activitySearch.activityCode}${activitySearch.ambit}${activitySearch.paymentMethod}${activitySearch.days}`
							let hashSearch = crypto.createHash('md5').update(strCriteriaSearch).digest("hex")
							
							if (hash == hashSearch) {

								if (objQuality.indexOf(5) < 0) {									
									objQuality.push(5)
								}
							}
						}

					}else{
						arrDuplicatesHash.push(hash)
					}
				}

				if(affiliate.documentNumber == '' || affiliate.settlements.length == 0 ){//calidad 4
					objQuality.push(4)
				}

				if(objQuality.length > 0){
					activity.quality = objQuality
				}else{
					delete activity.quality
				}
				
			}

			if(activityTotal >= 100000000){
				for(let activity of affiliate.activities){					
					if([5,6,7].indexOf(activity.type) > -1){
						continue
					}
						
					if(!activity.quality){
						activity.quality = []
					}

					activity.quality.push(6)
				}
			}

			if(activityCount >= 1000){
				for(let activity of affiliate.activities){
				
					if(activity.type != 2){
						continue
					}
						
					if(!activity.quality){
						activity.quality = []
					}

					activity.quality.push(7)
				}

			}			

			//salvo la informacion
			yield col.update({
					healthEntityCode:affiliate.healthEntityCode,
					documentNumber:affiliate.documentNumber,
					documentType:affiliate.documentType	
				},
				{
					$set:{activities:affiliate.activities}
				}
			)

		}

		objProcess.status = 1
		objProcess.progress = 100
		objProcess = yield objProcess.save()

		console.log(`[${moment().format('YYYY-MM-DD HH:mm:ss')}] Termine`)

		return true

	}catch(e){
		console.error(e.stack)
		errorManager.reportError(e.message,objProcess.id).next()	
	}
	

}