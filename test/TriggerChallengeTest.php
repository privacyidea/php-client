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

/*require_once(__DIR__ . '/../vendor/autoload.php');
require_once('utils/Utils.php');

use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use PHPUnit\Framework\TestCase;
use utils\Utils;

class TriggerChallengeTest extends TestCase implements PILog
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
    /*public function testTriggerChallengeSuccess()
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

        $this->pi->setServiceAccountName("testServiceAccount");
        $this->pi->setServiceAccountPass("testServicePass");
        $this->pi->setServiceAccountRealm("testServiceRealm");

        $response = $this->pi->triggerchallenge("testUser");

        $this->assertEquals("BittegebenSieeinenOTP-Wertein:", $response->getMessage());
        $this->assertEquals("BittegebenSieeinenOTP-Wertein:", $response->getMessages());
        $this->assertEquals("16734787285577957577", $response->getTransactionID());
        $this->assertEquals("otp", $response->getPreferredClientMode());
        $this->assertTrue($response->getStatus());
        $this->assertFalse($response->getValue());
        $this->assertEquals("totp", $response->triggeredTokenTypes()[0]);
        $this->assertEquals("BittegebenSieeinenOTP-Wertein:", $response->otpMessage());
        $this->assertEquals("", $response->webauthnMessage());
        $this->assertEquals("", $response->u2fMessage());
        $this->assertEquals("", $response->pushMessage());
        $this->assertEquals(Utils::imageData(), $response->getMultiChallenge()[0]->image);
        $this->assertEquals("interactive", $response->getMultiChallenge()[0]->clientMode);
    }

    /**
     * @throws PIBadRequestException
     */
    /*public function testNoServiceAccount()
    {
        $response = $this->pi->triggerchallenge("testUser", array('accept-language:en'));

        $this->assertNull($response);
    }

    /**
     * @throws PIBadRequestException
     */
    /*public function testNoUsername()
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

    public function piDebug($message): void
    {
        echo $message . "\n";
    }

    public function piError($message): void
    {
        echo "error: " . $message . "\n";
    }
}*/