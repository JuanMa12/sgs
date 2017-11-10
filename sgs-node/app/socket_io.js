"use strict"

module.exports = function(app){

	//instancia de socket.io
	var server = require('http').createServer();
	var io = require('socket.io')(server);
	server.listen(app.context.parameters.io_port)
	console.log(`socket.io on port: ${app.context.parameters.io_port}`)
 
 	//demograpy process io service
	var demProc = io.of('/demography/process')
	demProc.on('connection',function(socket){
		console.log('Nueva conexion de procesos modulo '+socket.id)
	})
	app.context.demProc = demProc

	//demograpy reports io service
	var demReports = io.of('/demography/reports')
	demReports.on('connection',function(socket){
		console.log('Nueva conexion de repotes modulo '+socket.id)
	})
	app.context.demReports = demReports

	//finantial process io service
	var finantialProcess = io.of('/finantial/process')
	finantialProcess.on('connection',function(socket){
		console.log('Nueva conexion de procesos modulo '+socket.id)
	})
	app.context.finantialProcess = finantialProcess

	//finantial reports io service
	var finantialReports = io.of('/finantial/reports')
	finantialReports.on('connection',function(socket){
		console.log('Nueva conexion de reportes modulo '+socket.id)
	})
	app.context.finantialReports = finantialReports

	//health process io service
	var healthProcess = io.of('/health/process')
	healthProcess.on('connection',function(socket){
		console.log('Nueva conexion de procesos modulo '+socket.id)
	})
	app.context.healthProcess = healthProcess

	//health reports io service
	var healthReports = io.of('/health/reports')
	healthReports.on('connection',function(socket){
		console.log('Nueva conexion de reporte modulo '+socket.id)
	})
	app.context.healthReports = healthReports

	//operative reports io service
	var operativeReports = io.of('/operative/reports')
	operativeReports.on('connection',function(socket){
		console.log('Nueva conexion de reporte modulo '+socket.id)
	})
	app.context.operativeReports = operativeReports
}