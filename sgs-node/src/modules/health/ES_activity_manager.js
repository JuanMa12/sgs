"use strict"
const utils = require('./../utils')
const moment = require('moment')

let methods = {}

methods.getType2Activity = function(data,objProcess,mode){

	if(mode == 2016 && data.length != 18){
		let error = new Error('Numero de columnas no valido.')
		error.fatal = true
		throw error
	}else if(mode != 2016 && data.length != 17){
		let error = new Error('Numero de columnas no valido.')
		error.fatal = true
		throw error
	}
	 
	if(!(moment(data[9], 'YYYY-MM-DD',true).isValid())){
		let error = new Error('Formato de fecha de activdad no valido.')
		error.fatal = true
		throw error	
	}

	let objActivity = {
		reference:Number(data[1]),
		divipola:data[6],	
		diagnosticMainCode:data[7],	
		diagnosticSecondCode:data[8],
		date:Number(moment(data[9],'YYYY-MM-DD').format('X')),
		activityCode:data[10],
		ambit:data[11],
		paymentMethod: data[12],
		days: Number(data[13]),
		value: parseFloat(data[14]),
		userValue: parseFloat(data[15]),
		serviceProvider: data[16],
		processId: objProcess.id,
		type:2,
	}

	if(mode && mode == 2016){
		objActivity.serviceProvider = data[17]
		objActivity.copayment = parseFloat(data[16])
	}

	return objActivity
}

methods.getType3Activity = function(data,objProcess,mode){

	if(mode == 2016 && data.length != 18){
		let error = new Error('Numero de columnas no valido.')
		error.fatal = true
		throw error
	}else if(mode != 2016 && data.length != 17){
		let error = new Error('Numero de columnas no valido.')
		error.fatal = true
		throw error
	} 

	if(!(moment(data[9], 'YYYY-MM-DD',true).isValid())){
		let error = new Error('Formato de fecha de activdad no valido.')
		error.fatal = true
		throw error	
	}

	let objActivity = {
		reference:Number(data[1]),
		divipola:data[6],	
		diagnosticMainCode:data[7],
		diagnosticSecondCode:data[8],
		date:Number(moment(data[9],'YYYY-MM-DD').format('X')),
		activityCode:data[10],
		quantity:Number(data[11]),
		ambit:data[12],
		paymentMethod: data[13],
		value: parseFloat(data[14]),
		userValue: parseFloat(data[15]),
		serviceProvider: data[16],
		processId: objProcess.id,
		type:3,
	}

	if(mode && mode == 2016){
		objActivity.serviceProvider = data[17]
		objActivity.copayment = parseFloat(data[16])
	}

	return objActivity
}

methods.getType4Activity = function(data,objProcess){

	if(data.length != 21){
		let error = new Error('Numero de columnas no valido.')
		error.fatal = true
		throw error
	}

	if(!(moment(data[9], 'YYYY-MM-DD',true).isValid())){
		let error = new Error('Formato de fecha de activdad no valido.')
		error.fatal = true
		throw error	
	}

	let objActivity = {
		reference:Number(data[1]),
		divipola:data[6],	
		diagnosticMainCode:data[7],
		diagnosticSecondCode:data[8],
		date:Number(moment(data[9],'YYYY-MM-DD').format('X')),
		activityCode:data[10],
		concentrationQuantityDispensed:data[11],
		unitConcentration:data[12],
		pharmaceuticalForm: data[13],
		unitMeasureDispense: data[14],
		quantityDispensed: Number(data[15]),
		ambit: data[16],
		paymentMethod: data[17],
		value: parseFloat(data[18]),
		userValue: parseFloat(data[19]),
		serviceProvider: data[20],
		processId: objProcess.id,
		type:4,
	}

	return objActivity
}

methods.getType5Activity = function(data,objProcess,mode){

	if(mode == 2016 && data.length != 20){
		let error = new Error('Numero de columnas no valido.')
		error.fatal = true
		throw error
	}else if(mode != 2016 && data.length != 19){
		let error = new Error('Numero de columnas no valido.')
		error.fatal = true
		throw error
	}
	if(!(moment(data[9], 'YYYY-MM-DD',true).isValid())){
		let error = new Error('Formato de fecha de activdad no valido.')
		error.fatal = true
		throw error	
	}

	let objActivity = {
		reference:Number(data[1]),
		divipola:data[6],	
		diagnosticMainCode:data[7],
		diagnosticSecondCode:data[8],
		date:Number(moment(data[9],'YYYY-MM-DD').format('X')),
		noposCode:data[10],
		ambit: data[11],
		paymentMethod: data[12],
		days: data[13],
		noposValue: parseFloat(data[14]),
		noposUserValue: parseFloat(data[15]),
		serviceProvider: data[16],
		posCode: data[17],
		posValue: parseFloat(data[18]),
		processId: objProcess.id,
		type:5,
	}

	if(mode && mode == 2016){
		objActivity.copayment = parseFloat(data[16])
		objActivity.serviceProvider = data[17]
		objActivity.posCode = data[18]
		objActivity.posValue = parseFloat(data[19])
	}
	return objActivity
}

