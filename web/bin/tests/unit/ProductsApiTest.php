<?php
use PHPUnit\Framework\TestCase;

final class ProductsApiTest extends TestCase
{
    public function test_idql_products_returns_sample_when_db_unavailable()
    {
        // Minimal smoke test: call IdaeApiRest->doIdql with products and expect an array in response
        require_once __DIR__ . '/../../../conf.inc.php';
        // Ensure HTTP server vars are present when running under phpunit CLI
        if (empty($_SERVER['REQUEST_METHOD'])) $_SERVER['REQUEST_METHOD'] = 'POST';
        if (empty($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI'] = '/api/idql/products';
        $api = new Idae\Api\IdaeApiRest([]);
        // Build idql payload that requests products
        $idql = [
            'method' => 'find',
            'scheme' => 'products',
            'limit' => 2
        ];

        // Capture output
        ob_start();
        $api->doIdql($idql);
        $out = ob_get_clean();

        $this->assertIsString($out);
        $this->assertStringContainsString('"rs"', $out);
    }
}
