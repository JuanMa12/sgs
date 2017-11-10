use sigg

db.suf.ensureIndex({"documentNumber":1,"healthEntityCode":1,"documentType":1},{unique:true})
db.suf.ensureIndex({"settlements.processId":1})
db.suf.ensureIndex({"activities.processId":1})