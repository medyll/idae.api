/**
 * Created by lebru_000 on 25/12/14.
 */

process.env.NODE_TLS_REJECT_UNAUTHORIZED = 0;
NODE_TLS_REJECT_UNAUTHORIZED             = 0;

var fs = require('fs');
var mongo_url;

if ('/var/www/tac-tac.shop.mydde.fr/web/bin/node/app_https' == __dirname) {

	var ssl_opt = {
		key: fs.readFileSync('/etc/letsencrypt/live/tac-tac.shop.mydde.fr/privkey.pem'),
		cert: fs.readFileSync('/etc/letsencrypt/live/tac-tac.shop.mydde.fr/fullchain.pem'),
		ca: fs.readFileSync('/etc/letsencrypt/live/tac-tac.shop.mydde.fr/chain.pem'),
		ciphers: 'ECDHE-RSA-AES256-SHA:AES256-SHA:RC4-SHA:RC4:HIGH:!MD5:!aNULL:!EDH:!AESGCM',
		honorCipherOrder: true,
		requestCert: true,
		rejectUnauthorized: false
	}
} else if ('/var/www/www.tac-tac-city.fr/web/bin/node/app_https' == __dirname) {
	var ssl_opt = {
		key: fs.readFileSync('/etc/letsencrypt/live/www.tac-tac-city.fr/privkey.pem'),
		cert: fs.readFileSync('/etc/letsencrypt/live/www.tac-tac-city.fr/fullchain.pem'),
		ca: fs.readFileSync('/etc/letsencrypt/live/www.tac-tac-city.fr/chain.pem'),
		ciphers: 'ECDHE-RSA-AES256-SHA:AES256-SHA:RC4-SHA:RC4:HIGH:!MD5:!aNULL:!EDH:!AESGCM',
		honorCipherOrder: true,
		requestCert: true,
		rejectUnauthorized: false
	}

}

var https        = require('https');
var http         = require('http');
var app          = http.createServer(http_handler);
var io           = require('socket.io')(app);
var url          = require('url');
var qs           = require('qs');
var request      = require('request');
var cookieParser = require('socket.io-cookie');
var mongoClient  = require('mongodb').MongoClient;

var socket_array = {};


build_header = function (data) {

	if (!data.DOCUMENTDOMAIN) {
		return '';
	}
	data.vars          = data.vars || '';
	var SESSID         = data.SESSID || '',
	    PHPSESSID      = data.PHPSESSID || '',
	    DOCUMENTDOMAIN = data.DOCUMENTDOMAIN || 'appgem.destinationsreve.com';

	var headers = {
		'Cookie': 'PHPSESSID=' + PHPSESSID + '; path=/',
		'content-type': 'application/x-www-form-urlencoded'
	};

	return headers;
};
build_vars   = function (data) {
	if (!data.DOCUMENTDOMAIN) {
		return '';
	}
	data.vars = data.vars || '';

	return data.vars;
};

