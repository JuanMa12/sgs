"use strict";
//https://github.com/alexmingoia/koa-router
var Router= require('koa-router')

var secured = function *(next) {
	//console.log(this.request.body)
	yield next
}

module.exports=function(app){
	var router= new Router();

	// Controllers
	var defaultController = require('../src/controllers/DefaultController')()
	
	//site routes
	router.get('/', secured, defaultController.index)
	
	//reportes
	router.get('/report/:id', secured, defaultController.getReportData)

	//procesos
	router.put('/porder/:id', secured, defaultController.startProcess)
	router.post('/porder/:id', secured, defaultController.restartProcess)
	router.delete('/porder/:id', secured, defaultController.deleteProcess)

	//auth routes
	//router.get('/login', securityController.index)

	app.context.router = router
	
	return router.middleware();
}