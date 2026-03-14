<?php
use PHPUnit\Framework\TestCase;

final class RestApiTest extends TestCase
{
    public function setUp(): void
    {
        require_once __DIR__ . '/../../../conf.inc.php';
        // ensure basic server vars
        if (empty($_SERVER['REQUEST_METHOD'])) {
            $_SERVER['REQUEST_METHOD'] = 'GET';
        }
        if (empty($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = '/api/products';
        }
    }

    public function test_rest_get_products_returns_sample_when_db_unavailable()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        // place command tokens in URI since parser relies on path segments
        $_SERVER['REQUEST_URI'] = '/api/products/limit:1';

        $api = new Idae\Api\IdaeApiRest([]);

        ob_start();
        $api->doRest();
        $out = ob_get_clean();

        $this->assertIsString($out);
        $this->assertStringContainsString('"rs"', $out);
    }

    public function test_rest_post_products_returns_sample_when_db_unavailable()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        // include parsing tokens in URI again
        $_SERVER['REQUEST_URI'] = '/api/products/method:find/limit:1';

        $api = new Idae\Api\IdaeApiRest([]);
        ob_start();
        $api->doRest();
        $out = ob_get_clean();

        $this->assertIsString($out);
        $this->assertStringContainsString('"rs"', $out);
    }
}
