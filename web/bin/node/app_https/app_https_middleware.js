/**
 * Created by Mydde on 28/09/2016.
 */


	var   http    =   require('http')
	,   connect =   require('connect')
	, 	fs 		= 	require('fs')
	, 	qs 		= 	require('qs')
	, 	url 	= 	require('url')
	, 	request =  	require('request') ;

// var io;

function http_handler(req, res) {
	//
	console.log('http_handler : ');

	if (req.url === '/favicon.ico') {
		res.writeHead(200, {'Content-Type': 'image/x-icon'} );
		res.end();
		return;
	}
	var path = url.parse(req.url).pathname;

	switch (path){
		case '/postScope':
			// DOCUMENTDOMAIN
			var fullBody = '';
			req.on('data', function(chunk) {
				fullBody += chunk.toString();
				if (fullBody.length > 1e6) {
					req.connection.destroy();
				}
			});
			req.on('end', function() {
				data = qs.parse(fullBody);
				//
				reloadVars={scope:data.scope,value:data.value}
				if(data.vars) reloadVars.vars = qs.stringify(data.vars)
				if(data.scope && data.value){
					io.sockets.emit('reloadScope',reloadVars);
				}
				res.writeHead(200, {'Content-Type': 'text/html'})
				res.end();
			});
			break;
		case "/run":
			var fullBody = '';
			req.on('data', function(chunk) {
				fullBody += chunk.toString();
				if (fullBody.length > 1e6) {
					req.connection.destroy();
				}
			});
			req.on('end', function() {
				data = qs.parse(fullBody);

				data.vars = data.vars || ''
				data.options = data.options || {}
				data.vars.defer = '';
				SESSID = data.SESSID || '';
				PHPSESSID = data.PHPSESSID || 'none';
				DOCUMENTDOMAIN = data.DOCUMENTDOMAIN;
				//
				var url = 'http://'+DOCUMENTDOMAIN+'/'+data.mdl+'.php'

				request.post({
					url	:	url ,
					method: 'POST',
					headers:{'Cookie':'PHPSESSID='+PHPSESSID+'; path=/','content-type': 'application/x-www-form-urlencoded'},
					body: qs.stringify(data.vars)
				},function(err,res,body){
					//  console.log('run ',body);
				});
			})

			break;
		case '/runModule':
			var fullBody = '';
			req.on('data', function(chunk) {
				fullBody += chunk.toString();
				if (fullBody.length > 1e6) {
					req.connection.destroy();
				}
			});
			req.on('end', function() {
				data = qs.parse(fullBody);
				//
				vars = data.vars || '';
				title = data.title || '';
				mdl = data.mdl || '';
				SESSID = data.SESSID || '';
				PHPSESSID = data.PHPSESSID || '';
				DOCUMENTDOMAIN = data.DOCUMENTDOMAIN || 'app.destinationsreve.com';
				//
				request.post({
					uri:'http://'+DOCUMENTDOMAIN+'/mdl/'+data.mdl+'.php',
					headers:{'Cookie':'PHPSESSID='+PHPSESSID+'; path=/','content-type': 'application/x-www-form-urlencoded'},
					body: qs.stringify(vars)
				},function(err,res,body){
					console.log('rumModule',mdl,body);
					io.sockets.emit('act_run',{body:body})

				});
				res.writeHead(200, {'Content-Type': 'text/html'})
				res.end();
			});


			break;
		case '/postReload': // => middleware ici
			//  DOCUMENTDOMAIN
			var fullBody = '';

			req.on('data', function(chunk) {
				fullBody += chunk.toString();
				// console.log(fullBody,fullBody.length,1e6)
				if (fullBody.length > 1e6) {
					console.log('destroy ',fullBody.length)
					req.connection.destroy();
				}
			});
			req.on('end', function() {


				var data = qs.parse(fullBody);

				res.writeHead(200, {'Content-Type': 'text/html'});
				res.end();
				//
				DOCUMENTDOMAIN = data.DOCUMENTDOMAIN || '';
				reloadVars={module:data.module,value:data.value || ''};
				//

				console.log( 'Type postReload ', typeof(data.vars));

				if(data.cmd && data.vars){
					if(data.OWN){
						io.sockets.to(data.OWN).emit('receive_cmd',data);
					}else{
						io.sockets.to(DOCUMENTDOMAIN).emit('receive_cmd',data);
					}
				}
				if(data.vars){
					if(typeof(data.vars)=='object') reloadVars.vars = qs.stringify(data.vars)
				}
				if(data.module && data.value){
					io.sockets.emit('reloadModule',reloadVars);
				}
			});
			break;
	}
}


module.exports = function(ioobj){
	console.log('middle !!! ')
	middleware_start = function (ioobj) {
		//
		console.log('middleware starts')
		var io = ioobj;
		var http = require('http').createServer(http_handler);
		return http;
	}

}
