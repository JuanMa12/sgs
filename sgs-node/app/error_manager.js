"use strict"

var moment = require('moment')

module.exports = function(app){

	let manager = {
		reportError: function*(msg,processId,severity){
			try{
				var error = app.context.entities.process_error.build()
				error.process_id = processId
				error.description = msg
				error.date = moment().format('X')
				error.severity = 3

				if(severity != undefined){
					error.severity = severity
				}

				error = yield error.save()
			}catch(e){
				console.log(e.message)
			}
		}
	}
	
	app.context.errorManager = manager
}