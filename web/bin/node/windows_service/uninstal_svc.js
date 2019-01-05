var Service = require('node-windows').Service;

// Create a new service object
var svc = new Service({
  name:'tac-tac-main-service',
  description: 'tac-tac-main-service',
  script: 'D:\boulot\UwAmp\www\tac-tac.lan\web\bin\node\app_https\app_https_main.js'
});

// Listen for the 'uninstall' event so we know when it is done.
svc.on('uninstall',function(){
  console.log('Uninstall complete.');
  console.log('The service exists: ',svc.exists);
});

// Uninstall the service.
svc.uninstall();