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

class ValidateCheckU2FTest extends TestCase implements PILog
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
        $this->pi->setRealm("testRealm");
        $this->pi->setLogger($this);
    }

    public function tearDown(): void
    {
        $this->tearDownHttpMock();
    }

    /**
     * @throws PIBadRequestException
     */
    /*public function testTriggerU2F()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body(Utils::triggerU2FResponseBody())
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck("testUser", "testPass", null, array('accept-language:en'));

        $this->assertEquals("Please confirm with your U2F token (Yubico U2F EE Serial 61730834)", $response->getMessage());
        $this->assertEquals("Please confirm with your U2F token (Yubico U2F EE Serial 61730834)", $response->getMessages());
        $this->assertEquals("12399202888279169736", $response->getTransactionID());
        $this->assertEquals("u2f", $response->getPreferredClientMode());
        $this->assertIsArray($response->getMultiChallenge());
        $this->assertTrue($response->getStatus());
        $this->assertFalse($response->getValue());
        $this->assertEquals(Utils::triggerU2FResponseBody(), $response->getRawResponse());
        $this->assertEquals("Please confirm with your U2F token (Yubico U2F EE Serial 61730834)", $response->u2fMessage());

        $temp = str_replace(" ", "", Utils::u2fSignRequest());
        $trimmedSignRequest = str_replace("\n", "", $temp);
        $this->assertEquals($trimmedSignRequest, $response->u2fSignRequest());
    }

    /**
     * @throws PIBadRequestException
     */
    /*public function testSuccess()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body(Utils::matchingOneTokenResponseBody())
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheckU2F("testUser", "12345678", Utils::u2fSignResponse(), array('accept-language:en'));

        $this->assertEquals("matching 1 tokens", $response->getMessage());
        $this->assertEquals(Utils::matchingOneTokenResponseBody(), $response->getRawResponse());
        $this->assertTrue($response->getStatus());
        $this->assertTrue($response->getValue());
    }

    /**
     * @throws PIBadRequestException
     */
    /*public function testNoSignResponse()
    {
        $response = $this->pi->validateCheckU2F("testUser", "12345678", "");
        $this->assertNull($response);
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