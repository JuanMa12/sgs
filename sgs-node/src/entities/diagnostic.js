"use strict";
var Sequelize = require('sequelize')

module.exports= function(sequelize) {
	//definicion del la entidad
	var model = sequelize.define('diagnostic',{
		code: {type:Sequelize.STRING},
		description: {type:Sequelize.STRING},
		diagnostic_group_id: {type:Sequelize.INTEGER},
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