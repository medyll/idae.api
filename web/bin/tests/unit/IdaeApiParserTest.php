<?php
use PHPUnit\Framework\TestCase;
use Idae\Api\IdaeApiParser;

/**
 * Covers the URI parser: that a request path becomes the query it should, and
 * that numeric filter values survive the trip.
 */
class IdaeApiParserTest extends TestCase
{
    /**
     * A plain `/table/id` path parses into the matching selector.
     */
    public function testParseSimpleUri()
    {
        $parser = new IdaeApiParser();
        $parser->setRequestUri('/products/find/limit:10/page:2');
        $parser->setQyCodeType('php');

        $result = $parser->parse();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('scheme', $result);
        $this->assertEquals('products', $result['scheme']);
        $this->assertArrayHasKey('where', $result);
        $this->assertArrayHasKey('limit', $result);
        $this->assertEquals('10', $result['limit']);
        $this->assertArrayHasKey('page', $result);
        $this->assertEquals('2', $result['page']);
    }

    /**
     * A decimal filter value stays a float rather than being truncated to an int.
     */
    public function testDecimalFilterValueKeepsItsPrecision()
    {
        $parser = new IdaeApiParser();
        $parser->setQyCodeType('php');

        $result = $parser->parse([
            'scheme' => 'products',
            'method' => 'find',
            'where' => ['eq' => ['price' => '9.99']],
        ]);

        $this->assertSame(9.99, $result['where']['price']);
    }
}
