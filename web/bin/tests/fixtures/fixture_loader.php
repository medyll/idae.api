<?php
// Simple fixture loader for integration tests.
// Usage: php tests/fixtures/fixture_loader.php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

$host = getenv('MDB_HOST') ?: 'mongo';
$port = getenv('MDB_PORT') ?: '27017';
$user = getenv('MDB_USER') ?: '';
$pass = getenv('MDB_PASS') ?: '';
$dbName = getenv('MDB_TEST_DB') ?: 'idae_test';
$prefix = getenv('MDB_PREFIX') ?: '';

$uri = $user !== '' ? sprintf('mongodb://%s:%s@%s:%s', $user, $pass, $host, $port) : sprintf('mongodb://%s:%s', $host, $port);

echo "Connecting to MongoDB at $host:$port\n";

try {
    $client = new Client($uri);

    // Prepare documents
    $docs = [
        ['idproducts' => 1, 'sku' => 'TEST-001', 'name' => 'Fixture Product A', 'price' => 9.99, 'status' => 'active', 'nameproducts' => 'Prod A'],
        ['idproducts' => 2, 'sku' => 'TEST-002', 'name' => 'Fixture Product B', 'price' => 19.99, 'status' => 'active', 'nameproducts' => 'Prod B'],
        ['idproducts' => 3, 'sku' => 'TEST-003', 'name' => 'Fixture Product C', 'price' => 29.99, 'status' => 'inactive', 'nameproducts' => 'Prod C'],
    ];

    $dbs = [$dbName];
    if (!empty($prefix)) {
        $dbs[] = $prefix . $dbName;
    }

    $total = 0;
    foreach ($dbs as $db) {
        $collection = $client->{$db}->products;
        foreach ($docs as $doc) {
            $collection->updateOne(['sku' => $doc['sku']], ['$set' => $doc], ['upsert' => true]);
        }
        $count = $collection->countDocuments(['sku' => ['$in' => array_column($docs, 'sku')]]);
        echo "Inserted/updated $count fixture documents into $db.products\n";
        $total += $count;
    }

    echo "Total fixtures processed: $total\n";
    exit(0);

} catch (\Exception $e) {
    fwrite(STDERR, "Fixture loader failed: " . $e->getMessage() . "\n");
    exit(2);
}
