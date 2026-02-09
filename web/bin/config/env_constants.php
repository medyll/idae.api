<?php
// Environment-driven overrides for constants used in tests and containers.
// This file is included by bin/config/constants.php when present.

if (!defined('MDB_HOST')) {
    $envHost = getenv('MDB_HOST');
    define('MDB_HOST', $envHost ?: 'host.docker.internal');
}

if (!defined('MDB_USER')) {
    $envUser = getenv('MDB_USER');
    define('MDB_USER', $envUser ?: 'admin');
}

if (!defined('MDB_PASSWORD')) {
    $envPass = getenv('MDB_PASSWORD') ?: getenv('MDB_PASS');
    define('MDB_PASSWORD', $envPass ?: 'gwetme2011');
}

// Also map other common env overrides if present
if (!defined('MDB_PREFIX') && getenv('MDB_PREFIX')) define('MDB_PREFIX', getenv('MDB_PREFIX'));
if (!defined('SOCKETIO_HOST') && getenv('SOCKETIO_HOST')) define('SOCKETIO_HOST', getenv('SOCKETIO_HOST'));
if (!defined('SOCKETIO_PORT') && getenv('SOCKETIO_PORT')) define('SOCKETIO_PORT', getenv('SOCKETIO_PORT'));
if (!defined('ENVIRONEMENT') && getenv('ENVIRONEMENT')) define('ENVIRONEMENT', getenv('ENVIRONEMENT'));
