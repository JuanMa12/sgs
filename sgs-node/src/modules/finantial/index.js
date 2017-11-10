"use strict"
var co = require('co')

let FinantialModule = {}
	
/**
 * Cargue de informacion de cartera no POS radicada
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
 */
FinantialModule.loadNoPosR = function*(application,porder){

	let errorManager = application.errorManager
	let socket = application.finantialProcess
	
	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		porder.emitToFront(socket,`iniciando proceso de cargue de información`)
		//se corren los procesos en serie
		var LoadData = require('./proceses/load_nopos_r')
		var load = yield LoadData(application,porder,proc)
		var loadResult = yield load.start()

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(application.finantialProcess)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Cargue de informacion de cartera no POS no radicada
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
 */
FinantialModule.loadNoPosNR = function*(application,porder){

	let errorManager = application.errorManager
	let socket = application.finantialProcess
	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		porder.emitToFront(socket,`iniciando proceso de cargue de información`)
		//se corren los procesos en serie
		var LoadData = require('./proceses/load_nopos_nr')
		var load = yield LoadData(application,porder,proc)
		var loadResult = yield load.start()

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(application.finantialProcess)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Cargue de informacion de cartera POS 1080 de 2012
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
 */
FinantialModule.loadUpcPos = function*(application,porder){

	let errorManager = application.errorManager
	let socket = application.finantialProcess

	try{

		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		porder.emitToFront(socket,`iniciando proceso de cargue de información`)

		//se corren los procesos en serie
		var LoadData = require('./proceses/load_upc_pos')
		var load = yield LoadData(application,porder,proc)
		var loadResult = yield load.start()

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(application.finantialProcess)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Cargue de informacion lma giros
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
 */
FinantialModule.loadUpcPosLma = function*(application,porder){

	let errorManager = application.errorManager
	let socket = application.finantialProcess
	try{

		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		porder.emitToFront(socket,`iniciando proceso de cargue de información`)
		//se corren los procesos en serie
		var LoadData = require('./proceses/load_upc_pos_lma')
		var load = yield LoadData(application,porder,proc)
		var loadResult = yield load.start()

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(application.finantialProcess)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
} 

/**
 * Cargue de informacion de circular unica tipo 001
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
 */

FinantialModule.loadCUTypeOne = function*(application,porder){

	let errorManager = application.errorManager
	let socket = application.finantialProcess
	try{

		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		porder.emitToFront(socket,`iniciando proceso de cargue de información`)
		//se corren los procesos en serie
		var LoadData = require('./proceses/load_cu_type_one')
		var load = yield LoadData(application,porder,proc)
		var loadResult = yield load.start()

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(application.finantialProcess)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
} 

/**
 * Cargue de informacion de información de circular única tipo 002
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/	
FinantialModule.loadCUTypeTwo = function*(application,porder){

	let errorManager = application.errorManager
	let socket = application.finantialProcess

	try{

		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		porder.emitToFront(socket,`iniciando proceso de cargue de información`)
		//se corren los procesos en serie
		var LoadData = require('./proceses/load_cu_type_two')
		var load = yield LoadData(application,porder,proc)
		var loadResult = yield load.start()

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(application.finantialProcess)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
} 

/**
 * Cargue de circular única tipo 003
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/

FinantialModule.loadCUTypeThree = function*(application,porder){

	let errorManager = application.errorManager
	let socket = application.finantialProcess
	try{

		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		porder.emitToFront(socket,`iniciando proceso de cargue de información`)
		//se corren los procesos en serie
		var LoadData = require('./proceses/load_cu_type_three')
		var load = yield LoadData(application,porder,proc)
		var loadResult = yield load.start()

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(application.finantialProcess)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Cargue de informacion de circular única tipo 016
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
 */

FinantialModule.loadCUTypeSixteen = function*(application,porder){

	let errorManager = application.errorManager
	let socket = application.finantialProcess
	
	try{

		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		porder.emitToFront(socket,`iniciando proceso de cargue de información`)
		//se corren los procesos en serie
		var LoadData = require('./proceses/load_cu_type_sixteen')
		var load = yield LoadData(application,porder,proc)
		var loadResult = yield load.start()

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(application.finantialProcess)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Eliminación de registros de cartera no POS radicada
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/

FinantialModule.deleteNoPosRData = function*(application,porder){
	let socket = application.finantialProcess

	try{

		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let mysql = application.appDbEngine()

		porder.status = 5 // estado proceso eliminando
		yield  porder.save()

		for(let proc of porder.processes){
			if(proc.status != 4){
				//notificacion
				porder.emitToFront(socket,`Eliminando datos del proceso ${proc.id}`)
				let result = yield mysql.execute(`DELETE FROM finantial_upc_nopos_r_records WHERE process_id = ${proc.id}`)

				proc.status = 4//estado invalido
				proc = yield proc.save()
			}
		}

		porder.status = 4//estado proceso eliminado
		yield  porder.save()

		porder.emitToFront(socket,`Proceso de eliminación terminado`)
		console.log(`Proceso terminado`)

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Eliminación de registros de cartera no POS no radicada
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/

FinantialModule.deleteNoPosNRData = function*(application,porder){

	try{

		let socket = application.finantialProcess

		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let mysql = application.appDbEngine()

		porder.status = 5 // estado proceso eliminando
		yield  porder.save()

		for(let proc of porder.processes){
			if(proc.status != 4){
				//notificacion
				porder.emitToFront(socket,`Eliminando datos del proceso ${proc.id}`)
				let result = yield mysql.execute(`DELETE FROM finantial_upc_nopos_nr_records WHERE process_id = ${proc.id}`)

				proc.status = 4//estado invalido
				proc = yield proc.save()
			}
		}

		porder.status = 4//estado proceso eliminado
		yield  porder.save()

		porder.emitToFront(socket,`Proceso de eliminación terminado`)
		console.log(`Proceso terminado`)
	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
} 

/**
 * Eliminación de registros de cartera POS 1080 de 2012
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/

FinantialModule.deleteUpcPosData = function*(application,porder){
	let socket = application.finantialProcess

	try{

		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let mysql = application.appDbEngine()

		porder.status = 5 // estado proceso eliminando
		yield  porder.save()

		for(let proc of porder.processes){
			if(proc.status != 4){
				//notificacion
				porder.emitToFront(socket,`Eliminando datos del proceso ${proc.id}`)
				let result = yield mysql.execute(`DELETE FROM finantial_upc_pos_records WHERE process_id = ${proc.id}`)

				proc.status = 4//estado invalido
				proc = yield proc.save()
			}
		}

		porder.status = 4//estado proceso eliminado
		yield  porder.save()

		porder.emitToFront(socket,`Proceso de eliminación terminado`)
		console.log(`Proceso terminado`)

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
} 

/**
 * Eliminación de registros de cartera de esfuerzo propio
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/
FinantialModule.deleteSelfEffort = function*(application,porder){
	let socket = application.finantialProcess

	try{
		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let mysql = application.appDbEngine()

		porder.status = 5 // estado proceso eliminando
		yield  porder.save()

		for(let proc of porder.processes){
			//notificacion
			if(proc.status != 4){
				porder.emitToFront(socket,`Eliminando datos del proceso ${proc.id}`)

				let result = yield mysql.execute(`DELETE FROM finantial_self_effort WHERE process_id = ${proc.id}`)
				
				proc.status = 4//estado invalido
				proc = yield proc.save()
			}
		}

		porder.status = 4//estado proceso eliminado
		yield  porder.save()
		
		porder.emitToFront(socket,`Proceso de eliminación terminado`)
		console.log(`Proceso terminado`)
	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Eliminación de registros de ingresos lma giros
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/
FinantialModule.deletePosLmaData = function*(application,porder){

	let socket = application.finantialProcess

	try{

		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let mysql = application.appDbEngine()

		porder.status = 5 // estado proceso eliminando
		yield  porder.save()

		for(let proc of porder.processes){
			
			if(proc.status != 4){
				//notificacion
				porder.emitToFront(socket,`Eliminando datos de proceso ${proc.id}`)
				let result = yield mysql.execute(`DELETE FROM finantial_upc_pos_lma_records WHERE process_id = ${proc.id}`)
				
				proc.status = 4//estado invalido
				proc = yield proc.save()
			}

		}

		porder.status = 4//estado proceso eliminado
		yield  porder.save()

		porder.emitToFront(socket,`Proceso de eliminación terminado`)
		console.log(`Proceso terminado`)
	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Eliminación de registros de circular única tipo 001
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/
FinantialModule.deleteCUTypeOne = function*(application,porder){

	let socket = application.finantialProcess

	try{
		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let mysql = application.appDbEngine()

		porder.status = 5 // estado proceso eliminando
		yield  porder.save()

		for(let proc of porder.processes){
			if(proc.status != 4){
				//notificacion
				porder.emitToFront(socket,`Eliminando datos del proceso ${proc.id}`)
				let result = yield mysql.execute(`DELETE FROM finantial_ct1 WHERE process_id = ${proc.id}`)

				proc.status = 4//estado invalido
				proc = yield proc.save()
			}
		}

		porder.status = 4//estado proceso eliminado
		yield  porder.save()

		porder.emitToFront(socket,`Proceso de eliminación terminado`)
		console.log(`Proceso terminado`)
	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Eliminación de registros de circular única tipo 002
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/
FinantialModule.deleteCUTypeTwo = function*(application,porder){
	let socket = application.finantialProcess

	try{

		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let mysql = application.appDbEngine()

		porder.status = 5 // estado proceso eliminando
		yield  porder.save()

		for(let proc of porder.processes){
			if(proc.status != 4){
				//notificacion
				porder.emitToFront(socket,`Eliminando datos del proceso ${proc.id}`)
				let result = yield mysql.execute(`DELETE FROM finantial_ct2 WHERE process_id = ${proc.id}`)

				proc.status = 4//estado invalido
				proc = yield proc.save()
			}
		}

		porder.status = 4//estado proceso eliminado
		yield  porder.save()

		porder.emitToFront(socket,`Proceso de eliminación terminado`)
		console.log(`Proceso terminado`)
	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Eliminación de registros de circular única tipo 003
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/
FinantialModule.deleteCUTypeThree = function*(application,porder){
	let socket = application.finantialProcess

	try{

		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let mysql = application.appDbEngine()

		porder.status = 5 // estado proceso eliminando
		yield  porder.save()

		for(let proc of porder.processes){
			if(proc.status != 4){
				//notificacion
				porder.emitToFront(socket,`Eliminando datos del proceso ${proc.id}`)
				let result = yield mysql.execute(`DELETE FROM finantial_ct3 WHERE process_id = ${proc.id}`)

				proc.status = 4//estado invalido
				proc = yield proc.save()
			}
		}

		porder.status = 4//estado proceso eliminado
		yield  porder.save()

		porder.emitToFront(socket,`Proceso de eliminación terminado`)
		console.log(`Proceso terminado`)
	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
} 

/**
 * Eliminación de registros de circular única tipo 008
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/
FinantialModule.deleteCUTypeEight = function*(application,porder){
	let socket = application.finantialProcess

	try{
		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let mysql = application.appDbEngine()

		porder.status = 5 // estado proceso eliminando
		yield  porder.save()

		for(let proc of porder.processes){
			if(proc.status != 4){
				//notificacion
				porder.emitToFront(socket,`Eliminando datos del proceso ${proc.id}`)
				let result = yield mysql.execute(`DELETE FROM finantial_ct8 WHERE process_id = ${proc.id}`)

				proc.status = 4//estado invalido
				proc = yield proc.save()
			}
		}

		porder.status = 4//estado proceso eliminado
		yield  porder.save()

		porder.emitToFront(socket,`Proceso de eliminación terminado`)
		console.log(`Proceso terminado`)
	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
} 

/**
 * Eliminación de registros de circular única tipo 016
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/
FinantialModule.deleteCUTypeSixteen = function*(application,porder){
	let socket = application.finantialProcess

	try{
		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let mysql = application.appDbEngine()

		porder.status = 5 // estado proceso eliminando
		yield  porder.save()

		for(let proc of porder.processes){
			if(proc.status != 4){
				//notificacion
				porder.emitToFront(socket,`Eliminando datos del proceso ${proc.id}`)
				let result = yield mysql.execute(`DELETE FROM finantial_ct16 WHERE process_id = ${proc.id}`)

				proc.status = 4//estado invalido
				proc = yield proc.save()
			}
		}

		porder.status = 4//estado proceso eliminado
		yield  porder.save()

		porder.emitToFront(socket,`Proceso de eliminación terminado`)
		console.log(`Proceso terminado`)
	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}	
}

/**
 * Eliminación de registros de circular única tipo 027
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/
FinantialModule.deleteCUTypeTwentySeven = function*(application,porder){
	let socket = application.finantialProcess

	try{
		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let mysql = application.appDbEngine()

		porder.status = 5 // estado proceso eliminando
		yield  porder.save()

		for(let proc of porder.processes){
			if(proc.status != 4){
				//notificacion
				porder.emitToFront(socket,`Eliminando datos del proceso ${proc.id}`)
				let result = yield mysql.execute(`DELETE FROM finantial_ct27 WHERE process_id = ${proc.id}`)

				proc.status = 4//estado invalido
				proc = yield proc.save()
			}
		}

		porder.status = 4//estado proceso eliminado
		yield  porder.save()

		porder.emitToFront(socket,`Proceso de eliminación terminado`)
		console.log(`Proceso terminado`)
	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Eliminación de registros de circular única tipo 150
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/
FinantialModule.deleteCUTypeHundredFifty = function*(application,porder){
	let socket = application.finantialProcess

	try{
		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let mysql = application.appDbEngine()
	
		porder.status = 5 // estado proceso eliminando
		yield  porder.save()
	
		for(let proc of porder.processes){
			if(proc.status != 4){
				//notificacion
				porder.emitToFront(socket,`Eliminando datos del proceso ${proc.id}`)
				let result = yield mysql.execute(`DELETE FROM finantial_ct150 WHERE process_id = ${proc.id}`)
	
				proc.status = 4//estado invalido
				proc = yield proc.save()
			}
		}
	
		porder.status = 4//estado proceso eliminado
		yield  porder.save()
	
		porder.emitToFront(socket,`Proceso de eliminación terminado`)
		console.log(`Proceso terminado`)
	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Eliminación de registros de circular única tipo 151
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/
FinantialModule.deleteCUTypeHundredFiftyOne = function*(application,porder){
	let socket = application.finantialProcess

	try{
		porder = yield porder.reload({include:{all:true}})
		console.log(`Eliminando registros de la orden de proceso ${porder.id}`)
		
		let mysql = application.appDbEngine()
	
		porder.status = 5 // estado proceso eliminando
		yield  porder.save()
	
		for(let proc of porder.processes){
			if(proc.status != 4){
				//notificacion
				porder.emitToFront(socket,`Eliminando datos del proceso ${proc.id}`)
				let result = yield mysql.execute(`DELETE FROM finantial_ct151 WHERE process_id = ${proc.id}`)
	
				proc.status = 4//estado invalido
				proc = yield proc.save()
			}
		}
	
		porder.status = 4//estado proceso eliminado
		yield  porder.save()
	
		porder.emitToFront(socket,`Proceso de eliminación terminado`)
		console.log(`Proceso terminado`)
	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Reporte  cartera no POS
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/
FinantialModule.createNoPosReport = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports

	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso para la generacion del reporte
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		//se lanza el proceso
		var report = require('./proceses/report_nopos_r')
		
		if (porder.meta.source == '_nr') {
			report = require('./proceses/report_nopos_nr')
		}

		var data = yield report(application,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(application.finantialReports)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Reporte ingresos lma giros
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/
FinantialModule.createPosLmaReport = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports

	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()


		//se crea un proceso para la generacion del reporte
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		//se corren los procesos en serie
		var report = require('./proceses/report_upc_pos_lma')
		var data = yield report(application,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(application.finantialReports)

		console.log("orden terminada")
	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Reporte conteos de registros
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
FinantialModule.createRecordCountReport = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports

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
		var report = require('./proceses/report_rc')
		var data = yield report(application,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))
		
		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Reporte de suficiencia por eps y periodo
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
FinantialModule.createESReport = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports

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

		var report = require('./proceses/report_es')
		var data = yield report(application,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}
/**
 * Reporte de ingresos LMA y periodo
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
FinantialModule.createLMAReport = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports
	
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

		var report = require('./proceses/report_lma')
		var data = yield report(application,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}
/**
 * Reporte de circular unica tipo 1
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
FinantialModule.circularTypeOne = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports
	
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

		var report = require('./proceses/report_one_circular')
		var data = yield report(application,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}
/**
 * Reporte de circular unica tipo 2
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
FinantialModule.circularTypeTwo = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports
	
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

		var report = require('./proceses/report_two_circular')
		var data = yield report(application,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}
/**
 * Reporte de formato 1080
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
FinantialModule.createNoReport = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports

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

		var report = require('./proceses/report_upc_pos')
		var data = yield report(application,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
} 

/**
 * Registros formato cartera esfuerzo propio
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
FinantialModule.loadSelfEffort = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialProcess

	try{
		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		porder.emitToFront(socket,`iniciando proceso de cargue de información`)
		//se corren los procesos en serie
		var LoadData = require('./proceses/load_self_effort')
		var load = yield LoadData(application,porder,proc)
		var loadResult = yield load.start()

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(application.finantialProcess)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Reporte de cartera esfuerzo propio
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
FinantialModule.createSelfEffortReport = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports

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

		var report = require('./proceses/report_self_effort')
		var data = yield report(application,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Reporte de circular unica tipo 003
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
FinantialModule.circularTypeThree = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports

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

		var report = require('./proceses/report_three_circular')
		var data = yield report(application,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Cargue de informacion de circular única tipo 008
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
 */

FinantialModule.loadCUTypeEight = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialProcess
	
	try{

		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		porder.emitToFront(socket,`iniciando proceso de cargue de información`)
		//se corren los procesos en serie
		var LoadData = require('./proceses/load_cu_type_eight')
		var load = yield LoadData(application,porder,proc)
		var loadResult = yield load.start()

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(application.finantialProcess)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}
/**
 * Reporte de circular unica tipo 008
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
FinantialModule.circularTypeEight = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports
	
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

		var report = require('./proceses/report_eight_circular')
		var data = yield report(application,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Reporte de circular unica tipo 016
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
FinantialModule.circularTypeSixteen = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports
	
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

		var report = require('./proceses/report_sixteen_circular')
		var data = yield report(application,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Cargue de circular única tipo 027
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/

FinantialModule.loadCUTypeTwentySeven = function*(application,porder){

	let errorManager = application.errorManager
	let socket = application.finantialProcess

	try{

		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		porder.emitToFront(socket,`iniciando proceso de cargue de información`)
		//se corren los procesos en serie
		var LoadData = require('./proceses/load_cu_type_twenty_seven')
		var load = yield LoadData(application,porder,proc)
		var loadResult = yield load.start()

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
} 

/**
 * Reporte de circular unica tipo 027
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
FinantialModule.circularTypeTwentySeven = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports
	
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

		var report = require('./proceses/report_twenty_seven_circular')
		var data = yield report(application,porder,proc,socket)

		let redis = application.redis
		let redisResult = yield redis.set(porder.id,JSON.stringify(data))

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		porder.status = 3//orden fallida
		porder = yield porder.save()
		porder.emitToFront(socket,e.message)
	}
}

/**
 * Cargue de circular única tipo 150
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/

FinantialModule.loadCUTypeHundredFifty = function*(application,porder){

	let errorManager = application.errorManager
	let socket = application.finantialProcess

	try{

		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		porder.emitToFront(socket,`iniciando proceso de cargue de información`)
		//se corren los procesos en serie
		var LoadData = require('./proceses/load_cu_type_hundred_fifty')
		var load = yield LoadData(application,porder,proc)
		var loadResult = yield load.start()

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		errorManager.reportError(e.message,proc.id).next()	
	}
} 

/**
 * Reporte de circular unica tipo 150
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
FinantialModule.circularTypeHundredFifty = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports
	
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

		var report = require('./proceses/report_hundred_fifty_circular')
		var data = yield report(application,porder,proc,socket)

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
/**
 * Cargue de circular única tipo 151
 * @param  {Object} application Objeto de aplicacion
 * @param  {Object} porder      Objeto de orden de proceso
*/

FinantialModule.loadCUTypeHundredFiftyOne = function*(application,porder){

	let errorManager = application.errorManager
	let socket = application.finantialProcess

	try{

		//marcamos la orden del proceso como en proceso
		porder.status = 1
		porder = yield porder.save()

		//se crea un proceso de subida de informacion
		var proc = application.entities.process.build()
		proc.process_order_id = porder.id
		proc.status = 0 // estado inicial
		proc.progress = 0
		proc = yield proc.save()

		porder.emitToFront(socket,`iniciando proceso de cargue de información`)
		//se corren los procesos en serie
		var LoadData = require('./proceses/load_cu_hundred_fifty_one')
		var load = yield LoadData(application,porder,proc)
		var loadResult = yield load.start()

		porder.status = 2//orden terminada
		porder = yield porder.save()
		porder.emitToFront(socket)

		console.log("orden terminada")

	}catch(e){
		console.error(e.stack)
		errorManager.reportError(e.message,proc.id).next()	
	}
}
/**
 * Reporte de circular unica tipo 151
 * @param  {[type]} application [description]
 * @param  {[type]} porder      [description]
 * @return {[type]}             [description]
 */
FinantialModule.circularTypeHundredFiftyOne = function*(application,porder){
	let errorManager = application.errorManager
	let socket = application.finantialReports
	
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

		var report = require('./proceses/report_hundred_fifty_one_circular')
		var data = yield report(application,porder,proc,socket)

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
module.exports = FinantialModule

