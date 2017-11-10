"use strict";
var Sequelize = require('sequelize')

module.exports= function(sequelize) {
	//definicion del la entidad
	var model = sequelize.define('municipality',{
		name: {type:Sequelize.STRING},
		code: {type:Sequelize.STRING},
		zone: {type:Sequelize.STRING},
		category: {type:Sequelize.STRING},
	},{
		timestamps: false,
		underscored:true,
		classMethods:{
			associate: function(entities) {
				this.belongsTo(entities.department)
			},
		},
		instanceMethods:{
			
		}
	})

	return model
}