function http_handler(req, res) {

	var arr_delay = [];
	if (!req.url) {
		return;
	}

	if (req.url === '/favicon.ico') {
		res.writeHead(200, {'Content-Type': 'image/x-icon'});
		res.end();
		return;
	}
	var path = url.parse(req.url).pathname;

	//
	var fullBody = '';

	req.on('data', function (chunk) {
		fullBody += chunk.toString();
		if (fullBody.length > 1e6) {
			req.connection.destroy();
		}
	});

	//
	switch (path) {
		case '/postScope':

			req.on('end', function () {
				data = qs.parse(fullBody);
				//
				reloadVars = {scope: data.scope, value: data.value};
				if (data.vars) {
					reloadVars.vars = qs.stringify(data.vars);
				}
				if (data.scope && data.value) {
					io.sockets.emit('reloadScope', reloadVars);
				}
				res.writeHead(200, {'Content-Type': 'text/html'});
				res.end();
			});
			break;
		case "/run":

			req.on('end', function () {
				var reqbody = qs.parse(fullBody);

				var vars       = reqbody.vars || '';
				var options    = vars.vars || {},
				    route      = vars.route || '',
				    delay      = vars.delay || 0,
				    delay_name = vars.delay_name || false,
				    method     = vars.method || 'POST';

				var SESSID         = reqbody.SESSID || '',
				    PHPSESSID      = reqbody.PHPSESSID || 'none',
				    DOCUMENTDOMAIN = reqbody.DOCUMENTDOMAIN;
				//
				var url = 'https://' + DOCUMENTDOMAIN + '/' + route;

				var post_params = {
					url: url,
					method: method,
					headers: build_header(reqbody),
					body: qs.stringify(options)
				};

				if (vars.delay_name && arr_delay[vars.delay_name]) {
					arr_delay[vars.delay_name] = true;
					clearTimeout(arr_delay[vars.delay_name]);
					arr_delay[vars.delay_name] = setTimeout(function () {
						request.post(post_params, function (err, res, body) {
							if (vars.delay_name && arr_delay[vars.delay_name]) {
								clearTimeout(arr_delay[vars.delay_name]);
							}
						});
					}, delay);

				} else {
					setTimeout(function () {
						request.post(post_params, function (err, res, body) {
							if (vars.delay_name && arr_delay[vars.delay_name]) {
								delete arr_delay[vars.delay_name];
							}
						});

					}, delay);
				}
				res.writeHead(200, {'Content-Type': 'text/html'});
				res.end();
			});

			break;
		case '/runModule':

			req.on('end', function () {
				data = qs.parse(fullBody);
				//
				vars           = data.vars || '';
				title          = data.title || '';
				mdl            = data.mdl || '';
				SESSID         = data.SESSID || '';
				PHPSESSID      = data.PHPSESSID || '';
				DOCUMENTDOMAIN = data.DOCUMENTDOMAIN || 'app.destinationsreve.com';
				//
				request.post({
					uri: 'http://' + DOCUMENTDOMAIN + '/mdl/' + data.mdl + '.php',
					headers: {
						'Cookie': 'PHPSESSID=' + PHPSESSID + '; path=/',
						'content-type': 'application/x-www-form-urlencoded'
					},
					body: qs.stringify(vars)
				}, function (err, res, body) {
					// console.log('rumModule', mdl, body);
					io.sockets.in(DOCUMENTDOMAIN).emit('act_run', {body: body})

				});
				res.writeHead(200, {'Content-Type': 'text/html'});
				res.end();
			});

			break;
		case '/postGrantIn':
			req.on('end', function () {
				var data = qs.parse(fullBody);
				res.writeHead(200, {'Content-Type': 'text/html'});
				res.end();

				if (data.vars && data.vars.room) {
					var ids = Object.keys(io.sockets.connected);
					// console.log (io.sockets.connected);
					ids.forEach(function (id) {
						var socket_da = io.sockets.connected[id];
						if (socket_da['PHPSESSID'] == data.PHPSESSID) {
							socket_da.join(data.vars.room);
							if (data.DOCUMENTDOMAIN) {
								socket_da.join(data.DOCUMENTDOMAIN);
							}
						}

					});

				}

			});
			break;
		case '/postReload': // => middleware, most happen  here

			req.on('end', function () {
				var data = qs.parse(fullBody);
				//console.log('postReload',data)
				res.writeHead(200, {'Content-Type': 'text/html'});
				res.end();

				//
				var DOCUMENTDOMAIN = data.DOCUMENTDOMAIN || '',
				    reloadVars     = {module: data.module, value: data.value || ''};
				//

				if (data.cmd && data.vars) {
					if (data.OWN) {
						//  console.log('postReload', DOCUMENTDOMAIN, data.OWN, typeof(data.OWN))
						switch (typeof(data.OWN)) {
							case 'string':
								io.sockets.to(data.OWN).emit('receive_cmd', data);
								break;
							case 'object':
								data.OWN.forEach(function (item, index) {
									io.sockets.to(item).emit('receive_cmd', data);
								});
								break;
						}
					} else {
						//console.log ('reload to DOCUMENTDOMAIN ', DOCUMENTDOMAIN);

						io.sockets.to(DOCUMENTDOMAIN).emit('receive_cmd', data);
					}
				}
				if (data.vars) {
					if (typeof(data.vars) == 'object') {
						reloadVars.vars = qs.stringify(data.vars)
					}
				}
				if (data.module && data.value) {
					io.sockets.emit('reloadModule', reloadVars);
				}
			});
			break;
	}
}

