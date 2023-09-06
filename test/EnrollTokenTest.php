<?php

//require_once(__DIR__ . '/../src/Client-Autoloader.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once("utils/Utils.php");

use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use PHPUnit\Framework\TestCase;
use utils\Utils;

class EnrollTokenTest extends TestCase implements PILog
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
        $responseBodyAuth = Utils::postAuthResponseBody();

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/auth')
            ->then()
            ->body($responseBodyAuth)
            ->end();
        $this->http->setUp();

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->headerIs("Authorization", Utils::authToken())
            ->pathIs('/token/init')
            ->then()
            ->body(Utils::tokenInitResponseBody())
            ->end();
        $this->http->setUp();

        $this->pi->serviceAccountName = "TestServiceAccount";
        $this->pi->serviceAccountPass = "TestServicePass";
        $this->pi->serviceAccountRealm = "TestServiceRealm";

        $response = $this->pi->enrollToken(
            "testUser",
            "1",
            "totp",
            "Enrolled for test",
            array('accept-language:en'));

        $this->assertNotNull($response);
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('detail', $response);
        $this->assertEquals(Utils::imageData(), $response->detail->googleurl->img);
    }

    /**
     * @throws PIBadRequestException
     */
    public function testNoServiceAccount()
    {
        $response = $this->pi->enrollToken(
            "testUser",
            "1",
            "totp",
            "Enrolled for test");

        $this->assertNull($response);
    }

    /**
     * @throws PIBadRequestException
     */
    public function testNoUsername()
    {
        $response = $this->pi->enrollToken(
            "",
            "1",
            "totp",
            "Enrolled for test");

        $this->assertNull($response);
    }

    /**
     * @throws PIBadRequestException
     */
    public function testUserAlreadyHasAToken()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/auth')
            ->then()
            ->body(Utils::postAuthResponseBody())
            ->end();
        $this->http->setUp();

        $this->http->mock
            ->when()
            ->methodIs('GET')
            ->headerIs("Authorization", Utils::authToken())
            ->pathIs('/token')
            ->then()
            ->body(Utils::getTokenResponseBody())
            ->end();
        $this->http->setUp();

        $this->pi->serviceAccountName = "TestServiceAccount";
        $this->pi->serviceAccountPass = "TestServicePass";
        $this->pi->serviceAccountRealm = "TestServiceRealm";

        $response = $this->pi->enrollToken(
            "testUser",
            "1",
            "totp",
            "Enrolled for test");

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
