<?php
require __DIR__ . '/../../vendor/autoload.php';
use MongoDB\Client;

$uri = 'mongodb://admin:gwetme2011@host.docker.internal:27017/admin';
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
