"use strict"

var Redis = require("ioredis")

module.exports = function(app){

	let redis = new Redis()
	app.context.redis = redis
}