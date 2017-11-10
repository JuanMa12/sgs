"use strict"
var mysql = require('mysql2')

var db_config;
var connection;

function handleDisconnect() {
	connection = mysql.createConnection(db_config); // Recreate the connection, since

	connection.on('error', function(err) {
		if(err.code === 'PROTOCOL_CONNECTION_LOST') { // Connection to the MySQL server is usually
			handleDisconnect();                         // lost due to either server restart, or a
		} else {                                      // connection idle timeout (the wait_timeout
			throw err;                                  // server variable configures this)
		}
	});
}

function customQuery(stm){
	return function(callback){
		connection.query(stm,function(err,rows){
			callback(err,rows)
		})
	}
}

var manager = {
	execute: function*(stm){
		var result = yield customQuery(stm)
		return result
	}
}

module.exports = function(app){

	let parameters = app.context.parameters.database_engine

	db_config = {
		host: parameters.host,
		user: parameters.username,
		database: parameters.database,
		password: parameters.password
	}

	app.context.appDbEngine = function(){
		handleDisconnect()
		return manager
	}

}