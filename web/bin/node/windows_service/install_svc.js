var Service = require('node-windows').Service;

// Create a new service object
var svc = new Service({
  name:'tac-tac-main-service',
  description: 'tac-tac-main-service',
  script: 'D:\\boulot\\UwAmp\\www\\tac-tac.lan\\web\\bin\\node\\app_https\\app_https_main.js'
});

// Listen for the 'install' event, which indicates the
// process is available as a service.
svc.on('install',function(){
  svc.start();
});

// install the service
svc.install();