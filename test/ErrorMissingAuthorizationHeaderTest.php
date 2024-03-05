<?php

//require_once(__DIR__ . '/../src/Client-Autoloader.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once('utils/Utils.php');

use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use PHPUnit\Framework\TestCase;
use utils\Utils;

class ErrorMissingAuthorizationHeaderTest extends TestCase implements PILog
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
    public function testErrorMissingAuthorizationHeader()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/triggerchallenge')
            ->then()
            ->body(Utils::errorMissingAuthorizationHeaderResponseBody())
            ->end();
        $this->http->setUp();

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/auth')
            ->then()
            ->body(Utils::postAuthNoRoleAdminResponseBody())
            ->end();
        $this->http->setUp();

        $this->pi->serviceAccountName = "testServiceAccount";
        $this->pi->serviceAccountPass = "testServicePass";
        $this->pi->serviceAccountRealm = "testServiceRealm";

        $response = $this->pi->triggerchallenge("testUser");

        $this->assertEquals("4033", $response->errorCode);
        $this->assertEquals("Authentication failure. Missing Authorization header.", $response->errorMessage);
        $this->assertFalse($response->status);
        $this->assertEquals("", $response->otpMessage());
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