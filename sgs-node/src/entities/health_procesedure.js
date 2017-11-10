"use strict";
var Sequelize = require('sequelize')

module.exports= function(sequelize) {
	//definicion del la entidad
	var model = sequelize.define('health_procesedure',{
		code: {type:Sequelize.STRING},
		description: {type:Sequelize.STRING},
		type: {type:Sequelize.STRING},
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