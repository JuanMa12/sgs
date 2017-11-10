"use strict"

var app = require('koa')()
var parser = require('koa-body')

app.use(parser({
	multipart: true,
}))


app.context.parameters = require('./parameters')

//base de datos mongo
let mongoChecker = require('./app/mongo')(app);
app.use(mongoChecker)//midleware que verifica conexion

require('./app/processTail')(app)

app.use(require('./app/routes')(app));

//base de datos usando ORM sequelize
require('./app/database')(app);


//adminitrador de errorres
require('./app/error_manager')(app);

//redis
require('./app/redis')(app);

//db project
require('./app/mysql_connection')(app)

//socket.io config
require('./app/socket_io')(app)



//inicializacion del servicio
var port = Number(process.env.PORT || app.context.parameters.port);
app.listen(port);
console.log('Listening on port: '+port);