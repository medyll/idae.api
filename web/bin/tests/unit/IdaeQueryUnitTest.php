<?php
use PHPUnit\Framework\TestCase;
use Idae\Query\IdaeQuery;

/**
 * Covers IdaeQuery against a fake connection, so no database is needed.
 *
 * The anonymous classes inside each test stand in for the collection and the
 * scheme model IdaeQuery reads at construction.
 */
final class IdaeQueryUnitTest extends TestCase
{
    /**
     * find() returns the rows the injected collection yields.
     */
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

    /**
     * An unregistered scheme throws rather than falling through to a query: the
     * fake connection's plug() throws if it is reached at all.
     */
    public function testUnknownSchemeFailsExplicitly()
    {
        $appschemeModel = new class {
            public function findOne($query) {
                return null;
            }
        };

        $fakeConnect = new class($appschemeModel) {
            public $appscheme_model_instance;
            public function __construct($appschemeModel) {
                $this->appscheme_model_instance = $appschemeModel;
            }
            public function plug($base, $table) {
                throw new \LogicException('plug() must not be reached');
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown or unconfigured scheme: missing');

        new IdaeQuery('missing', $fakeConnect);
    }
}
