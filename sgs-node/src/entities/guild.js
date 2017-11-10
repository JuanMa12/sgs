"use strict";
var Sequelize = require('sequelize')

module.exports= function(sequelize) {
	//definicion del la entidad
	var model = sequelize.define('guild',{
		name: {type:Sequelize.STRING},
	},{
		timestamps: false,
		underscored:true,
		classMethods:{
			associate: function(entities) {
				this.hasMany(entities.health_promotion_entity)
			},
		},
		instanceMethods:{
			
		}
	})

	return model
}