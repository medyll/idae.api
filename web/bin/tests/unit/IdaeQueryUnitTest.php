<?php
use PHPUnit\Framework\TestCase;
use Idae\Query\IdaeQuery;

final class IdaeQueryUnitTest extends TestCase
{
    public function testFindReturnsArrayWithFakeConnect()
    {
        // build a fake collection that supports find and findOne
        $fakeColl = new class {
            public function find($query = [], $options = []) {
                return new \ArrayIterator([
                    ['idproducts' => 1, 'nameproducts' => 'Sample A'],
                ]);
            }
            public function findOne($query = [], $opts = []) {
                return ['idproducts'=>1,'nameproducts'=>'Sample A'];
            }
        };

        // fake appscheme_model_instance used by constructor init
        $appscheme_model_instance = new class($fakeColl) {
            private $coll;
            public function __construct($coll) { $this->coll = $coll; }
            public function findOne($q) {
                // return minimal scheme info so IdaeQuery can set collection name
                return ['codeAppscheme' => 'products', 'codeAppscheme_base' => 'testdb'];
            }
        };

        // fake connect object
        $fakeConnect = new class($appscheme_model_instance, $fakeColl) {
            public $appscheme_model_instance;
            private $coll;
            public function __construct($asi, $coll) {
                $this->appscheme_model_instance = $asi;
                $this->coll = $coll;
            }
            public function plug($base, $table) {
                // always return our fake collection regardless of args
                return $this->coll;
            }
        };

        $query = new IdaeQuery('products', $fakeConnect);
        $result = $query->find(['idproducts' => 1]);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Sample A', $result[0]['nameproducts']);
    }
}
