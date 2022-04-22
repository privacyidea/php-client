<?php

require_once('../../src/Client-Autoloader.php');
require_once('../../vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;

class TestPollTransaction extends TestCase
{
    private $pi;

    use HttpMockTrait;

    public static function setUpBeforeClass(): void
    {
        static::setUpHttpMockBeforeClass('8082', 'localhost');
    }

    public static function tearDownAfterClass(): void
    {
        static::tearDownHttpMockAfterClass();
    }

    public function setUp(): void
    {
        $this->setUpHttpMock();
        $this->pi = new PrivacyIDEA('testUserAgent', "http://127.0.0.1:8082");
    }

    public function tearDown(): void
    {
        $this->tearDownHttpMock();
    }

    public function testPollTransaction()
    {
        $respPolling = '{
                "id": 1,
          "jsonrpc": "2.0",
          "result": {
                    "status": true,
            "value": true
          },
          "version": "privacyIDEA 3.5.2",
          "versionnumber": "3.5.2",
          "signature": "rsa_sha256_pss:12345"
        }';

        $this->http->mock
            ->when()
            ->methodIs('GET')
            ->pathIs('/validate/polltransaction')
            ->then()
            ->body($respPolling)
            ->end();
        $this->http->setUp();

        $response = $this->pi->pollTransaction("");
        $this->assertNotNull($response, "Response is not NULL without transaction_id given.");

        $response = $this->pi->pollTransaction("1234567890");
        $this->assertNotNull($response, "Response is NULL.");

        $this->assertTrue($response, "Value is not true as expected.");
    }
}
