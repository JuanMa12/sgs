"use strict"
const co = require('co')
const LineByLineReader = require('line-by-line')
const utils = require('./../../utils')

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
	console.log(totalLines)

	let sufCollectionName = yield utils.getCollectionName(application.mongo,'suf',objOrder.meta.year,true)
	let sufCol = application.mongo.collection(sufCollectionName)
	
	let pipCollectionName = yield utils.getCollectionName(application.mongo,'pip',objOrder.meta.year,true)
	let pipCol = application.mongo.collection(pipCollectionName)
	
	let main = function(){
		return function(callback){

			let lr = new LineByLineReader(objOrder.meta.path)
			
			let settlementBuffer = []
			let affiliationBuffer = []

			let currentLine = 0
			let insertedAttempts  = 0
			let currentLinePerBuffer = 0
			let insertedLines = 0
			let emptyLines = 0
			let manualBreak = false

			let errorCount = 0
							
			console.log(`Procesando orden ${objOrder.id} - LMA`)		
			objOrder.emitToFront(socket,"Procesando liquidaciones")

			lr.on('line', function(line) {
				lr.pause()
				co(function*(){

					if (manualBreak) {
				    	return
				    }
		
					currentLine++

					if(!utils.validateReadableCharacter(line)){
				    	errorManager.reportError(`Linea ${currentLine}: Presencia de caracteres especiales: '${line}'`,objProcess.id).next()
				    	errorCount++
				    	lr.resume()
			    		return
				    }
					
				    let data = line.split(',')	
				    if(data.length < 5){
				    	errorManager.reportError(`Linea ${currentLine}: No cuenta con la cantidad de columnas necesarias: '${line}'`,objProcess.id).next()
				    	errorCount++
				    	lr.resume()
			    		return			 
				    }

				    if(errorCount > 1000){
				    	let error = new Error('El numero de errores ha superado el limite. El proceso no puede continuar.')
				    	error.fatal = true
						throw error
				    }

			    	//registro de afiliaciones
				    let affiliation = {
						insertOne:{
							documentType:data[1],	
							documentNumber:data[2],
							healthEntityCode:(data[10].toUpperCase()).trim(),
							birthdate:utils.getUnixDate(data[7]),
							genre:data[8],
							activities:[],
							settlements:[]
						}
					}

					affiliationBuffer.push(affiliation)

					//registro de liquidacion
					let settlement = {
						reference:Number(data[0]),
						date:Number(utils.getUnixDate(data[14])),
						divipola:`${data[11]}${data[12]}`,
						value:data[16],
						days:data[15],
						processId:objProcess.id
					}

					let affiliationKey = {
						documentType:data[1],	
						documentNumber:data[2],
						healthEntityCode:(data[10].toUpperCase()).trim()
					}

					settlementBuffer.push({
						updateOne:{
							"filter":affiliationKey,
							"update":{$push:{settlements:settlement}}
						}
					})

					currentLinePerBuffer ++

					if(currentLinePerBuffer > 499 || currentLine >= totalLines){

						if(affiliationBuffer.length > 0){
							yield [sufCol.bulkWrite(affiliationBuffer,{ ordered : false }),pipCol.bulkWrite(affiliationBuffer,{ ordered : false })]
							affiliationBuffer = []
						}

						if(settlementBuffer.length > 0){
							insertedAttempts += settlementBuffer.length
							let res = yield [sufCol.bulkWrite(settlementBuffer,{ ordered : false }),pipCol.bulkWrite(settlementBuffer,{ ordered : false })]
							insertedLines += res[0].modifiedCount
							settlementBuffer = []
						}

						currentLinePerBuffer = 0

						let currentProgress = currentLine/totalLines*90
						objProcess.progress = currentProgress
						objProcess = yield objProcess.save()
						objOrder.emitToFront(socket)

						if(currentLine >= totalLines){
							let finalMessage = `Lineas leidas: ${currentLine}
								Lineas Enviadas: ${insertedAttempts}
								Lineas insertadas: ${insertedLines}
								Lineas vacias: ${emptyLines}`
							
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
				    	errorManager.reportError(`${e.message} en la linea ${currentLine}`,objProcess.id).next()
			    		callback(e)
			    	}else{
			    		console.log(e)
			    		errorManager.reportError(e.message,objProcess.id).next()
			    		lr.resume()
			    	}
			    })
				
			})
		}
	}

	let result = yield main()
	return result
} 

