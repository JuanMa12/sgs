"use strict";
var Sequelize = require('sequelize')

module.exports= function(sequelize) {
	//definicion del la entidad
	var model = sequelize.define('payer_type',{
		name: {type:Sequelize.STRING},
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