methods.getType6Activity = function(data,objProcess,mode){

	if(mode == 2016 && data.length != 25){
		let error = new Error('Numero de columnas no valido.')
		error.fatal = true
		throw error
	}else if(mode != 2016 && data.length != 24){
		let error = new Error('Numero de columnas no valido.')
		error.fatal = true
		throw error
	}

	if(!(moment(data[9], 'YYYY-MM-DD',true).isValid())){
		let error = new Error('Formato de fecha de activdad no valido.')
		error.fatal = true
		throw error	
	}

	let objActivity = {
		reference:Number(data[1]),
		divipola:data[6],	
		diagnosticMainCode:data[7],
		diagnosticSecondCode:data[8],
		date:Number(moment(data[9],'YYYY-MM-DD').format('X')),
		noposCode:data[10],
		quantity: Number(data[11]),
		ambit: data[12],
		paymentMethod: data[13],
		noposValue: parseFloat(data[14]),
		noposUserValue: parseFloat(data[15]),
		serviceProvider: data[16],
		posCode: data[17],
		posConcentration: data[18],
		posConcentrationUnit: data[19],
		posDosageForm: data[20],
		posUnitMeasurement: data[21],
		posQuantity: Number(data[22]),
		posValue: parseFloat(data[23]),
		processId: objProcess.id,
		type:6,
	}

	if(mode && mode == 2016){
		objActivity.copayment = parseFloat(data[16])
		objActivity.serviceProvider = data[17]
		objActivity.posCode = data[18]
		objActivity.posConcentration = data[19]
		objActivity.posConcentrationUnit = data[20]
		objActivity.posDosageForm = data[21]
		objActivity.posUnitMeasurement = data[22]
		objActivity.posQuantity =Number(data[23])
		objActivity.posValue = parseFloat(data[24])
	}

	return objActivity
}

methods.getType7Activity = function(data,objProcess){
	
	if(data.length != 9){
		let error = new Error('Numero de columnas no valido.')
		error.fatal = true
		throw error
	}

	if(!(moment(data[4], 'YYYY-MM-DD',true).isValid())){
		let error = new Error('Formato de fecha de servicio no valido.')
		error.fatal = true
		throw error	
	}

	let objActivity = {
		reference:Number(data[1]),
		serviceDate:Number(moment(data[4],'YYYY-MM-DD').format('X')),
		paymentDate:Number(moment(data[5],'YYYY-MM-DD').format('X')),
		paymentValue:Number(data[6]),
		invoiceNumber: data[7],
		paymentMethod: data[8],
		processId: objProcess.id,
		type:7,
	}

	return objActivity
}


methods.getType8Activity = function(data,objProcess,mode){

	if(mode == 2016 && data.length != 18){
		let error = new Error('Numero de columnas no valido.')
		error.fatal = true
		throw error
	}else if(mode != 2016 && data.length != 17){
		let error = new Error('Numero de columnas no valido.')
		error.fatal = true
		throw error
	}

	if(!(moment(data[9], 'YYYY-MM-DD',true).isValid())){
		let error = new Error('Formato de fecha de activdad no valido.')
		error.fatal = true
		throw error	
	}

	let objActivity = {
		reference:Number(data[1]),
		divipola:data[6],	
		diagnosticMainCode:data[7],
		diagnosticSecondCode:data[8],
		date: Number(moment(data[9],'YYYY-MM-DD').format('X')),
		activityCode:data[10],
		ambit:data[11],
		paymentMethod: data[12],
		quantity: Number(data[13]),
		value: parseFloat(data[14]),
		userValue: parseFloat(data[15]),
		serviceProvider: data[16], 
		processId: objProcess.id,
		type:8,
	}

	if(mode && mode == 2016){
		objActivity.copayment = parseFloat(data[16])
		objActivity.serviceProvider = data[17]
	}
	
	return objActivity
}

module.exports = methods