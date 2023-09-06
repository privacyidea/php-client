<?php

//require_once(__DIR__ . '/../src/Client-Autoloader.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once("utils/Utils.php");

use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use PHPUnit\Framework\TestCase;
use utils\Utils;

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
    public function testTriggerPushToken()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body(Utils::triggerPushTokenResponseBody())
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck("testUser", "testPass", null, array('accept_language:en'));

        $this->assertEquals("Bitte geben Sie einen OTP-Wert ein: , Please confirm the authentication on your mobile device!", $response->message);
        $this->assertEquals("Bitte geben Sie einen OTP-Wert ein: , Please confirm the authentication on your mobile device!", $response->messages);
        $this->assertEquals("02659936574063359702", $response->transactionID);
        $this->assertEquals("push", $response->preferredClientMode);
        $this->assertIsArray($response->multiChallenge);
        $this->assertTrue($response->status);
        $this->assertFalse($response->value);
        $this->assertEquals(Utils::triggerPushTokenResponseBody(), $response->raw);
        $this->assertEquals("Please confirm the authentication on your mobile device!", $response->pushMessage());
        $this->assertEquals("hotp", $response->triggeredTokenTypes()[0]);
        $this->assertEquals("push", $response->triggeredTokenTypes()[1]);
    }

    /**
     * @throws PIBadRequestException
     */
    public function testSuccess()
    {
        $this->http->mock
            ->when()
            ->methodIs('GET')
            ->pathIs('/validate/polltransaction')
            ->then()
            ->body(Utils::pollingResponseBody())
            ->end();
        $this->http->setUp();

        $response = $this->pi->pollTransaction("1234567890", array('accept_language:en'));

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