function init_app() {
	io.use(cookieParser);
	io.use(authorization);
	socket_onconnection();
}
//
function authorization(socket, next) {
	// console.log('auth', socket.handshake.headers);
	if (socket.request.headers.cookie) {
		var cookie = socket.request.headers.cookie;
		//  PHPSESSID TEST
		if (!cookie.PHPSESSID) {
			next(new Error('not authorized')); //;
			return false;
		}

		socket.PHPSESSID = cookie.PHPSESSID;
		socket.join(socket.PHPSESSID);
		next();
		authorization_ok(socket);
	} else {
		{
			if (socket.handshake) {
				// console.log('Echec .request', socket.request.headers);
			}
			next(new Error('not authorized'));
			return false;
		}
	}
}


declare_db = function (host) {

	if (host == null) {
		return false;
	}
	switch (host) {
		case 'http://idaertys.mydde.fr':
			prefix = 'idaenext';
			break;
		case 'http://idaertys-preprod.mydde.fr':
			prefix = 'idaenext';
			break;
		case 'http://tactac_idae.preprod.mydde.fr':
			prefix = 'tactac';
			break;
		case 'https://tac-tac.shop.mydde.fr':
			prefix = 'tactac';
			break;
		case 'https://tac-tac.city.fr':
			prefix = 'tactac';
			break;
		case 'https://tac-tac.lan':
			prefix = 'tactac';
			break;
	}
};

authorization_ok = function (socket) {
	declare_db(socket.request.headers.origin);
	socket.emit('ask_grantIn');
};


