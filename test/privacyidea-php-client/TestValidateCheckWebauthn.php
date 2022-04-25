<?php

require_once('../../src/Client-Autoloader.php');
require_once('../../vendor/autoload.php');
require_once('UtilsForTests.php');

use PHPUnit\Framework\TestCase;
use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;

class TestValidateCheckWebauthn extends TestCase implements PILog
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
        $webauthnrequest = "{\n" . "            \"allowCredentials\": [\n" . "              {\n" .
            "                \"id\": \"83De8z_CNqogB6aCyKs6dWIqwpOpzVoNaJ74lgcpuYN7l-95QsD3z-qqPADqsFlPwBXCMqEPssq75kqHCMQHDA\",\n" .
            "                \"transports\": [\n" . "                  \"internal\",\n" .
            "                  \"nfc\",\n" . "                  \"ble\",\n" .
            "                  \"usb\"\n" . "                ],\n" .
            "                \"type\": \"public-key\"\n" . "              }\n" .
            "            ],\n" .
            "            \"challenge\": \"dHzSmZnAhxEq0szRWMY4EGg8qgjeBhJDjAPYKWfd2IE\",\n" .
            "            \"rpId\": \"office.netknights.it\",\n" .
            "            \"timeout\": 60000,\n" .
            "            \"userVerification\": \"preferred\"\n" . "          }\n";

        $responseBody =
            "{\n" . "  \"detail\": {\n" . "    \"attributes\": {\n" . "      \"hideResponseInput\": true,\n" .
            "      \"img\": \"static/img/FIDO-U2F-Security-Key-444x444.png\",\n" .
            "      \"webAuthnSignRequest\": {\n" . "        \"allowCredentials\": [\n" . "          {\n" .
            "            \"id\": \"83De8z_CNqogB6aCyKs6dWIqwpOpzVoNaJ74lgcpuYN7l-95QsD3z-qqPADqsFlPwBXCMqEPssq75kqHCMQHDA\",\n" .
            "            \"transports\": [\n" . "              \"internal\",\n" . "              \"nfc\",\n" .
            "              \"ble\",\n" . "              \"usb\"\n" . "            ],\n" .
            "            \"type\": \"public-key\"\n" . "          }\n" . "        ],\n" .
            "        \"challenge\": \"dHzSmZnAhxEq0szRWMY4EGg8qgjeBhJDjAPYKWfd2IE\",\n" .
            "        \"rpId\": \"office.netknights.it\",\n" . "        \"timeout\": 60000,\n" .
            "        \"userVerification\": \"preferred\"\n" . "      }\n" . "    },\n" .
            "    \"message\": \"Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)\",\n" .
            "    \"messages\": [\n" .
            "      \"Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)\"\n" . "    ],\n" .
            "    \"multi_challenge\": [\n" . "      {\n" . "        \"attributes\": {\n" .
            "          \"hideResponseInput\": true,\n" .
            "          \"img\": \"static/img/FIDO-U2F-Security-Key-444x444.png\",\n" .
            "          \"webAuthnSignRequest\": " . $webauthnrequest . "        },\n" .
            "        \"message\": \"Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)\",\n" .
            "        \"serial\": \"WAN00025CE7\",\n" . "        \"transaction_id\": \"16786665691788289392\",\n" .
            "        \"type\": \"webauthn\"\n" . "      }\n" . "    ],\n" . "    \"serial\": \"WAN00025CE7\",\n" .
            "    \"threadid\": 140040275289856,\n" . "    \"transaction_id\": \"16786665691788289392\",\n" .
            "    \"transaction_ids\": [\n" . "      \"16786665691788289392\"\n" . "    ],\n" .
            "    \"type\": \"webauthn\"\n" . "  },\n" . "  \"id\": 1,\n" . "  \"jsonrpc\": \"2.0\",\n" .
            "  \"result\": {\n" . "    \"authentication\": \"CHALLENGE\",\n" . "    \"status\": true,\n" .
            "    \"value\": false\n" . "  },\n" . "  \"time\": 1611916339.8448942\n" . "}";

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body($responseBody)
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck("testUser", "testPass");

        $this->assertEquals("Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)", $response->message);
        $this->assertEquals("Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)", $response->messages);
        $this->assertEquals("16786665691788289392", $response->transactionID);
        $this->assertEquals("16786665691788289392", $response->multiChallenge[0]->transactionID);
        $this->assertEquals("Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)", $response->multiChallenge[0]->message);
        $this->assertEquals("WAN00025CE7", $response->multiChallenge[0]->serial);
        $this->assertEquals("webauthn", $response->multiChallenge[0]->type);
        $this->assertArrayHasKey("img", $response->multiChallenge[0]->attributes);
        $this->assertTrue($response->status);
        $this->assertFalse($response->value);
        $this->assertEquals("Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)", $response->webauthnMessage());
        $temp = str_replace(" ", "", $webauthnrequest);
        $trimmedSignRequest = str_replace("\n", "", $temp);
        $this->assertEquals($trimmedSignRequest, $response->webauthnSignRequest());
    }

    /**
     * @throws PIBadRequestException
     */
    public function testSuccess()
    {
        $webauthnSignResponse = "{" . "\"credentialid\":\"X9FrwMfmzj...saw21\"," .
            "\"authenticatordata\":\"xGzvgq0bVGR3WR0A...ZJdA7cBAAAACA\"," .
            "\"clientdata\":\"eyJjaGFsbG...dfhs\"," .
            "\"signaturedata\":\"MEUCIQDNrG...43hc\"}";

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body(UtilsForTests::responseBodySuccess())
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheckWebAuthn("testUser", "12345678", $webauthnSignResponse, "test.it");

        $this->assertNotNull($response);
        $this->assertEquals(UtilsForTests::responseBodySuccess(), $response->raw);
        $this->assertEquals("matching 1 tokens", $response->message);
        $this->assertTrue($response->status);
        $this->assertTrue($response->value);
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
