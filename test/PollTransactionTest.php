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
require_once("utils/Utils.php");

use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use PHPUnit\Framework\TestCase;
use utils\Utils;

class PollTransactionTest extends TestCase implements PILog
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
        $this->pi = new PrivacyIDEA('testUserAgent', "localhost:8082");
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
    /*public function testTriggerPushToken()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body(Utils::triggerPushTokenResponseBody())
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck("testUser", "testPass", null, array('accept-language:en'));

        $this->assertEquals("Bitte geben Sie einen OTP-Wert ein: , Please confirm the authentication on your mobile device!", $response->getMessage());
        $this->assertEquals("Bitte geben Sie einen OTP-Wert ein: , Please confirm the authentication on your mobile device!", $response->getMessages());
        $this->assertEquals("02659936574063359702", $response->getTransactionID());
        $this->assertEquals("push", $response->getPreferredClientMode());
        $this->assertIsArray($response->getMultiChallenge());
        $this->assertTrue($response->getStatus());
        $this->assertFalse($response->getValue());
        $this->assertEquals(Utils::triggerPushTokenResponseBody(), $response->getRawResponse());
        $this->assertEquals("Please confirm the authentication on your mobile device!", $response->pushMessage());
        $this->assertEquals("hotp", $response->triggeredTokenTypes()[0]);
        $this->assertEquals("push", $response->triggeredTokenTypes()[1]);
    }

    /**
     * @throws PIBadRequestException
     */
    /*public function testSuccess()
    {
        $this->http->mock
            ->when()
            ->methodIs('GET')
            ->pathIs('/validate/polltransaction')
            ->then()
            ->body(Utils::pollingResponseBody())
            ->end();
        $this->http->setUp();

        $response = $this->pi->pollTransaction("1234567890", array('accept-language:en'));

        $this->assertNotNull($response);
        $this->assertTrue($response);
    }

    /**
     * @throws PIBadRequestException
     */
    /*public function testNoTransactionID()
    {
        $response = $this->pi->pollTransaction("");
        $this->assertFalse($response);
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