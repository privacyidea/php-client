<?php

//require_once(__DIR__ . '/../src/Client-Autoloader.php');
/*require_once(__DIR__ . '/../vendor/autoload.php');
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
        $this->pi->setLogger($this);
        $this->pi->setRealm("testRealm");
    }

    public function tearDown(): void
    {
        $this->tearDownHttpMock();
    }

    /**
     * @throws PIBadRequestException
     */
/*    public function testErrorMissingAuthorizationHeader()
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

        $this->pi->setServiceAccountName("testServiceAccount");
        $this->pi->setServiceAccountPass("testServicePass");
        $this->pi->setServiceAccountRealm("testServiceRealm");

        $response = $this->pi->triggerchallenge("testUser");

        $this->assertEquals("4033", $response->getErrorCode());
        $this->assertEquals("Authentication failure. Missing Authorization header.", $response->getErrorMessage());
        $this->assertFalse($response->getStatus());
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