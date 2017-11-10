"use strict";
var Sequelize = require('sequelize')

module.exports= function(sequelize) {
	//definicion del la entidad
	var model = sequelize.define('health_promotion_entity',{
		name: {type:Sequelize.STRING},
		code: {type:Sequelize.STRING},
		code_mobility: {type:Sequelize.STRING},
		alias: {type:Sequelize.STRING},
	},{
		timestamps: false,
		underscored:true,
		classMethods:{
			associate: function(entities) {
				
			},
		},
		instanceMethods:{
			
		}
	})

	return model
}