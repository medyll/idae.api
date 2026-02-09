<?php
// Composer autoload
$vendor = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendor)) {
    require $vendor;
}

// Ensure the application config is available for tests (defines constants)
$conf = realpath(__DIR__ . '/../../conf.inc.php');
if ($conf && file_exists($conf)) {
    // expose path so legacy code can find it
    
    if (!defined('CONF_INC')) define('CONF_INC', $conf);
    $_SERVER['CONF_INC'] = $conf;
    include_once $conf;
}
