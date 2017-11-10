"use strict";
var Sequelize = require('sequelize')

module.exports= function(sequelize) {
	//definicion del la entidad
	var model = sequelize.define('department',{
		name: {type:Sequelize.STRING},
		code: {type:Sequelize.STRING},	
	},{
		timestamps: false,
		underscored:true,
		classMethods:{
			associate: function(entities) {
				this.hasMany(entities.municipality)
			},
		},
		instanceMethods:{
			
		}
	})

	return model
}