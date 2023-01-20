<?php

require_once(__DIR__ . '/../src/Client-Autoloader.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once('utils/UtilsForTests.php');

use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use PHPUnit\Framework\TestCase;
use utils\UtilsForTests;

class U2FTest extends TestCase implements PILog
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
    public function testTriggerU2F()
    {
        $u2frequest =  "{\"appId\":\"https:\/\/ttype.u2f\"," .
            "\"challenge\":\"TZKiB0VFFMFsnlz00lF5iCqtQduDJf56AeJAY_BT4NU\"," .
            "\"keyHandle\":\"UUHmZ4BUFCrt7q88MhlQJYu4G5qB9l7ScjRRxA-M35cTH-uHWyMEpxs4WBzbkjlZqzZW1lC-jDdFd2pKDUsNnA\"," .
            "\"version\":\"U2F_V2\"}";

        $responseBody = "{" . "\"detail\":{" . "\"preferred_client_mode\":\"u2f\"," . "\"attributes\":{" . "\"hideResponseInput\":true," .
        "\"img\":\"static/img/FIDO-U2F-Security-Key-444x444.png\"," . "\"u2fSignRequest\":{" .
        "\"appId\":\"http//ttype.u2f\"," . "\"challenge\":\"TZKiB0VFFMF...tQduDJf56AeJAY_BT4NU\"," .
        "\"keyHandle\":\"UUHmZ4BUFCrt7q88MhlQ...qzZW1lC-jDdFd2pKDUsNnA\"," .
        "\"version\":\"U2F_V2\"}}," .
        "\"message\":\"Please confirm with your U2F token (Yubico U2F EE Serial 61730834)\"," .
        "\"messages\":[\"Please confirm with your U2F token (Yubico U2F EE Serial 61730834)\"]," .
        "\"multi_challenge\":[{" . "\"attributes\":{" . "\"hideResponseInput\":true," .
        "\"img\":\"static/img/FIDO-U2F-Security-Key-444x444.png\"," . "\"u2fSignRequest\":" .
        $u2frequest . "}," .
        "\"message\":\"Please confirm with your U2F token (Yubico U2F EE Serial 61730834)\"," .
        "\"serial\":\"U2F00014651\"," . "\"transaction_id\":\"12399202888279169736\"," .
        "\"type\":\"u2f\"}]," . "\"serial\":\"U2F00014651\"," . "\"threadid\":140050978137856," .
        "\"transaction_id\":\"12399202888279169736\"," .
        "\"transaction_ids\":[\"12399202888279169736\"]," . "\"type\":\"u2f\"}," . "\"id\":1," .
        "\"jsonrpc\":\"2.0\"," . "\"result\":{" . "\"status\":true," . "\"value\":false}," .
        "\"time\":1649769348.7552881," . "\"version\":\"privacyIDEA 3.6.3\"," .
        "\"versionnumber\":\"3.6.3\"," .
        "\"signature\":\"rsa_sha256_pss:3e51d814...dccd5694b8c15943e37e1\"}";

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body($responseBody)
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck("testUser", "testPass");

        $this->assertEquals("Please confirm with your U2F token (Yubico U2F EE Serial 61730834)", $response->message);
        $this->assertEquals("Please confirm with your U2F token (Yubico U2F EE Serial 61730834)", $response->messages);
        $this->assertEquals("12399202888279169736", $response->transactionID);
        $this->assertEquals("u2f", $response->preferredClientMode);
        $this->assertIsArray($response->multiChallenge);
        $this->assertTrue($response->status);
        $this->assertFalse($response->value);
        $this->assertEquals($responseBody, $response->raw);
        $temp = str_replace(" ", "", $u2frequest);
        $trimmedSignRequest = str_replace("\n", "", $temp);
        $this->assertEquals($trimmedSignRequest, $response->u2fSignRequest());
        $this->assertEquals("Please confirm with your U2F token (Yubico U2F EE Serial 61730834)", $response->u2fMessage());
    }

    /**
     * @throws PIBadRequestException
     */
    public function testSuccess()
    {
        $u2fSignResponse = "{\"clientData\":\"eyJjaGFsbGVuZ2UiOiJpY2UBc3NlcnRpb24ifQ\"," . "\"errorCode\":0," .
            "\"keyHandle\":\"UUHmZ4BUFCrt7q88MhlQkjlZqzZW1lC-jDdFd2pKDUsNnA\"," .
            "\"signatureData\":\"AQAAAxAwRQIgZwEObruoCRRo738F9up1tdV2M0H1MdP5pkO5Eg\"}";

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body(UtilsForTests::responseBodySuccess())
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheckU2F("testUser", "12345678", $u2fSignResponse);

        $this->assertEquals("matching 1 tokens", $response->message);
        $this->assertEquals(UtilsForTests::responseBodySuccess(), $response->raw);
        $this->assertTrue($response->status);
        $this->assertTrue($response->value);
    }

    /**
     * @throws PIBadRequestException
     */
    public function testNoSignResponse()
    {
        $response = $this->pi->validateCheckU2F("testUser", "12345678", "");
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
