/// 3007 preproduction tac-tac
/// 3008 production tac-tac

var port = ('/var/www/tac-tac.shop.mydde.fr/web/bin/node/app_https' == __dirname) ? '3007' : '3008';

var appsocket = require('./app_https_socket.js');
var cron = require('./app_cron.js');
// var appmiddleware = require ('./app_https_middleware.js')(appsocket);

console.log(port);

appsocket.socket_start(port);

