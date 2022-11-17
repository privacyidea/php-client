<?php

require_once(__DIR__ . '/../src/Client-Autoloader.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once('utils/UtilsForTests.php');

use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use PHPUnit\Framework\TestCase;
use utils\UtilsForTests;

class TriggerChallengeTest extends TestCase implements PILog
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
        $this->pi->logger = $this;
        $this->pi->realm = "testRealm";
    }

    public function tearDown(): void
    {
        $this->tearDownHttpMock();
    }

    /**
     * @throws PIBadRequestException
     */
    public function testTriggerChallengeSuccess()
    {
        $responseBody = "{\"detail\":{" . "\"preferred_client_mode\":\"interactive\"," . "\"attributes\":null," . "\"message\":\"BittegebenSieeinenOTP-Wertein:\"," .
            "\"messages\":[\"BittegebenSieeinenOTP-Wertein:\"]," . "\"multi_challenge\":[{" .
            "\"attributes\":null," . "\"message\":\"BittegebenSieeinenOTP-Wertein:\"," .
            "\"serial\":\"TOTP00021198\"," . "\"transaction_id\":\"16734787285577957577\"," .
            "\"type\":\"totp\"}]," . "\"serial\":\"TOTP00021198\"," . "\"threadid\":140050885818112," .
            "\"transaction_id\":\"16734787285577957577\"," .
            "\"transaction_ids\":[\"16734787285577957577\"]," . "\"type\":\"totp\"}," . "\"id\":1," .
            "\"jsonrpc\":\"2.0\"," . "\"result\":{" . "\"status\":true," . "\"value\":false}," .
            "\"time\":1649666174.5351279," . "\"version\":\"privacyIDEA3.6.3\"," .
            "\"versionnumber\":\"3.6.3\"," .
            "\"signature\":\"rsa_sha256_pss:4b0f0e12c2...89409a2e65c87d27b\"}";

        $authToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwicmVhbG0iOiIiLCJub25jZSI6IjVjOTc4NWM5OWU";

        $responseBodyAuth = UtilsForTests::authToken($authToken);

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/triggerchallenge')
            ->then()
            ->body($responseBody)
            ->end();
        $this->http->setUp();

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/auth')
            ->then()
            ->body($responseBodyAuth)
            ->end();
        $this->http->setUp();

        $this->pi->serviceAccountName = "testServiceAccount";
        $this->pi->serviceAccountPass = "testServicePass";
        $this->pi->serviceAccountRealm = "testServiceRealm";

        $response = $this->pi->triggerchallenge("testUser");

        $this->assertEquals("BittegebenSieeinenOTP-Wertein:", $response->message);
        $this->assertEquals("BittegebenSieeinenOTP-Wertein:", $response->messages);
        $this->assertEquals("16734787285577957577", $response->transactionID);
        $this->assertEquals("interactive", $response->preferredClientMode);
        $this->assertTrue($response->status);
        $this->assertFalse($response->value);
        $this->assertEquals("totp", $response->triggeredTokenTypes()[0]);
        $this->assertEquals("BittegebenSieeinenOTP-Wertein:", $response->otpMessage());
        $this->assertEquals("", $response->webauthnMessage());
        $this->assertEquals("", $response->u2fMessage());
        $this->assertEquals("", $response->pushMessage());
    }

    /**
     * @throws PIBadRequestException
     */
    public function testNoServiceAccount()
    {
        $response = $this->pi->triggerchallenge("testUser");

        $this->assertNull($response);
    }

    /**
     * @throws PIBadRequestException
     */
    public function testNoUsername()
    {
        $response = $this->pi->triggerchallenge("");

        $this->assertNull($response);
    }

    public function testWrongServerURL()
    {
        $e = "";
        $this->pi = new PrivacyIDEA("testUserAgent", "https://xasfdfasda.com");

        try
        {
            $this->pi->triggerchallenge("testUser");
        }
        catch (PIBadRequestException $e)
        {
            echo $e;
        }

        $this->assertNotEmpty($e);
        $this->assertIsObject($e);
        $this->assertIsNotString($e);
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
