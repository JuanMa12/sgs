"use strict";
var Sequelize = require('sequelize')

module.exports= function(sequelize) {
	//definicion del la entidad
	var model = sequelize.define('diagnostic_group',{
		code: {type:Sequelize.STRING},
		name: {type:Sequelize.STRING},
		parent_id: {type:Sequelize.INTEGER},
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