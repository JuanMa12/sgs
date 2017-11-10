"use strict"
const LineByLineReader = require('line-by-line')
const fs = require('fs')
const moment = require('moment')
const archiver = require('archiver')
const path = require('path')
const formatter = require('number-formatter')

module.exports = {
	getFileLinesCount : function(path){
		return function(callback){
			var count = 1;
    		fs.createReadStream(path)
	        .on('data', function(chunk) {
	            for (var i = 0; i < chunk.length; i++)
	                if (chunk[i] == 10) count++;
	        })
	        .on('end', function() {
				callback(null,count)
	            
	        });
		}
	},

	appendIntoCsv:function(lines,path){
		return function(callback){
			let arrLines = []
			for(let line of lines){
				let strLine = line.join(';')
				arrLines.push(strLine)	
			}
		
			let strLines = arrLines.join("\n")

			fs.appendFileSync(path,strLines+"\n")

			callback(null,'ok')
		}
	},

	getUnixDate:function(strDate){
		let format = 'DD/MM/YYYY'
		if(strDate.length > 10){
			format = 'DD/MM/YYYY HH:ss'
		}

		return moment(strDate,format).format('X')
	},

	compilePeriods:function(periods){
		let arrPeriods = []
		for (let date of periods) {

			let currentPeriod = {initDate:date}
			let createFlag = true

			for(let period of arrPeriods){
				if(period.endDate == date){
					currentPeriod = period
					createFlag = false
					break
				}
			}

			currentPeriod.endDate = moment(date,'X').add(1, 'months').format('X')

			if(createFlag){
				arrPeriods.push(currentPeriod)
			}

		}

		return arrPeriods
	},

	validateDateByPeriods:function(date,periods){
		for(let period of periods){
			if(Number(date) >= Number(period.initDate) && Number(date) < Number(period.endDate)){
				return true
			}
		}

		return false
	},

	compressFiles:function(outputPath,arrFiles){
		return function(callback){
			let output = fs.createWriteStream(outputPath);
			let zipArchive = archiver('zip');

			zipArchive.pipe(output)

			output.on('close', function() {
			    callback(null,'ok')
			})

			for(let filePath of arrFiles){
				zipArchive.file(filePath,{name:path.basename(filePath)});
			}

			zipArchive.finalize(function(err, bytes) {
			    if(err) {
			      throw err;
			    }
			});
		}
	},

	getQuinquennialGroup:function(age,genre){
		
		if(age <= 1){		
			return "Menor a 1"
		}else if(age <= 4){
			return'Entre 1 y 4'
		}else if(age <= 14){
			return'Entre 5 y 14'
		}else if(age <= 18){
			if(genre == 'F'){
				return'Entre 15 y 18 M'
			}
			return'Entre 15 y 18 H'
		}else if(age <= 44){
			if(genre == 'F'){
				return'Entre 19 y 44 M'
			}
			return'Entre 19 y 44 H'
		}else if(age <= 49){
			return'Entre 45 y 49'
		}else if(age <= 54){
			return'Entre 50 y 54'
		}else if(age <= 59){
			return'Entre 55 y 59'
		}else if(age <= 64){
			return'Entre 60 y 64'
		}else if(age <= 69){
			return'Entre 65 y 69'
		}else if(age <= 74){
			return'Entre 70 y 74'
		}
		
		return'De 75 y más'
	},

	getNumberFormat:function(number){
		if (number == 0) {
			return number
		}

		return formatter("###,##",number)
	},	

	validateReadableCharacter:function(str){
		if(/^[a-zA-Z0-9-\/\ñáéíóúÑÁÉÍÓÚüÜ;.:'\s\-,]*$/.test(str) == false) {
		    return false
		}
		return true
	},
	
	getCollectionName:function*(db,source,period,forceCreate){
		let arrSources = ['suf','pip']

		let collectionName = ''
		if(period == 2015){
			collectionName = source
		}else{
			collectionName = `${source}_${period}`
		}

		let collections = yield db.listCollections().toArray()
		
		let found = false
		for(let collection of collections){
			if(collection.name == collectionName){
				found = true
				break
			}
		}

		if(!found && forceCreate){
			let collection = yield db.createCollection(collectionName)
			yield collection.createIndex({"healthEntityCode":1,"documentNumber":1,"documentType":1},{unique:true})
			yield collection.createIndex({"settlements.processId":1})
			yield collection.createIndex({"activities.processId":1})
			
			let stats = yield collection.stats()

			if (stats.shards) {				
				yield db.command({
					shardCollection:`sigg.${collectionName}`,
					key:{"healthEntityCode":1,"documentNumber":1,"documentType":1},
					unique:true
				})		
			}
		}
		return collectionName
	},

	getArrYears:function(periods){
		let arrYears = []
		for (let date of periods) {
			let year = moment(date,'X').format('YYYY')
			if(arrYears.indexOf(year) < 0){
				arrYears.push(year)				
			}
		}
		return arrYears
	},

	getArrQuarters:function(periods,mode){
		let objResult = {}

		let arrQuarters = [1,2,3,4]
		if(mode==true){
			arrQuarters = [40,41,42,43]
		}

		for (let date of periods) {
			let year = moment(date,'X').format('YYYY')
			let month = moment(date,'X').format('M')

			if(!objResult[year]){
				objResult[year] = []
			}

			if (month >=1 && month <= 3) {
				if(objResult[year].indexOf(arrQuarters[0]) < 0){
					objResult[year].push(arrQuarters[0])			
				}
			}else if(month >=4 && month <= 6){
				if(objResult[year].indexOf(arrQuarters[1]) < 0){
					objResult[year].push(arrQuarters[1])
				}
			}else if(month >=7 && month <= 9){
				if(objResult[year].indexOf(arrQuarters[2]) < 0){
					objResult[year].push(arrQuarters[2])
				}
			}else if(month >=10 && month <= 12){
				if(objResult[year].indexOf(arrQuarters[3]) < 0){
					objResult[year].push(arrQuarters[3])
				}
			}
		}

		return objResult
	},

	getperiodName: function(number){
		switch(Number(number)){
			case 1:
			case 40:
				return 'Enero a Marzo'					
			case 2:
			case 41:
				return 'Abril a Junio'									
			case 3:
			case 42:
				return 'Julio a Septiembre'									
			case 4:
			case 43:
				return 'Octubre a Diciembre'	
		}
	},
}