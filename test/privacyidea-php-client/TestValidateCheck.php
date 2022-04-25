<?php

require_once('../../src/Client-Autoloader.php');
require_once('../../vendor/autoload.php');
require_once('UtilsForTests.php');

use PHPUnit\Framework\TestCase;
use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;

class TestValidateCheck extends TestCase implements PILog
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
        $this->pi = new PrivacyIDEA('testUserAgent', "http://localhost:8082");

        $this->pi->logger = $this;
        $this->pi->sslVerifyHost = false;
        $this->pi->sslVerifyPeer = false;
        $this->pi->realm = "testRealm";
    }

    public function tearDown(): void
    {
        $this->tearDownHttpMock();
    }

    /**
     * @throws PIBadRequestException
     */
    public function testOTPSuccess()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body(UtilsForTests::responseBodySuccess())
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck("testUser", "testPass");

        $this->assertEquals("matching 1 tokens", $response->message);
        $this->assertEquals(UtilsForTests::responseBodySuccess(), $response->raw);
        $this->assertTrue($response->status);
        $this->assertTrue($response->value);
        $this->assertEquals("", $response->otpMessage());
    }

    /**
     * @throws PIBadRequestException
     */
    public function testEmptyResponse()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body("")
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck("testUser", "testPass", "123456677");

        $this->assertNull($response);
    }

    /**
     * @throws PIBadRequestException
     */
    public function testNoUsername()
    {
        $response = $this->pi->validateCheck("", "testPass");

        $this->assertNull($response);
    }

    /**
     * @throws PIBadRequestException
     */
    public function testUserNotFound()
    {
        $responseBody =
            "{" . "\"detail\":null," . "\"id\":1," . "\"jsonrpc\":\"2.0\"," . "\"result\":{" . "\"error\":{" .
            "\"code\":904," . "\"message\":\"ERR904: The user can not be found in any resolver in this realm!\"}," .
            "\"status\":false}," . "\"time\":1649752303.65651," . "\"version\":\"privacyIDEA 3.6.3\"," .
            "\"signature\":\"rsa_sha256_pss:1c64db29cad0dc127d6...5ec143ee52a7804ea1dc8e23ab2fc90ac0ac147c0\"}";

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body($responseBody)
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck("testFalseUser", "testFalsePass");

        $this->assertEquals("904", $response->errorCode);
        $this->assertEquals("ERR904: The user can not be found in any resolver in this realm!", $response->errorMessage);
        $this->assertFalse($response->status);
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
