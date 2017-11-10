"use strict";
var Sequelize = require('sequelize')

module.exports= function(sequelize) {
	//definicion del la entidad
	var model = sequelize.define('process',{
		status: {type:Sequelize.STRING},
		progress: {type:Sequelize.STRING}
	},{
		timestamps: false,
		underscored:true,
		classMethods:{
			associate: function(entities) {
				this.belongsTo(entities.process_order)
				this.hasMany(entities.process_error)
			},
		},
		instanceMethods:{
			
		}
	})

	return model
}