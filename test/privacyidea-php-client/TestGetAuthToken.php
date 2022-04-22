<?php

require_once('../../src/Client-Autoloader.php');
require_once('../../vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;

class TestGetAuthToken extends TestCase
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

    /**
     * @throws PIBadRequestException
     */
    public function test()
    {
        $respAuthToken = '{
         "id": 1,
         "jsonrpc": "2.0",
         "result": {
             "status": true,
             "value": {
                 "token": "eyJhbGciOiJIUz....jdpn9kIjuGRnGejmbFbM"
             }
         },
         "version": "privacyIDEA unknown"
        }';

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/auth')
            ->then()
            ->body($respAuthToken)
            ->end();
        $this->http->setUp();

        $response = $this->pi->getAuthToken();
        $this->assertEmpty($response, "Response is not false.");

        $this->pi->serviceAccountPass = "testPass";
        $this->pi->serviceAccountName = "testAdmin";
        $this->pi->serviceAccountRealm = "testRealm";

        $response = $this->pi->getAuthToken();
        $this->assertEquals('eyJhbGciOiJIUz....jdpn9kIjuGRnGejmbFbM', $response, "Auth token did not match.");

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/auth')
            ->then()
            ->end();
        $this->http->setUp();

        $response = $this->pi->getAuthToken();
        $this->assertFalse($response);
    }
}
