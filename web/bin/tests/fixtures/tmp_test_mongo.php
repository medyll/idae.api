<?php
require __DIR__ . '/../../vendor/autoload.php';
use MongoDB\Client;

// Credentials come from the environment; export MDB_USER/MDB_PASSWORD/MDB_HOST
// before running this fixture.
$mdb_user = getenv('MDB_USER') ?: 'admin';
$mdb_pass = getenv('MDB_PASSWORD');
$mdb_host = getenv('MDB_HOST') ?: 'host.docker.internal:27017';
if (!$mdb_pass) {
	fwrite(STDERR, "MDB_PASSWORD is not set\n");
	exit(1);
}
$uri = 'mongodb://' . $mdb_user . ':' . $mdb_pass . '@' . $mdb_host . '/admin';
echo "Connecting to $uri\n";
try {
    $c = new Client($uri);
    $col = $c->selectDatabase('maw_idae_test')->selectCollection('products');
    $doc = $col->findOne(['sku' => 'TEST-001']);
    if ($doc) {
        echo "Found: " . json_encode((array)$doc) . "\n";
    } else {
        echo "No document found\n";
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
