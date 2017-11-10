"use strict";
var Sequelize = require('sequelize')

module.exports= function(sequelize) {
	//definicion del la entidad
	var model = sequelize.define('payer',{
		name: {type:Sequelize.STRING},
		divipola: {type:Sequelize.STRING},
	},{
		timestamps: false,
		underscored:true,
		classMethods:{
			associate: function(entities) {
				this.belongsTo(entities.payer_type)
			},
		},
		instanceMethods:{
			
		}
	})

	return model
}