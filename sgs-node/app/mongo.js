"use strict";
const MongoClient = require('mongodb').MongoClient

module.exports = function(app){

	var parameters = app.context.parameters

	function mongoConnect(){
		return new Promise(function(resolve,reject){
			let db = MongoClient.connect(parameters.mongo_host,{maxPoolSize:100},function(err,db){
				if(err)
					reject(err)

				console.log(`Conexion establecida mongodb: ${parameters.mongo_host}`)
				app.context.mongo = db

				resolve()
			})
		})
	}

	mongoConnect()

	return function*(next){
		if(!this.mongo){
			console.log("Restableciendo conexion con mongo")
			yield mongoConnect()
		}

		yield next
	}
}