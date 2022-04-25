<?php

require_once('../../src/Client-Autoloader.php');
require_once('../../vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;

class TestPollTransaction extends TestCase implements PILog
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
        $this->pi = new PrivacyIDEA('testUserAgent', "localhost:8082");
        $this->pi->realm = "testRealm";
        $this->pi->logger = $this;
    }

    public function tearDown(): void
    {
        $this->tearDownHttpMock();
    }

    /**
     * @throws PIBadRequestException
     */
    public function testSuccess()
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

        $response = $this->pi->pollTransaction("1234567890");

        $this->assertNotNull($response);
        $this->assertTrue($response);
    }

    /**
     * @throws PIBadRequestException
     */
    public function testNoTransactionID()
    {
        $response = $this->pi->pollTransaction("");
        $this->assertFalse($response);
    }

    public function piDebug($message)
    {
        echo $message . "\n";
    }

    public function piError($message)
    {
        echo "error: " . $message . "\n";
    }
}
