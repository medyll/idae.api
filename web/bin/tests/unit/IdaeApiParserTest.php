<?php
use PHPUnit\Framework\TestCase;
use Idae\Api\IdaeApiParser;

class IdaeApiParserTest extends TestCase
{
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
