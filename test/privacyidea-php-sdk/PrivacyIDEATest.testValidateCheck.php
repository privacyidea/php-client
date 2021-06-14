<?php

declare(strict_types=1);

require_once('../../src/privacyidea-php-sdk/SDK-Autoloader.php');
require_once('../../vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use InterNations\Component\HttpMock\PHPUnit\HttpMockTrait;

class PrivacyIDEATest extends TestCase
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
        $this->pi = new PrivacyIDEA('testUserAgent', "http://127.0.0.1:8082");
        $this->pi->disableLog = true;
    }

    public function tearDown(): void
    {
        $this->tearDownHttpMock();
    }

    public function testValidateCheck()
    {
        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body(null)
            ->end();
        $this->http->setUp();

        $response = $this->pi->validateCheck(['user' => 'testUser', 'pass' => 'testPass'], "1234567890");
        $this->assertNull($response, "Response is not NULL.");

        $respValidateCheck = '{
          "detail": {
            "attributes": null,
            "message": "Please enter OTP: ",
            "messages": [
              "Please enter OTP: "
            ],
            "multi_challenge": [
              {
                "attributes": null,
                "message": "Please enter OTP: ",
                "serial": "OATH00016327",
                "transaction_id": "10254108800156191660",
                "type": "hotp"
              }
            ],
            "serial": "OATH00016327",
            "threadid": 139868461995776,
            "transaction_id": "10254108800156191660",
            "transaction_ids": [
              "10254108800156191660"
            ],
            "type": "hotp"
          },
          "id": 1,
          "jsonrpc": "2.0",
          "result": {
            "status": true,
            "value": false
          },
          "version": "privacyIDEA 3.5.2",
          "versionnumber": "3.5.2",
          "signature": "rsa_sha256_pss:12345"
        }';

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/check')
            ->then()
            ->body($respValidateCheck)
            ->end();
        $this->http->setUp();

        $this->pi->sslVerifyHost = false;
        $this->pi->sslVerifyPeer = false;

        $response = $this->pi->validateCheck(array());
        $this->assertNull($response, "No user or pass added to params.");

        $this->pi->realm = "testRealm";
        $response = $this->pi->validateCheck(['user' => 'testUser', 'pass' => 'testPass'], "1234567890");
        $this->assertNotNull($response, "Response is NULL.");

        $this->assertEquals('Please enter OTP: ', $response->messages, "Message did not match.");
        $this->assertEquals("10254108800156191660", $response->transaction_id, "Transaction id did not match.");
        $this->assertEquals($respValidateCheck, $response->raw, "Cannot to get the raw response in JSON format!");
        $this->assertTrue($response->status, "Status is not true as expected.");
        $this->assertFalse($response->value, "Value is not false as expected.");
        $this->assertEmpty($response->detailAndAttributes, "detailAndAttributes is not empty as expected.");
        $this->assertNull($response->error, "Error is not null as expected.");

        $this->assertEquals("10254108800156191660", $response->multi_challenge[0]->transaction_id, "Transaction id did not match.");
        $this->assertEquals("Please enter OTP: ", $response->multi_challenge[0]->message, "Message did not match.");
        $this->assertEquals("OATH00016327", $response->multi_challenge[0]->serial, "Serial did not match.");
        $this->assertEquals("hotp", $response->multi_challenge[0]->type, "Type did not match.");
        $this->assertNull($response->multi_challenge[0]->attributes, "attributes did not match.");
    }

}
