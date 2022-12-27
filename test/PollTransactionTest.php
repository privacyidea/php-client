<?php

require_once(__DIR__ . '/../src/Client-Autoloader.php');
require_once(__DIR__ . '/../vendor/autoload.php');

use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use PHPUnit\Framework\TestCase;

class PollTransactionTest extends TestCase implements PILog
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
    public function testTriggerPUSH()
    {
        $responseBody = "{\n" . "  \"detail\": {\n" . "\"preferred_client_mode\":\"poll\"," . "    \"attributes\": null,\n" .
            "    \"message\": \"Bitte geben Sie einen OTP-Wert ein: , Please confirm the authentication on your mobile device!\",\n" .
            "    \"messages\": [\n" . "      \"Bitte geben Sie einen OTP-Wert ein: \",\n" .
            "      \"Please confirm the authentication on your mobile device!\"\n" . "    ],\n" .
            "    \"multi_challenge\": [\n" . "      {\n" . "        \"attributes\": null,\n" .
            "        \"message\": \"Bitte geben Sie einen OTP-Wert ein: \",\n" .
            "        \"serial\": \"OATH00020121\",\n" .
            "        \"transaction_id\": \"02659936574063359702\",\n" . "        \"type\": \"hotp\"\n" .
            "      },\n" . "      {\n" . "        \"attributes\": null,\n" .
            "        \"message\": \"Please confirm the authentication on your mobile device!\",\n" .
            "        \"serial\": \"PIPU0001F75E\",\n" .
            "        \"transaction_id\": \"02659936574063359702\",\n" . "        \"type\": \"push\"\n" .
            "      }\n" . "    ],\n" . "    \"serial\": \"PIPU0001F75E\",\n" .
            "    \"threadid\": 140040525666048,\n" . "    \"transaction_id\": \"02659936574063359702\",\n" .
            "    \"transaction_ids\": [\n" . "      \"02659936574063359702\",\n" .
            "      \"02659936574063359702\"\n" . "    ],\n" . "    \"type\": \"push\"\n" . "  },\n" .
            "  \"id\": 1,\n" . "  \"jsonrpc\": \"2.0\",\n" . "  \"result\": {\n" .
            "    \"status\": true,\n" . "    \"value\": false\n" . "  },\n" .
            "  \"time\": 1589360175.594304,\n" . "  \"version\": \"privacyIDEA 3.2.1\",\n" .
            "  \"versionnumber\": \"3.2.1\",\n" . "  \"signature\": \"rsa_sha256_pss:AAAAAAAAAA\"\n" . "}";

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body($responseBody)
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck("testUser", "testPass");

        $this->assertEquals("Bitte geben Sie einen OTP-Wert ein: , Please confirm the authentication on your mobile device!", $response->message);
        $this->assertEquals("Bitte geben Sie einen OTP-Wert ein: , Please confirm the authentication on your mobile device!", $response->messages);
        $this->assertEquals("02659936574063359702", $response->transactionID);
        $this->assertEquals("push", $response->preferredClientMode);
        $this->assertIsArray($response->multiChallenge);
        $this->assertTrue($response->status);
        $this->assertFalse($response->value);
        $this->assertEquals($responseBody, $response->raw);
        $this->assertEquals("Please confirm the authentication on your mobile device!", $response->pushMessage());
        $this->assertEquals("hotp", $response->triggeredTokenTypes()[0]);
        $this->assertEquals("push", $response->triggeredTokenTypes()[1]);
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
