<?php
// Simple fixture loader for integration tests.
// Usage: php tests/fixtures/fixture_loader.php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

$host = getenv('MDB_HOST') ?: 'mongo';
$port = getenv('MDB_PORT') ?: '27017';
$user = getenv('MDB_USER') ?: '';
$pass = getenv('MDB_PASSWORD') ?: (getenv('MDB_PASS') ?: '');
$dbName = getenv('MDB_TEST_DB') ?: 'idae_test';

$uri = sprintf('mongodb://%s:%s', $host, $port);
$options = [];
if ($user !== '') {
    $options = [
        'username' => $user,
        'password' => $pass,
        'authSource' => getenv('MDB_AUTH_SOURCE') ?: 'admin',
    ];
}

echo "Connecting to MongoDB at $host:$port\n";

try {
    $client = new Client($uri, $options);

    // Prepare documents
    $docs = [
        ['idproducts' => 1, 'sku' => 'TEST-001', 'name' => 'Fixture Product A', 'price' => 9.99, 'status' => 'active', 'nameproducts' => 'Prod A'],
        ['idproducts' => 2, 'sku' => 'TEST-002', 'name' => 'Fixture Product B', 'price' => 19.99, 'status' => 'active', 'nameproducts' => 'Prod B'],
        ['idproducts' => 3, 'sku' => 'TEST-003', 'name' => 'Fixture Product C', 'price' => 29.99, 'status' => 'inactive', 'nameproducts' => 'Prod C'],
    ];

    $collection = $client->{$dbName}->products;
    foreach ($docs as $doc) {
        $collection->updateOne(['sku' => $doc['sku']], ['$set' => $doc], ['upsert' => true]);
    }
    $count = $collection->countDocuments(['sku' => ['$in' => array_column($docs, 'sku')]]);

    echo "Inserted/updated $count fixture documents into $dbName.products\n";
    exit(0);

} catch (\Exception $e) {
    fwrite(STDERR, "Fixture loader failed: " . $e->getMessage() . "\n");
    exit(2);
}
