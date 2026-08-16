<?php
use PHPUnit\Framework\TestCase;

final class ProductsApiTest extends TestCase
{
    public function test_idql_write_method_is_rejected_without_touching_database()
    {
        require_once __DIR__ . '/../../../conf.inc.php';
        if (empty($_SERVER['REQUEST_METHOD'])) $_SERVER['REQUEST_METHOD'] = 'POST';
        if (empty($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI'] = '/api/idql/products';
        $api = new Idae\Api\IdaeApiRest([]);

        $idql = [
            'method' => 'delete',
            'scheme' => 'products',
            'where' => [],
        ];

        ob_start();
        $api->doIdql($idql);
        $out = ob_get_clean();

        $decoded = json_decode($out, true);
        $this->assertIsArray($decoded);
        $this->assertFalse($decoded['status']);
        $this->assertStringContainsString('Invalid parameter: method', $decoded['message']);
        $this->assertSame(422, http_response_code());
    }
}
