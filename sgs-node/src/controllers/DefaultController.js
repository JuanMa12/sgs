"use strict"
var co = require('co')
var DefaultController = {}

module.exports = function(){

	var FinantialModule = require('../modules/finantial/')
	var HealthModule = require('../modules/health/')

	DefaultController.index = function*(){
		this.body = "<h2>Funcionando...</h2>"
	}

	/**
	 * Accion que da inicio a una orden de proceso
	 */
	DefaultController.startProcess = function*(){
		try{
			var porder = yield this.entities.process_order.findById(this.params.id)
			if(!porder) throw new Error('La orden no fue encuentrada')
			if(porder.status != 0) throw new Error('La orden no se encuentra en estado inicial')

			let application = this

			co(function*(){

				let startProcess

				switch(porder.process_order_type_id){
					case 13://Proceso de subida LMA
						startProcess = HealthModule.loadLMA
						break
					case 24://prima pura
					case 14://estudio de suficiencia
						startProcess = HealthModule.loadES
						break
					case 18://reporte de estudio de suficiencia
						if(porder.meta.periods == undefined) throw new Error("Periodos no especificados")
						startProcess = FinantialModule.createESReport
						break
					case 20://reporte de ingresos LMA
						if(porder.meta.periods == undefined) throw new Error("Periodos no especificados")
						startProcess = FinantialModule.createLMAReport
						break
					case 21://reporte de frecuencias y costos
						if(porder.meta.periods == undefined) throw new Error("Periodos no especificados")
						startProcess = HealthModule.createFrecReport
						break
					case 22://reporte de extencion de uso
						if(porder.meta.periods == undefined) throw new Error("Periodos no especificados")
						startProcess = HealthModule.createExtReport
						break
					case 28://reporte de diagnosticos
						if(porder.meta.periods == undefined) throw new Error("Periodos no especificados")
						startProcess = HealthModule.createDiagReport
						break
					case 29://reporte de procedimientos
						if(porder.meta.periods == undefined) throw new Error("Periodos no especificados")
						startProcess = HealthModule.createProdReport
						break
					case 30://Intensidad de uso
						if(porder.meta.periods == undefined) throw new Error("Periodos no especificados")
						startProcess = HealthModule.createIntReport
						break
					case 31://Proceso de marcado de calidad
						startProcess = HealthModule.quality
						break
					case 38://reporte de cartera esfuerzo propio
						if(porder.meta.periods == undefined) throw new Error("Periodos no especificados")
						startProcess = HealthModule.createEpidemiologicReport
						break
					case 45://Reporte por ambito
						startProcess = HealthModule.createReportAmbit
						break 
					case 46://Reporte por modalidad de pago
						startProcess = HealthModule.createReportModality
						break
					default:
						throw new Error(`Tipo de orden no compatible ${porder.process_order_type_id}`)
				}
				yield startProcess(application,porder)
				
			}).catch(function(e){
				console.log(e)
			})
				
			//Procesamiento de datos
			this.body = {status:true,description:'Proceso iniciado'}

		}catch(e){
			console.log(e.stack)
			var errorManager = this.errorManager
			let result = yield errorManager.reportError(e.message)
			this.body = {status:false,description:e.message}
		}
	}

	/**
	 * Accion que elimina los datos de una orden de proceso
	 * @yield {[type]} [description]
	 */
	DefaultController.deleteProcess = function*(){
		try{

			var porder = yield this.entities.process_order.findById(this.params.id)
			if(!porder) throw new Error('El proceso no fue encontrado..')

			let application = this

			let deleteProcess

			switch(porder.process_order_type_id){
				case 13:
					deleteProcess = HealthModule.deleteLMAData
					break
				case 24:
				case 14:
					deleteProcess = HealthModule.deleteESData	
					break
				case 31:					
					deleteProcess = HealthModule.clearQualityProcess	
					break	
				default:
					throw new Error("Tipo de orden no compatible")
			}

			co(function*(){
				yield deleteProcess(application,porder)
			})

			this.body = {status:true,description:'El proceso de eliminacion ha comenzado'}

		}catch(e){
			console.log(e.stack)
			var errorManager = this.errorManager
			yield errorManager.reportError(e.message)
			this.body = {status:false,description:e.message}
		}
	}

	/**
	 * Accion que elimina los datos de una orden de proceso
	 * @yield {[type]} [description]
	 */
	DefaultController.restartProcess = function*(){
		try{
			var porder = yield this.entities.process_order.findById(this.params.id)
			if(!porder) throw new Error('El proceso no fue encontrado..')

			let deleteProcess 
			let startProcess

			switch(porder.process_order_type_id){
				case 13:
					deleteProcess = HealthModule.deleteLMAData
					startProcess = HealthModule.loadLMA
					break
				case 24:
				case 14:
					deleteProcess = HealthModule.deleteESData
					startProcess = HealthModule.loadES
					break
				case 18:
					startProcess = FinantialModule.createESReport
					break
				case 20:
					startProcess = FinantialModule.createLMAReport
					break
				case 21:
					startProcess = HealthModule.createFrecReport
					break
				case 22:
					startProcess = HealthModule.createExtReport
					break

				case 28:
					startProcess = HealthModule.createDiagReport
					break
				case 29:
					startProcess = HealthModule.createProdReport
					break
				case 30:
					startProcess = HealthModule.createIntReport
					break
				case 31:					
					deleteProcess = HealthModule.clearQualityProcess
					startProcess = HealthModule.quality
					break
				case 38:
					startProcess = HealthModule.createEpidemiologicReport
					break
				case 45:
					startProcess = HealthModule.createReportAmbit
					break
				case 46:
					startProcess = HealthModule.createReportModality
					break 
				default:
					throw new Error("Tipo de orden no compatible")
			}

			let application = this
			co(function*(){
				if(deleteProcess){
					yield deleteProcess(application,porder)
				}

				porder.status = 0
				yield porder.save()
				yield startProcess(application,porder)
			}).catch(function(e){
				console.log(e)
			})

			this.body = {status:true,description:'El proceso de reinicio ha comenzado'}

		}catch(e){
			console.log(e.stack)
			var errorManager = this.errorManager
			yield errorManager.reportError(e.message)
			this.body = {status:false,description:e.message}
		}
	}

	/**
	 * Accion que retorna los datos de un repote
	 * @yield {[type]} [description]
	 */
	DefaultController.getReportData = function*(){
		try{
			let params = this.params

			var porder = yield this.entities.process_order.findById(params.id,{
				include: [{ all: true }]
			})

			if(!porder) throw new Error('El proceso no fue encuentrado')
			
			//Procesamiento de datos
			let redis = this.redis
			let res = yield redis.get(params.id)

			if(!res) throw new Error('Datos no encontrados')
			let data = JSON.parse(res)

			this.body = {status:true,description:data}
			
		}catch(e){
			console.log(e.stack)
			var errorManager = this.errorManager
			let result = yield errorManager.reportError(e.message,this.params.id)
			this.body = {status:false,description:e.message}
		}
	}

	return DefaultController
}
