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

        // Prefer the test user created by the init script to avoid root/auth issues
        $testUser = 'idae_test_user';
        $testPwd = 'idae_test_pwd';
        $testDb = (defined('MDB_PREFIX') ? MDB_PREFIX : '') . 'idae_test';

        $tried = [];
        $client = null;
        // Try test user first
        try {
            $uri = 'mongodb://' . $testUser . ':' . $testPwd . '@' . MDB_HOST . '/' . $testDb . '?authSource=' . $testDb;
            $client = new Client($uri);
            $tried[] = $testUser;
        } catch (\Exception $e) {
            // fallback to MDB_USER
        }

        if (is_null($client) && defined('MDB_USER')) {
            try {
                $uri = 'mongodb://' . MDB_USER . ':' . MDB_PASSWORD . '@' . MDB_HOST . '/admin';
                $client = new Client($uri);
                $tried[] = MDB_USER;
            } catch (\Exception $e) {
                $this->markTestSkipped('Cannot connect to MongoDB with test or configured user: ' . $e->getMessage());
            }
        }

        $dbName = $testDb;
        $collection = $client->selectDatabase($dbName)->selectCollection('products');

        // Reset collection and insert fixtures (skip if auth/SSL problems)
        try {
            $collection->deleteMany([]);
            $docs = [
                ['idproducts' => 1, 'nameproducts' => 'Prod A', 'status' => 'active'],
                ['idproducts' => 2, 'nameproducts' => 'Prod B', 'status' => 'inactive']
            ];
            $collection->insertMany($docs);
        } catch (\MongoDB\Driver\Exception\AuthenticationException $e) {
            $this->markTestSkipped('MongoDB authentication failed: ' . $e->getMessage());
        } catch (\MongoDB\Driver\Exception\BulkWriteException $e) {
            // Could wrap underlying auth error
            $this->markTestSkipped('MongoDB bulk write/auth error: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->markTestSkipped('MongoDB not available: ' . $e->getMessage());
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
