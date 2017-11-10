"use strict"
var co = require('co')
const utils = require('./../utils')

/**
 * Modulo de salud
 * @type {Object}
 */
let HealthModule = {}

/**
 * Funcion que administra la carga de informacion de LMA al sistema
 * @param  {Object} application [Objeto de aplicacion]
 * @param  {Objsect} porder     [Objeto de orden de proceso correspondiente]
 */
HealthModule.loadLMA = function*(application,porder){

	let errorManager = application.errorManager
	let socket = application.healthProcess

	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()
		
		//se crea un proceso de subida de liquidaciones
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()
						
		var script = require('./proceses/load_LMA')
		yield application.addProcessToTail(script,porder,proc,socket)
		
		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Generador generador que elimina los datos de liquidaciones generadas por una orden de proceso
 * @param {[object]} application   [Objeto de aplicacion]
 * @param {[object]} porder        [Objeto de orden de proceso]
 */
HealthModule.deleteLMAData = function*(application,porder){

	let socket = application.healthProcess

	try{

		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let sufCollectionName = yield utils.getCollectionName(application.mongo,'suf',porder.meta.year)
		let sufCol = application.mongo.collection(sufCollectionName)

		let pipCollectionName = yield utils.getCollectionName(application.mongo,'pip',porder.meta.year)
		let pipCol = application.mongo.collection(pipCollectionName)

		porder.status = 5 // estado proceso eliminando
		yield  porder.save()

		for(let proc of porder.processes){
			if(proc.status != 4){
				//notificacion
				porder.emitToFront(socket,`Eliminando datos de proceso ${proc.id}`)

				//Eliminación de datos de las dos colecciones
				yield [
					sufCol.update({settlements:{$elemMatch:{processId:proc.id}}},
						{'$pull':{'settlements':{'processId':proc.id}}},
						{ multi: true }),
					pipCol.update({settlements:{$elemMatch:{processId:proc.id}}},
						{'$pull':{'settlements':{'processId':proc.id}}},
						{ multi: true })
					]
				
				proc.status = 4//estado invalido
				proc = yield proc.save()

			}
		}

		porder.status = 4//estado proceso eliminado
		yield  porder.save()

		porder.emitToFront(socket,`Proceso de eliminación terminado.`)
		console.log(`Proceso terminado`)	

	}catch(e){
		console.error(e)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}


}

/**
 * Generador que elimina los datos de UPC generados por una orden de proceso
 * @param {Object} application   Objeto de applicacion
 * @param {Object} porder        Objeto de orden de proceso
 */
HealthModule.deleteESData = function*(application,porder){
	let socket = application.healthProcess

	try{

		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let collectionName = yield utils.getCollectionName(application.mongo,porder.meta.source,porder.meta.year)
		let col = application.mongo.collection(collectionName)

		porder.status = 5 // estado proceso eliminando
		yield  porder.save()

		for(let proc of porder.processes){
			if(proc.status != 4){
				//notificacion
				porder.emitToFront(socket)
				
				//Eliminación de datos
				yield col.update({activities:{$elemMatch:{processId:proc.id}}},
					{'$pull':{'activities':{'processId':proc.id}}},
					{ multi: true })
				
				proc.status = 4//estado invalido
				proc = yield proc.save()
				
			}
		}

		porder.status = 4//estado proceso eliminado
		yield  porder.save()

		porder.emitToFront(socket,`Proceso de eliminacion terminado`)
		console.log(`Proceso terminado`)

	}catch(e){
		console.error(e)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}

}

/**
 * Funcion de carga de datos UPC
 * @param  {Object} application Objeto de aplicacion
 * @param  {Objetc} porder      Objeto de orden de proceso
 */
HealthModule.loadES = function*(application,porder){

	let errorManager = application.errorManager
	let socket = application.healthProcess

	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 //estado inicial
		proc.progress = 0
		proc = yield proc.save()

		//se corren los procesos en serie
		var script = require('./proceses/load_ES')
		yield application.addProcessToTail(script,porder,proc,socket)

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Proceso de marcado de calidad
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
 */
HealthModule.quality = function*(application,porder){

	let errorManager = application.errorManager
	let socket = application.healthProcess

	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 //estado inicial
		proc.progress = 0
		proc = yield proc.save()

		//se corren los procesos en serie
		var script = require('./proceses/quality')
		yield application.addProcessToTail(script,porder,proc,socket)

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)	
	}
}

