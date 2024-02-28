<?php
/*
 * Copyright 2024 NetKnights GmbH - lukas.matusiewicz@netknights.it
 * <p>
 * Licensed under the GNU AFFERO GENERAL PUBLIC LICENSE Version 3;
 * you may not use this file except in compliance with the License.
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

//require_once(__DIR__ . '/../src/Client-Autoloader.php');
/*require_once(__DIR__ . '/../vendor/autoload.php');
require_once('utils/Utils.php');

use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use PHPUnit\Framework\TestCase;
use utils\Utils;

class ValidateCheckTest extends TestCase implements PILog
{
    private PrivacyIDEA $pi;

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

        $this->pi->setLogger($this);
        $this->pi->setSSLVerifyHost(false);
        $this->pi->setSSLVerifyPeer(false);
        $this->pi->setForwardClientIP(true);
        $this->pi->setRealm("testRealm");
    }

    public function tearDown(): void
    {
        $this->tearDownHttpMock();
    }

    /**
     * @throws PIBadRequestException
     */
    /*public function testOTPSuccess()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body(Utils::matchingOneTokenResponseBody())
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck("testUser", "testPass", null, array('accept-language:en'));

        $this->assertEquals("matching 1 tokens", $response->getMessage());
        $this->assertEquals(Utils::matchingOneTokenResponseBody(), $response->getRawResponse());
        $this->assertTrue($response->getStatus());
        $this->assertTrue($response->getValue());
        $this->assertEquals("", $response->otpMessage());
    }

    /**
     * @throws PIBadRequestException
     */
    /*public function testEmptyResponse()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body("")
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck("testUser", "testPass", "123456677");

        $this->assertNull($response);
    }

    /**
     * @throws PIBadRequestException
     */
    /*public function testNoUsername()
    {
        $response = $this->pi->validateCheck("", "testPass");

        $this->assertNull($response);
    }

    /**
     * @throws PIBadRequestException
     */
    /*public function testUserNotFound()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body(Utils::errorUserNotFoundResponseBody())
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck("testFalseUser", "testFalsePass");

        $this->assertEquals("904", $response->getErrorCode());
        $this->assertEquals("ERR904: The user can not be found in any resolver in this realm!", $response->getErrorMessage());
        $this->assertFalse($response->getStatus());
        $this->assertEquals("", $response->otpMessage());
    }

    public function piDebug($message): void
    {
        echo $message . "\n";
    }

    public function piError($message): void
    {
        echo "error: " . $message . "\n";
    }
}