<?php

//require_once(__DIR__ . '/../src/Client-Autoloader.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once('utils/Utils.php');

use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use PHPUnit\Framework\TestCase;
use utils\Utils;

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
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/triggerchallenge')
            ->then()
            ->body(Utils::tcSuccessResponseBody())
            ->end();
        $this->http->setUp();

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/auth')
            ->then()
            ->body(Utils::postAuthResponseBody())
            ->end();
        $this->http->setUp();

        $this->pi->serviceAccountName = "testServiceAccount";
        $this->pi->serviceAccountPass = "testServicePass";
        $this->pi->serviceAccountRealm = "testServiceRealm";

        $response = $this->pi->triggerchallenge("testUser");

        $this->assertEquals("BittegebenSieeinenOTP-Wertein:", $response->message);
        $this->assertEquals("BittegebenSieeinenOTP-Wertein:", $response->messages);
        $this->assertEquals("16734787285577957577", $response->transactionID);
        $this->assertEquals("otp", $response->preferredClientMode);
        $this->assertEquals(Utils::imageData(), $response->image);
        $this->assertTrue($response->status);
        $this->assertFalse($response->value);
        $this->assertEquals("totp", $response->triggeredTokenTypes()[0]);
        $this->assertEquals("BittegebenSieeinenOTP-Wertein:", $response->otpMessage());
        $this->assertEquals("", $response->webauthnMessage());
        $this->assertEquals("", $response->u2fMessage());
        $this->assertEquals("", $response->pushMessage());
        $this->assertEquals(Utils::imageData(), $response->multiChallenge[0]->image);
        $this->assertEquals("interactive", $response->multiChallenge[0]->clientMode);
    }

    /**
     * @throws PIBadRequestException
     */
    public function testNoServiceAccount()
    {
        $response = $this->pi->triggerchallenge("testUser", array('accept-language:en'));

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