/**
 * Generador que cambia de estado los procesos de la orden de proceso
 * @param {Object} application   Objeto de applicacion
 * @param {Object} porder        Objeto de orden de proceso
 */
HealthModule.clearQualityProcess = function*(application,porder){

	let socket = application.healthProcess
	
	try{

		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let totalProcess = porder.processes.length
		let current = 0

		porder.status = 5 // estado proceso eliminando
		yield  porder.save()

		for(let proc of porder.processes){
			current ++

			//notificacion
			console.log(`Eliminando datos de ${current}/${totalProcess} procesos`)
			porder.emitToFront(socket,`Eliminando datos de ${current}/${totalProcess} procesos`)
			
			proc.status = 4//estado invalido
			proc = yield proc.save()
		}

		console.log(`Proceso terminado`)
		porder.status = 4//estado proceso eliminado
		yield  porder.save()

	}catch(e){
		console.error(e)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}


/**
 * Reporte de extension de uso
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
 */
HealthModule.createExtReport = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.healthReports

	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 //estado inicial
		proc.progress = 0
		proc = yield proc.save()

		var script = require('./proceses/report_ext')
		var data = yield application.addProcessToTail(script,porder,proc,socket)
		
		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Reporte de diagnosticos
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
HealthModule.createDiagReport = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.healthReports

	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 //estado inicial
		proc.progress = 0
		proc = yield proc.save()

		var script = require('./proceses/report_diag')
		var data = yield application.addProcessToTail(script,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Reporte de procedimientos
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
HealthModule.createProdReport = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.healthReports

	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 //estado inicial
		proc.progress = 0
		proc = yield proc.save()

		//se corren los procesos en serie
		var script = require('./proceses/report_prod')
		var data = yield application.addProcessToTail(script,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Reporte de intensidad de uso
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
HealthModule.createIntReport = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.healthReports

	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 //estado inicial
		proc.progress = 0
		proc = yield proc.save()

		//se corren los procesos en serie
		var script = require('./proceses/report_int')
		var data = yield application.addProcessToTail(script,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Reporte de frecuencia de uso
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
HealthModule.createFrecReport = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.healthReports

	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 //estado inicial
		proc.progress = 0
		proc = yield proc.save()

		var script = require('./proceses/report_frec')
		var data = yield application.addProcessToTail(script,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}


/**
 * Reporte de frecuencia de uso
 * @param  {Object} application 
 * @param  {Object} porder            
 */
HealthModule.createEpidemiologicReport = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.healthReports

	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 //estado inicial
		proc.progress = 0
		proc = yield proc.save()

		var script = require('./proceses/report_epidemiologic')
		var data = yield application.addProcessToTail(script,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
} 

/**
 * Reporte por ambito
 * @param  {Object} application 
 * @param  {Object} porder            
 */
HealthModule.createReportAmbit = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.healthReports

	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 //estado inicial
		proc.progress = 0
		proc = yield proc.save()

		var script = require('./proceses/report_ambit')
		var data = yield application.addProcessToTail(script,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}
/**
 * Reporte por modalidad de pago
 * @param  {Object} application 
 * @param  {Object} porder            
 */
HealthModule.createReportModality = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.healthReports

	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 //estado inicial
		proc.progress = 0
		proc = yield proc.save()

		var script = require('./proceses/report_modality')
		var data = yield application.addProcessToTail(script,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		errorManager.reportError(e.message,proc.id).next()	
	}
}
module.exports = HealthModule
