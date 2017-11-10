"use strict";
var Sequelize = require('sequelize')

module.exports= function(sequelize) {
	//definicion del la entidad
	var model = sequelize.define('process_error',{
		process_id: {type:Sequelize.INTEGER},
		description: {type:Sequelize.STRING},
		severity: {type:Sequelize.STRING},
		date: {type:Sequelize.STRING}
	},{
		timestamps: false,
		underscored:true,
		classMethods:{
			associate: function(entities) {
				this.belongsTo(entities.process)
			},
		},
		instanceMethods:{
			
		}
	})

	return model
}