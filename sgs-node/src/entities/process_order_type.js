"use strict";
var Sequelize = require('sequelize')

module.exports= function(sequelize) {
	//definicion del la entidad
	var model = sequelize.define('process_order_type',{
		name: {type:Sequelize.STRING},
		module: {type:Sequelize.STRING},
		type: {type:Sequelize.STRING}
	},{
		timestamps: false,
		underscored:true,
		classMethods:{
			associate: function(entities) {
				this.hasMany(entities.process_order)
			},
		},
		instanceMethods:{
			
		}
	})

	return model
}