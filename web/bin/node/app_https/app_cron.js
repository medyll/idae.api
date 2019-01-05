var CronJob = require ('cron').CronJob;
var request = require ('request');

var time_zone = 'Europe/Paris';
var tab_cron  = [];

tab_cron ['minute'] = new CronJob ('00 * * * * *', function () {
	launch_job ('minute');
}, null, true, time_zone);

tab_cron ['minutes_5'] = new CronJob ('00 */5 * * * *', function () {
	launch_job ('minutes_5')
}, null, true, time_zone);

tab_cron ['minutes_10'] = new CronJob ('00 */10 * * * *', function () {
	launch_job ('minutes_10')

}, null, true, time_zone);
tab_cron ['minutes_15'] = new CronJob ('00 */15 * * * *', function () {
	launch_job ('minutes_15')

}, null, true, time_zone);
tab_cron ['midday'] = new CronJob ('00 00 12 * * *', function () {
	launch_job ('midday');

}, null, true, time_zone);

tab_cron ['midnight'] = new CronJob ('00 00 00 * * *', function () {

	launch_job ('midnight');

}, null, true, time_zone);

tab_cron ['hourly_double'] = new CronJob ('00 00 */2 * * *', function () {
	launch_job ('hourly_double');
}, null, true, 'America/Los_Angeles');

tab_cron ['hourly'] = new CronJob ('00 00 * * * *', function () {
	launch_job ('hourly');
}, null, true, time_zone);

tab_cron ['hourly_mid'] = new CronJob ('00 30 * * * *', function () {
	launch_job ('hourly_mid');

}, null, true, time_zone);

var MAINHOST = ('/var/www/tac-tac.shop.mydde.fr/web/bin/node/app_https' == __dirname) ? 'tac-tac.shop.mydde.fr' : 'tac-tac-city.fr';
MAINHOST = ('D:\\boulot\\wamp64\\www\\idae.api.lan\\web\\bin\\node\\app_https' == __dirname) ? 'tac-tac.lan' : MAINHOST;

process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0";

function launch_job(type) {
	var type = type;
	const url = 'https://'+MAINHOST+'/bin/cron/cron_dispatch.php',
	      uri = '?type_cron=' + type;
	request.get ({
		url     : url + uri  ,
		headers : { 'X-CRON' : 'tactac' }
	}, function (err, res, body) {
		// console.log (url + uri,type);
		// console.log ( body);
	});

}

function check_job(){

	for (var i in tab_cron) {
		test = tab_cron[i].running;
		if(test !=true){
			console.log(i,test);
			tab_cron[i].start();
		}
	}
}
setInterval(check_job,60000);
