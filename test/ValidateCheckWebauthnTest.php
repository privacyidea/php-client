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

class ValidateCheckWebauthnTest extends TestCase implements PILog
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
    /*public function testTriggerWebAuthn()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body(Utils::triggerWebauthnResponseBody())
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck("testUser", "testPass");

        $this->assertEquals("Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)", $response->getMessage());
        $this->assertEquals("Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)", $response->getMessages());
        $this->assertEquals("16786665691788289392", $response->getTransactionID());
        $this->assertEquals("webauthn", $response->getPreferredClientMode());
        $this->assertEquals("16786665691788289392", $response->getMultiChallenge()[0]->transactionID);
        $this->assertEquals("Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)", $response->getMultiChallenge()[0]->message);
        $this->assertEquals("WAN00025CE7", $response->getMultiChallenge()[0]->serial);
        $this->assertEquals("webauthn", $response->getMultiChallenge()[0]->type);
        $this->assertEquals(Utils::imageData(), $response->getMultiChallenge()[0]->image);
        $this->assertTrue($response->getStatus());
        $this->assertFalse($response->getValue());
        $this->assertEquals("Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)", $response->webauthnMessage());
        $temp = str_replace(" ", "", Utils::webauthnSignRequest());
        $trimmedSignRequest = str_replace("\n", "", $temp);
        $this->assertEquals($trimmedSignRequest, $response->webauthnSignRequest());
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

        $response = $this->pi->validateCheckWebAuthn("testUser", "12345678", Utils::webauthnSignResponse(), "test.it", array('accept-language:en'));

        $this->assertNotNull($response);
        $this->assertEquals(Utils::matchingOneTokenResponseBody(), $response->getRawResponse());
        $this->assertEquals("matching 1 tokens", $response->getMessage());
        $this->assertTrue($response->getStatus());
        $this->assertTrue($response->getValue());

        $signRequest = $response->webAuthnSignRequest();
        $this->assertEmpty($signRequest);
    }

    /**
     * @throws PIBadRequestException
     */
    /*public function testNoSignResponse()
    {
        $response = $this->pi->validateCheckWebAuthn("testUser", "12345678", "", "test.it");
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
}*/