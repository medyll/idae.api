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

        $uri = 'mongodb://' . MDB_USER . ':' . MDB_PASSWORD . '@' . MDB_HOST;
        try {
            $client = new Client($uri);
        } catch (Exception $e) {
            $this->markTestSkipped('Cannot connect to MongoDB: ' . $e->getMessage());
        }

        $dbName = (defined('MDB_PREFIX') ? MDB_PREFIX : '') . 'idae_test';
        $collection = $client->selectDatabase($dbName)->selectCollection('products');

        // Reset collection and insert fixtures
        $collection->deleteMany([]);
        $docs = [
            ['idproducts' => 1, 'nameproducts' => 'Prod A', 'status' => 'active'],
            ['idproducts' => 2, 'nameproducts' => 'Prod B', 'status' => 'inactive']
        ];
        $collection->insertMany($docs);

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
