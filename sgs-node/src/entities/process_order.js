"use strict";
var Sequelize = require('sequelize')
const co = require('co')

module.exports= function(sequelize,app) {

	//definicion del la entidad
	var model = sequelize.define('process_order',{
		name: {type:Sequelize.STRING},
		status: {type:Sequelize.STRING},
		meta_data: {type:Sequelize.JSON},
	},{
		timestamps: false,
		underscored:true,
		classMethods:{
			associate: function(entities) {
				this.hasMany(entities.process)
				this.belongsTo(entities.process_order_type)
			},
		},
		getterMethods:{
			meta:function(){
				return JSON.parse(this.meta_data)
			}
		},
		setterMethods:{
			meta:function(value){
				this.setDataValue('meta_data',JSON.stringify(value))
			}
		},
		instanceMethods:{
			emitToFront:function(emitObjetc,message){

				let currentOrder = this
				let entities = app.context.entities

				//ejecucion asincrona para evitar latencia
				co(function*(){
					let orderProgress = 0
					let orderErrors = 0
					let activeProcess = 0
					
					//iteracion sobre procesos y errores por la relacion
					let processes = yield entities.process.findAll({
						where:{process_order_id:currentOrder.id},
						include: [ entities.process_error ]
					})

					for(let objProcess of processes){
						if(objProcess.status != 4){
							orderProgress += objProcess.progress
							orderErrors += objProcess.process_errors.length 
							activeProcess++
						}
					}

					orderProgress = orderProgress / activeProcess

					emitObjetc.emit('update',{
						"id":currentOrder.id,
						"progress":orderProgress,
						"errors":orderErrors,
						"status":currentOrder.status,
						"message":(message)?message:undefined
					})
				})
			}
		}
	})

	return model
}