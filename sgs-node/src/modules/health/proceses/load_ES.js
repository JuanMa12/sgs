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
 * @param  {[type]} objOrder    [description]
 * @param  {[type]} objProcess  [description]
 * @return {[type]}             [description]
 */
module.exports = function*(application,objOrder,objProcess,socket){

	let errorManager = application.errorManager
	
	let totalLines = yield utils.getFileLinesCount(objOrder.meta.path)
	let collectionName = yield utils.getCollectionName(application.mongo,objOrder.meta.source,objOrder.meta.year,true)
	let col = application.mongo.collection(collectionName)
	
	console.log(`Procesando actividades orden ${objOrder.id} - ES`)

	let eps
	let mode
	let currentLinePerBuffer = 0
	let objConsolidateData = {}

	let currentLine = 0
	let emptyLines = 0
	let manualBreak = false

	objProcess.progress = 1
	objProcess = yield objProcess.save()
	objOrder.emitToFront(socket,'Leyendo fuente de datos')

	let main = function(){
		return function(callback){
			let lr = new LineByLineReader(objOrder.meta.path)

			lr.on('line', function(line) {
				lr.pause()

				co(function*(){					    					    
				    
				    if (manualBreak) {
				    	return
				    }

			    	currentLine++

				    if(!utils.validateReadableCharacter(line)){
				    	errorManager.reportError(`caracteres especiales en la linea ${currentLine}`,objProcess.id).next()
				    	lr.resume()
			    		return
				    }
				    
				    if(line.indexOf(';') < 0){
				    	errorManager.reportError(`Ausencia del caracter ";" en la linea ${currentLine}`,objProcess.id).next()
				    	lr.resume()
			    		return	
				    }

				    let data = line.split(';')
				    
				    if (data.length < 5) {
				    	emptyLines ++					   		
				    	lr.resume()
			    		return
				    }

			    	//Extraccion de datos de fuente
					let type = parseInt(data[0])
					let objActivity

					let birthdate = moment(data[4],'YYYY-MM-DD').format('X')
					
					let genre = data[5]
									   	
				    switch(type){
				    	case 1:
				    		if(data.length != 5){
								throw new Error('Numero de columnas no valido')
							}
			    			eps = (data[1].toUpperCase()).trim()
			    			mode = parseInt(moment(data[2],'YYYY-MM-DD').format('YYYY'))
			    			lr.resume()
			    			return
				    	case 2:			
				    		objActivity = ActivityManager.getType2Activity(data,objProcess,mode)
				    		break
				    	case 3:
				    		objActivity = ActivityManager.getType3Activity(data,objProcess,mode)				
				    		break
						case 4:
				    		objActivity = ActivityManager.getType4Activity(data,objProcess)				
				    		break
						case 5:
				    		objActivity = ActivityManager.getType5Activity(data,objProcess,mode)				
				    		break					   
						case 6:
				    		objActivity = ActivityManager.getType6Activity(data,objProcess,mode)				
				    		break
				   		case 7:
				    		objActivity = ActivityManager.getType7Activity(data,objProcess)				
				    		genre = 'NA'
				    		birthdate = 'NA'
				    		break					    	
				    	case 8:
				    		objActivity = ActivityManager.getType8Activity(data,objProcess,mode)				
				    		break
				    }

					let affiliationKey = {
						documentNumber: data[3],
						healthEntityCode: eps,
						documentType: data[2],
					}

					let strKey = `${affiliationKey.documentType}${affiliationKey.documentNumber}${affiliationKey.healthEntityCode}`
					let hash = crypto.createHash('md5').update(strKey).digest("hex")

					if(!objConsolidateData[hash]){
						objConsolidateData[hash] = {
							affiliationKey:affiliationKey,
							affiliationData:{
								documentType: data[2],	
								documentNumber: data[3],
								healthEntityCode: eps,
								birthdate:birthdate,
								genre: genre,
								activities: [],
								settlements: []
							},
							mainProceses:[],
						}
					}

					objConsolidateData[hash].mainProceses.push(objActivity)

					//Si es diferente de 7 siempre se actuliza el genero y la fecha de nacimiento.
					if(type != 7){
						objConsolidateData[hash].feedback = {genre:genre,birthdate:birthdate}
					}

			    	currentLinePerBuffer ++

					if(currentLinePerBuffer > 499 || currentLine >= totalLines){

						if(Object.keys(objConsolidateData).length > 0){

							let arrInsertProceses = []
							let arrUpdateProceses = []

							for(let hash of Object.keys(objConsolidateData)){
								arrInsertProceses.push({
									insertOne:objConsolidateData[hash].affiliationData
								})

								let objUpdate = {
									updateOne:{
										filter:objConsolidateData[hash].affiliationKey,
										update:{$push:{activities:{$each:objConsolidateData[hash].mainProceses}}},
									}
								}

								if(objConsolidateData[hash].feedback){
									objUpdate.updateOne.update.$set = objConsolidateData[hash].feedback
								}

								arrUpdateProceses.push(objUpdate)
							}

							//insercion de afiliados
							yield col.bulkWrite(arrInsertProceses,{ ordered : false })
							//actualizacion de actividades
							yield col.bulkWrite(arrUpdateProceses,{ ordered : false })

							objConsolidateData = {}
						}

						currentLinePerBuffer = 0

						let currentProgress = parseInt(currentLine/totalLines*100)
						if(currentProgress > objProcess.progress){
							objProcess.progress = currentProgress
							objProcess = yield objProcess.save()
							objOrder.emitToFront(socket)				
						}

						if(currentLine >= totalLines){
							let finalMessage = `Lineas leidas: ${currentLine}
								Lineas con errores: ${emptyLines}`
								
							console.log(`
								######################
								${finalMessage}`)

							yield errorManager.reportError(finalMessage,objProcess.id,2)
							
							objProcess.progress = 100
							objProcess.status = 2
							objProcess = yield objProcess.save()
							objOrder.emitToFront(socket)

							callback(null,true)
							return
						}

					}

			    	lr.resume()

			    }).catch(function(e){
			    	if(e.fatal){
			    		lr.close()
			    		manualBreak = true
			    		//se termina el proceso y se emite promesa fallida
				    	errorManager.reportError(`${e.message} En la linea: ${currentLine}`,objProcess.id).next()
			    		callback(e)
			    	}else{
				    	console.error(e.stack)
				    	errorManager.reportError(`${e.message} En la linea: ${currentLine}`,objProcess.id).next()
				    	lr.resume()
			    	}
			    })
				
			})
		}
	}

	let result = yield main()
	return result

}