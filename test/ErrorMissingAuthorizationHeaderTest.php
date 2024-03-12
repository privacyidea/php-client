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
}*/