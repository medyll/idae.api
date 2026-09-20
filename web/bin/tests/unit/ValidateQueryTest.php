<?php
use PHPUnit\Framework\TestCase;

/**
 * Covers IDQL validation: every malformed query answers 422 with a message,
 * rather than throwing or reaching the database.
 */
final class ValidateQueryTest extends TestCase
{
    /**
     * Loads the app configuration and fills in the server variables PHPUnit does
     * not set when running from the CLI.
     */
    public function setUp(): void
    {
        require_once __DIR__ . '/../../../conf.inc.php';
        // Ensure HTTP server vars are present when running under phpunit CLI
        if (empty($_SERVER['REQUEST_METHOD'])) $_SERVER['REQUEST_METHOD'] = 'POST';
        if (empty($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI'] = '/api/idql/products';
    }

    /**
     * A query with no scheme is refused.
     */
    public function test_missing_scheme_returns_422()
    {
        $api = new Idae\Api\IdaeApiRest([]);

        ob_start();
        // no 'scheme' key — should trigger validation error
        $api->doIdql(['limit' => 1]);
        $out = ob_get_clean();

        $this->assertIsString($out);
        $decoded = json_decode($out, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('status', $decoded);
        $this->assertFalse($decoded['status']);
        $this->assertStringContainsString('Missing scheme', $decoded['message'] ?? '');
        $this->assertEquals(422, http_response_code());
    }

    /**
     * A non-numeric limit is refused.
     */
    public function test_invalid_limit_returns_422()
    {
        $api = new Idae\Api\IdaeApiRest([]);

        ob_start();
        // invalid limit value (non-numeric)
        $api->doIdql(['scheme' => 'products', 'limit' => 'not-a-number']);
        $out = ob_get_clean();

        $this->assertIsString($out);
        $decoded = json_decode($out, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('status', $decoded);
        $this->assertFalse($decoded['status']);
        $this->assertStringContainsString('Invalid parameter: limit must be numeric', $decoded['message'] ?? '');
        $this->assertEquals(422, http_response_code());
    }

    /**
     * A `where` that is not an object is refused.
     */
    public function test_invalid_where_returns_422()
    {
        $api = new Idae\Api\IdaeApiRest([]);

        ob_start();
        // where must be an array/object
        $api->doIdql(['scheme' => 'products', 'where' => 'not-an-array']);
        $out = ob_get_clean();

        $this->assertIsString($out);
        $decoded = json_decode($out, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('status', $decoded);
        $this->assertFalse($decoded['status']);
        $this->assertStringContainsString('Invalid parameter: where must be an object', $decoded['message'] ?? '');
        $this->assertEquals(422, http_response_code());
    }

    /**
     * An unknown query method is refused.
     */
    public function test_invalid_method_returns_422()
    {
        $api = new Idae\Api\IdaeApiRest([]);

        ob_start();
        // invalid method should be rejected
        $api->doIdql(['scheme' => 'products', 'method' => 'not_a_real_method']);
        $out = ob_get_clean();

        $this->assertIsString($out);
        $decoded = json_decode($out, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('status', $decoded);
        $this->assertFalse($decoded['status']);
        $this->assertStringContainsString('Invalid parameter: method', $decoded['message'] ?? '');
        $this->assertEquals(422, http_response_code());
    }

    /**
     * An operator outside the allowed set is refused with a 422, not an internal
     * error from the transpiler.
     */
    public function test_unsupported_operator_returns_422_instead_of_internal_error()
    {
        $api = new Idae\Api\IdaeApiRest([]);

        ob_start();
        $api->doIdql([
            'scheme' => 'products',
            'method' => 'find',
            'where' => ['where' => ['this.active' => true]],
        ]);
        $out = ob_get_clean();

        $decoded = json_decode($out, true);
        $this->assertIsArray($decoded);
        $this->assertFalse($decoded['status']);
        $this->assertStringContainsString('Unsupported operator: where', $decoded['message'] ?? '');
        $this->assertSame(422, http_response_code());
    }
}
