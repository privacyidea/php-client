<?php

require_once(__DIR__ . '/../src/Client-Autoloader.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once("utils/UtilsForTests.php");

use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;
use PHPUnit\Framework\TestCase;
use utils\UtilsForTests;

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
        $authToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NoBVmAurqcaaMmwM-AsD1S6chGIM";

        $img = "data:image/png;base64,iVBdgfgsdfgRK5CYII=";

        $responseBodyAuth = UtilsForTests::authToken($authToken);

        $responseBodyTokenInit = "{\n" . "    \"detail\": {\n" . "        \"googleurl\": {\n" .
            "            \"description\": \"URL for google Authenticator\",\n" .
            "            \"img\": \"data:image/png;base64,iVBdgfgsdfgRK5CYII=\",\n" .
            "            \"value\": \"otpauth://hotp/OATH0003A0AA?secret=4DK5JEEQMWY3VES7EWB4M36TAW4YC2YH&counter=1&digits=6&issuer=privacyIDEA\"\n" .
            "        },\n" . "        \"oathurl\": {\n" .
            "            \"description\": \"URL for OATH token\",\n" .
            "            \"img\": \"data:image/png;base64,iVBdgfgsdfgRK5CYII=\",\n" .
            "            \"value\": \"oathtoken:///addToken?name=OATH0003A0AA&lockdown=true&key=e0d5d4909065b1ba925f2583c66fd305b9816b07\"\n" .
            "        },\n" . "        \"otpkey\": {\n" .
            "            \"description\": \"OTP seed\",\n" .
            "            \"img\": \"data:image/png;base64,iVBdgfgsdfgRK5CYII=\",\n" .
            "            \"value\": \"seed://e0d5d4909065b1ba925f2583c66fd305b9816b07\",\n" .
            "            \"value_b32\": \"4DK5JEEQMWY3VES7EWB4M36TAW4YC2YH\"\n" .
            "        },\n" . "        \"rollout_state\": \"\",\n" .
            "        \"serial\": \"OATH0003A0AA\",\n" .
            "        \"threadid\": 140470638720768\n" . "    },\n" .
            "    \"id\": 1,\n" . "    \"jsonrpc\": \"2.0\",\n" .
            "    \"result\": {\n" . "        \"status\": true,\n" .
            "        \"value\": true\n" . "    },\n" .
            "    \"time\": 1592834605.532012,\n" .
            "    \"version\": \"privacyIDEA 3.3.3\",\n" .
            "    \"versionnumber\": \"3.3.3\",\n" .
            "    \"signature\": \"rsa_sha256_pss:\"\n" . "}";

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
            ->headerIs("Authorization", $authToken)
            ->pathIs('/token/init')
            ->then()
            ->body($responseBodyTokenInit)
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

        $this->assertNotNull($response);
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('detail', $response);
        $this->assertEquals($img, $response->detail->googleurl->img);
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
        $authToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NoBVmAurqcaaMmwM-AsD1S6chGIM";

        $responseBodyAuth = UtilsForTests::authToken($authToken);

        $responseBodyGetToken = "{\"id\":1," . "\"jsonrpc\":\"2.0\"," . "\"result\":{" . "\"status\":true," . "\"value\":{" .
            "\"count\":1," . "\"current\":1," . "\"tokens\":[{" . "\"active\":true," . "\"count\":2," .
            "\"count_window\":10," . "\"description\":\"\"," . "\"failcount\":0," . "\"id\":347," .
            "\"info\":{" . "\"count_auth\":\"1\"," . "\"count_auth_success\":\"1\"," .
            "\"hashlib\":\"sha1\"," . "\"last_auth\":\"2022-03-2912:18:59.639421+02:00\"," .
            "\"tokenkind\":\"software\"}," . "\"locked\":false," . "\"maxfail\":10," . "\"otplen\":6," .
            "\"realms\":[\"defrealm\"]," . "\"resolver\":\"deflocal\"," . "\"revoked\":false," .
            "\"rollout_state\":\"\"," . "\"serial\":\"OATH00123564\"," . "\"sync_window\":1000," .
            "\"tokentype\":\"hotp\"," . "\"user_editable\":false," . "\"user_id\":\"5\"," .
            "\"user_realm\":\"defrealm\"," . "\"username\":\"Test\"}]}}," . "\"time\":1648549489.57896," .
            "\"version\":\"privacyIDEA3.6.3\"," . "\"versionnumber\":\"3.6.3\"," .
            "\"signature\":\"rsa_sha256_pss:58c4eed1...5247c47e3e\"}";

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
            ->methodIs('GET')
            ->headerIs("Authorization", $authToken)
            ->pathIs('/token')
            ->then()
            ->body($responseBodyGetToken)
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
