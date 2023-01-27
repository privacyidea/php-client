<?php

//require_once(__DIR__ . '/../src/Client-Autoloader.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once('utils/Utils.php');

use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use PHPUnit\Framework\TestCase;
use utils\Utils;

class ValidateCheckWebauthnTest extends TestCase implements PILog
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
    public function testTriggerWebAuthn()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body(Utils::triggerWebauthnResponseBody())
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck("testUser", "testPass");

        $this->assertEquals("Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)", $response->message);
        $this->assertEquals("Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)", $response->messages);
        $this->assertEquals("16786665691788289392", $response->transactionID);
        $this->assertEquals("webauthn", $response->preferredClientMode);
        $this->assertEquals("16786665691788289392", $response->multiChallenge[0]->transactionID);
        $this->assertEquals("Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)", $response->multiChallenge[0]->message);
        $this->assertEquals("WAN00025CE7", $response->multiChallenge[0]->serial);
        $this->assertEquals("webauthn", $response->multiChallenge[0]->type);
        $this->assertEquals(Utils::imageData(), $response->multiChallenge[0]->image);
        $this->assertTrue($response->status);
        $this->assertFalse($response->value);
        $this->assertEquals("Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)", $response->webauthnMessage());
        $temp = str_replace(" ", "", Utils::webauthnSignRequest());
        $trimmedSignRequest = str_replace("\n", "", $temp);
        $this->assertEquals($trimmedSignRequest, $response->webauthnSignRequest());
    }

    /**
     * @throws PIBadRequestException
     */
    public function testSuccess()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body(Utils::matchingOneTokenResponseBody())
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheckWebAuthn("testUser", "12345678", Utils::webauthnSignResponse(), "test.it");

        $this->assertNotNull($response);
        $this->assertEquals(Utils::matchingOneTokenResponseBody(), $response->raw);
        $this->assertEquals("matching 1 tokens", $response->message);
        $this->assertTrue($response->status);
        $this->assertTrue($response->value);

        $signRequest = $response->webAuthnSignRequest();
        $this->assertEmpty($signRequest);
    }

    /**
     * @throws PIBadRequestException
     */
    public function testNoSignResponse()
    {
        $response = $this->pi->validateCheckWebAuthn("testUser", "12345678", "", "test.it");
        $this->assertNull($response);
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
