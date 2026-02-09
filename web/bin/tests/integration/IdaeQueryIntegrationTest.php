<?php
use PHPUnit\Framework\TestCase;
use MongoDB\Client;
use Idae\Query\IdaeQuery;

class IdaeQueryIntegrationTest extends TestCase
{
    public function testFindOneWithInjectedConnect()
    {
        if (!defined('MDB_HOST') || !class_exists('MongoDB\\Client')) {
            $this->markTestSkipped('MongoDB client or DB constants not available');
        }

        // Try no-auth connection first (local dev override uses no-auth mongo)
        $testUser = 'idae_test_user';
        $testPwd = 'idae_test_pwd';
        $testDb = (defined('MDB_PREFIX') ? MDB_PREFIX : '') . 'idae_test';

        $client = null;

        // Prefer configured MDB_USER (if present) so authenticated reads succeed.
        if (defined('MDB_USER') && defined('MDB_PASSWORD')) {
            try {
                $uri = 'mongodb://' . MDB_USER . ':' . MDB_PASSWORD . '@' . MDB_HOST . '/admin';
                $client = new Client($uri);
            } catch (\Exception $e) {
                $client = null;
            }
        }

        // Next try test user created by init script
        if (is_null($client)) {
            try {
                $uri = 'mongodb://' . $testUser . ':' . $testPwd . '@' . MDB_HOST . '/' . $testDb . '?authSource=' . $testDb;
                $client = new Client($uri);
            } catch (\Exception $e) {
                $client = null;
            }
        }

        // Finally try no-auth connection (useful for local no-auth Mongo)
        if (is_null($client)) {
            try {
                $uri = 'mongodb://' . MDB_HOST;
                $client = new Client($uri);
            } catch (\Exception $e) {
                $client = null;
            }
        }

        if (is_null($client)) {
            $this->markTestSkipped('Cannot establish a MongoDB client connection for tests');
        }

        $dbName = $testDb;
        $collection = $client->selectDatabase($dbName)->selectCollection('products');

        // Attempt to read an existing fixture inserted by the init script (no writes)
        try {
            $res = $collection->findOne(['idproducts' => 1]);
        } catch (\Exception $e) {
            $this->markTestSkipped('MongoDB read failed: ' . $e->getMessage());
        }

        if (empty($res)) {
            $this->markTestSkipped('No fixture documents found in products collection');
        }

        // Minimal appscheme_model_instance stub
        $appscheme_model_instance = new class {
            public function findOne($q) {
                return ['codeAppscheme_base' => (defined('MDB_PREFIX')?MDB_PREFIX:'') . 'idae_test', 'codeAppscheme' => 'products'];
            }
        };

        // Test connect wrapper that exposes plug() and appscheme_model_instance
        $testConnect = new class($client, $appscheme_model_instance) {
            public $appscheme_model_instance;
            private $client;
            public function __construct($client, $appscheme_model_instance) {
                $this->client = $client;
                $this->appscheme_model_instance = $appscheme_model_instance;
            }
            public function plug($base, $table) {
                return $this->client->selectDatabase($base)->selectCollection($table);
            }
        };

        // Inject our test connect into IdaeQuery
        $query = new IdaeQuery('products', $testConnect);

        $res = $query->findOne(['idproducts' => 1]);
        $resArr = is_array($res) ? $res : (array)$res;

        $this->assertIsArray($resArr);
        $this->assertEquals('Prod A', $resArr['nameproducts']);
    }
}
