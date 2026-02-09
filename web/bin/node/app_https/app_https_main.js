/// 3007 preproduction 
/// 3008 production 

var port = ('/var/www/tac-tac.shop.mydde.fr/web/bin/node/app_https' == __dirname) ? '3007' : '3008';

var appsocket = require('./app_https_socket.js');
var cron = require('./app_cron.js');

console.log(port);

appsocket.socket_start(port);