function socket_onconnection() {

	console.log('socket is listening')
	io.on('connection', function (socket) {

		if (socket.PHPSESSID) {
			socket.join(socket.PHPSESSID);
		}

		// HEARTBEAT
		var sender = setInterval(function () {
			socket.emit('heartbeat_app', [new Date().getTime(), socket.PHPSESSID]);
		}, 15000);

		socket.on('disconnect', function (data) {

		});
		//
		socket.on('grantIn', function (data, fn) {
			var sess            = {};
			sess.sessionId      = socket.id;
			sess.DOCUMENTDOMAIN = data.DOCUMENTDOMAIN;
			sess.SESSID         = data.SESSID;
			sess.PHPSESSID      = data.PHPSESSID;
			// sess.SSSAVEPATH = data.SSSAVEPATH;
			//
			if (data.DOCUMENTDOMAIN) {
				socket.join(data.DOCUMENTDOMAIN);
			}
			if (data.ROOM) {
				socket.join(data.ROOM);
			}
			if (socket_array) {
				// socket_array[socket.id] = sess;
			}
			//
			io.sockets.to(data.DOCUMENTDOMAIN).send('user connected');
			// socket.broadcast.send('user connected');
			if (data.SESSID) {
				if (fn) {
					fn(true);
				}
			}
		});
		//
		socket.on('message', function (message) {
			// console.log(message) //	socket.send('cool');
		});
		socket.on('reloadModule', function (data) {
			socket.broadcast.emit('reloadModule', data);
		});
		socket.on('reloadScope', function (data) {
			// console.log(data);
			if (!data) {
				return;
			}
			if (!data.scope) {
				return;
			}
			reloadVars = {scope: data.scope, value: data.value};
			if (data.vars) {
				reloadVars.vars = qs.stringify(data.vars);
			}
			if (data.scope && data.value) {
				io.sockets.emit('reloadScope', reloadVars);
			}
		});
		// loadModule
		socket.on('loadModule', function (data, func) {
			var fn             = func || null,
			    vars           = data.vars || '',
			    title          = data.title || '',
			    mdl            = data.mdl || '',
			    DOCUMENTDOMAIN = data.DOCUMENTDOMAIN || 'app.destinationsreve.com';

			if (DOCUMENTDOMAIN) {
				socket.join(DOCUMENTDOMAIN)
			} else {
				return false;
			}

			request.post({
				uri: 'http://' + DOCUMENTDOMAIN + '/mdl/' + data.mdl + '.php',
				headers: build_header(data),
				body: qs.stringify(vars)
			}, function (err, res, body) {
				socket.to(DOCUMENTDOMAIN).emit('loadModule', {body: body, vars: vars, mdl: mdl, title: title})
			});
		});

		socket.on('socketModule', function (data, fun) {
			var fn = fun || null;

			data.vars       = data.vars || '';
			data.options    = data.options || {};
			data.vars.defer = '';

			DOCUMENTDOMAIN = data.DOCUMENTDOMAIN || 'appgem.destinationsreve.com';
			//
			if (DOCUMENTDOMAIN) {
				socket.join(DOCUMENTDOMAIN);
			}
			//
			var url = 'http://' + DOCUMENTDOMAIN + '/mdl/' + data.file + '.php';

			request.post({
				url: url,
				method: 'POST',
				headers: build_header(data),
				body: data.vars
			}, function (err, res, body) {
				socket.to(DOCUMENTDOMAIN).emit('socketModule', {body: body, out: data});
				if (fn) {
					fn({body: body, data: data});
				}
			});
		});
		socket.on('upd_data', function (data) {
			vars  = data.vars || '';
			title = data.title || '';
			mdl   = data.mdl || '';

			DOCUMENTDOMAIN = data.DOCUMENTDOMAIN || 'app.destinationsreve.com';
			//
			request.post({
				uri: 'http://' + DOCUMENTDOMAIN + '/services/json_data_table_row.php',
				headers: build_header(data),
				body: qs.stringify(vars)
			}, function (err, res, body) {
				io.sockets.in(DOCUMENTDOMAIN).emit('upd_data', {body: body, vars: vars, mdl: mdl, title: title});
				// socket.emit('upd_data',{body:body,vars:vars,mdl:mdl,title:title})
			});
		});

		socket.on('get_data', function (data, options, fn) {
			var vars           = data.vars || '',
			    options        = options || {},
			    DOCUMENTDOMAIN = data.DOCUMENTDOMAIN || 'app.destinationsreve.com';

			var directory = (data.directory) ? data.directory : 'bin/services';
			var extension = (data.extension) ? data.extension : 'php';
			//
			var url = 'http://' + DOCUMENTDOMAIN + '/' + directory + '/' + data.mdl + '.' + extension;


			if (socket.PHPSESSID !== undefined) {
				request.get({
					url: url,
					method: 'GET',
					headers: build_header(data),
					qs: vars
				}, function (err, res, body) {
					fn(body, options)
				});
			}
		});

		var runningRunModule = {};

		socket.on('runModule', function (data) {

			data.vars          = data.vars || '';
			data.options       = data.options || {};
			data.vars.defer    = '';
			var SESSID         = data.SESSID || '',
			    DOCUMENTDOMAIN = data.DOCUMENTDOMAIN || 'appgem.destinationsreve.com';

			var url = 'http://' + DOCUMENTDOMAIN + '/' + data.file + '.php';

			key = SESSID + data.file + data.vars;

			request.post({
				url: url,
				method: 'POST',
				headers: build_header(data),
				body: data.vars
			}, function (err, res, body) {

			});

		});
	})

}

module.exports = {

	init_app: function (port) {

	},
	socket_start: function (port) {
		//
		console.log('socket started on port ' + port);

		app.listen(port);
		init_app();

	},
	io: io,
	bar: function () {
		// whatever
	}

};