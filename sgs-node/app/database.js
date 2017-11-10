"use strict";
var Sequelize = require('sequelize')
var fs = require("fs")

var loadModels = function(sequelize){
	
} 

module.exports = function(app){

	var parameters = app.context.parameters.database

	if(typeof parameters != undefined && parameters.host != null){
		var sequelize = new Sequelize(
				parameters.database,
				parameters.username,
				parameters.password,{
			dialect:parameters.dialect,
			host:parameters.host,
			port:3306,
			logging:false
		})

		sequelize.authenticate().then(function(){

			var entitiesPath = __dirname+'/../src/entities/'
			var entities = {}

			fs.readdirSync(entitiesPath).forEach(function(file) {
				var name = file.split(".")[0]
				if(name!='index'){
					entities[name] = require(entitiesPath+name)(sequelize,app)
				}
			});

			//Se cargan relaciones
			Object.keys(entities).forEach(function(modelName) {
				if ("associate" in entities[modelName]) {
					entities[modelName].associate(entities);
				}
			});

			app.context.entities = entities
		})

		app.context.sequelize = sequelize
	}
}