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

    public function test_rest_write_command_is_rejected_before_query_execution()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/products/update';

        $api = new Idae\Api\IdaeApiRest([]);

        ob_start();
        $api->doRest();
        $out = ob_get_clean();

        $decoded = json_decode($out, true);
        $this->assertIsArray($decoded);
        $this->assertFalse($decoded['status']);
        $this->assertStringContainsString('Write operations are not supported', $decoded['message']);
        $this->assertSame(422, http_response_code());
    }

    public function test_rest_delete_http_method_returns_405()
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['REQUEST_URI'] = '/api/products/find';

        $api = new Idae\Api\IdaeApiRest([]);
        ob_start();
        $api->doRest();
        $out = ob_get_clean();

        $decoded = json_decode($out, true);
        $this->assertIsArray($decoded);
        $this->assertFalse($decoded['status']);
        $this->assertStringContainsString('Method not allowed', $decoded['message']);
        $this->assertSame(405, http_response_code());
    }
}
