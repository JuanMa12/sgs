"use strict"
const co = require("co")
const _ = require("lodash")

let processTail
let inExecution

let context

/**
 * Funcion que ejecuta procesos de la cola de procesos. Si los 
 * procesos en ejecucion superan los 10 los proceso quedan encolados
 * hasta que la cola se vacie.
 * @return {[type]} [description]
 */
let flushTail = function(){

	co(function*(){
		if(inExecution <= 10 && processTail.length > 0){

			//extraccion del primer proceso encolado
			let candidateProcess = processTail.shift()
			inExecution++

			emitTailInfo()
			
			let startProcess = candidateProcess.script
			try{
				let data = yield startProcess(context,candidateProcess.order,candidateProcess.process,candidateProcess.socket)
				candidateProcess.resolve(data)
			}catch(e){
				candidateProcess.reject(e)
			}

			inExecution--
			//auto invocacion recurrente
			flushTail()
		}

		console.log(`Numero de procesos en cola: ${inExecution}`)
	})
}

let emitTailInfo = function(){
	let position = 1
	for(let candidate of processTail){
		candidate.order.emitToFront(candidate.socket,`Posicion de la cola: ${position}`)
		position ++
	}
}

module.exports = function(app){

	processTail = []
	inExecution = 0
	context = app.context

	context.addProcessToTail = function(processScript,objOrder,objProcess,socket){

		return new Promise(function(resolve,reject){

			/**
			 * paso a la cola la funcion resolve y reject con el objetivo de
			 * delegar su ejecucion a la funcion de ejecucion de encolamiento
			 */
			processTail.push({
				script:processScript,
				order:objOrder,
				process:objProcess,
				socket:socket,
				resolve:resolve,
				reject:reject
			})

			flushTail()
		})
	}
}
