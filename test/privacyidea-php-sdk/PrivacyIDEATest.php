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

    public function testTriggerChallenge()
    {
        $respAuthToken = '{
         "id": 1,
         "jsonrpc": "2.0",
         "result": {
             "status": true,
             "value": {
                 "token": "eyJhbGciOiJIUz....jdpn9kIjuGRnGejmbFbM"
             }
         },
         "version": "privacyIDEA unknown"
        }';

        $respTriggerChallenge = '{
           "detail":{
              "attributes":null,
              "messages":[
                 "Please confirm the authentication on your mobile device!"
              ],
              "multi_challenge":[
                 {
                    "attributes":null,
                    "message":"please enter otp: ",
                    "serial":"OATH00016327",
                    "transaction_id":"08282050332563531714",
                    "type":"hotp"
                 }
              ],
              "serial":"TOTP0002A944",
              "transaction_id":"08282050332563531714",
              "type":"totp"
           },
           "result":{
              "status":true,
              "value":1
           },
           "version":"privacyIDEA 3.5.2",
           "versionnumber":"3.5.2",
           "signature":"rsa_sha256_pss:12345"
        }';

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/auth')
            ->then()
            ->body($respAuthToken)
            ->end();
        $this->http->setUp();

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/triggerchallenge')
            ->then()
            ->body(null)
            ->end();
        $this->http->setUp();

        $response = $this->pi->triggerChallenge("testUser");
        $this->assertNull($response, "Response is not NULL.");

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/validate/triggerchallenge')
            ->then()
            ->body($respTriggerChallenge)
            ->end();
        $this->http->setUp();

        $response = $this->pi->triggerChallenge("");
        $this->assertNull($response, "Response not NULL even if the username not given.");

        $response = $this->pi->triggerChallenge("testUser");
        $this->assertNotNull($response, "Response is NULL.");

        $this->assertEquals("Please confirm the authentication on your mobile device!", $response->messages, "Message did not match.");
        $this->assertEquals("08282050332563531714", $response->transaction_id, "Transaction id did not match.");
        $this->assertEquals($respTriggerChallenge, $response->raw, "Cannot to get the raw response in JSON format!");
        $this->assertTrue($response->status, "Status is not true as expected.");
        $this->assertEquals("1", $response->value, "Value is not false as expected.");
        $this->assertEmpty($response->detailAndAttributes, "detailAndAttributes is not empty as expected.");
        $this->assertNull($response->error, "Error is not null as expected.");

        $this->assertEquals("08282050332563531714", $response->multi_challenge[0]->transaction_id, "Transaction id did not match.");
        $this->assertEquals("please enter otp: ", $response->multi_challenge[0]->message, "Message did not match.");
        $this->assertEquals("OATH00016327", $response->multi_challenge[0]->serial, "Serial did not match.");
        $this->assertEquals("hotp", $response->multi_challenge[0]->type, "Type did not match.");
        $this->assertNull($response->multi_challenge[0]->attributes, "attributes did not match.");
    }

    public function testPollTransaction()
    {
        $respPolling = '{
                "id": 1,
          "jsonrpc": "2.0",
          "result": {
                    "status": true,
            "value": true
          },
          "version": "privacyIDEA 3.5.2",
          "versionnumber": "3.5.2",
          "signature": "rsa_sha256_pss:12345"
        }';

        $this->http->mock
            ->when()
            ->methodIs('GET')
            ->pathIs('/validate/polltransaction')
            ->then()
            ->body($respPolling)
            ->end();
        $this->http->setUp();

        $response = $this->pi->pollTransaction("");
        $this->assertNotNull($response, "Response is not NULL without transaction_id given.");

        $response = $this->pi->pollTransaction("1234567890");
        $this->assertNotNull($response, "Response is NULL.");

        $this->assertTrue($response, "Value is not true as expected.");
    }

    public function testEnrollToken()
    {
        // Test case if user already have a token
        $respAuthToken = '{
         "id": 1,
         "jsonrpc": "2.0",
         "result": {
             "status": true,
             "value": {
                 "token": "eyJhbGciOiJIUz....jdpn9kIjuGRnGejmbFbM"
             }
         },
         "version": "privacyIDEA unknown"
        }';

        $respTokenInfo = '{
           "id":1,
           "jsonrpc":"2.0",
           "result":{
              "status":true,
              "value":{
                 "count":3,
                 "current":1,
                 "tokens":[
                    {
                       "active":true,
                       "count":37,
                       "info":{
                          "count_auth":"126",
                          "tokenkind":"software"
                       },
                       "locked":false,
                       "realms":[
                          "testRealm"
                       ],
                       "resolver":"testResolver",
                       "revoked":false
                    }
                 ]
              }
           },
           "version":"privacyIDEA 3.5.2",
           "versionnumber":"3.5.2",
           "signature":"rsa_sha256_pss:12345"
        }';

        $respTokenInit = '{
           "detail":{
              "googleurl":{
                 "description":"URL for google Authenticator",
                 "img":"data:image/png;base64,iVBORw0",
                 "value":"otpauth://totp/TOTP0002A944?secret=Y5D5IM4H274ZI6NRO347QGQ4NPTIOHKL&period=30&digits=6&issuer=privacyIDEA"
              },
              "oathurl":{
                 "description":"URL for OATH token",
                 "img":"data:image/png;base64,iVBORw0",
                 "value":"oathtoken:///addToken?name=TOTP0002A944&lockdown=true&key=c747d43387d7f99479b176f9f81a1c6be6871d4b&timeBased=true"
              },
              "otpkey":{
                 "description":"OTP seed",
                 "img":"data:image/png;base64,iVBORw0",
                 "value":"seed://c747d43387d7f99479b176f9f81a1c6be6871d4b",
                 "value_b32":"Y5D5IM4H274ZI6NRO347QGQ4NPTIOHKL"
              },
              "rollout_state":"",
              "serial":"TOTP0002A944",
              "threadid":140286414018304
           },
           "id":1,
           "jsonrpc":"2.0",
           "result":{
              "status":true,
              "value":true
           },
           "version":"privacyIDEA 3.5.2",
           "versionnumber":"3.5.2",
           "signature":"rsa_sha256_pss:12345"
           }';

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/auth')
            ->then()
            ->body($respAuthToken)
            ->end();
        $this->http->setUp();

        $this->http->mock
            ->when()
            ->methodIs('GET')
            ->pathIs('/token/')
            ->then()
            ->body($respTokenInfo)
            ->end();
        $this->http->setUp();

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/token/init')
            ->then()
            ->body($respTokenInit)
            ->end();
        $this->http->setUp();

        $response = $this->pi->enrollToken([
            "user" => "testUser",
            "genkey" => "1",
            "type" => "totp",
            "description" => "Enrolled for Test"]);
        $this->assertNotNull($response, "Response is NULL.");
        $this->assertEmpty($response);

        // Test case if user have no token and we should enroll a new one
        $respTokenInfo = '{
           "id":1,
           "jsonrpc":"2.0",
           "result":{
              "status":true,
              "value":{
                 "count":3,
                 "current":1,
                 "tokens":[]
              }
           },
           "version":"privacyIDEA 3.5.2",
           "versionnumber":"3.5.2",
           "signature":"rsa_sha256_pss:12345"
        }';

        $this->http->mock
            ->when()
            ->methodIs('GET')
            ->pathIs('/token/')
            ->then()
            ->body($respTokenInfo)
            ->end();
        $this->http->setUp();

        $response = $this->pi->enrollToken([
            "user" => "",
            "genkey" => "1",
            "type" => "totp",
            "description" => "Enrolled for Test"]);
        $this->assertEmpty($response, "Without user given enrollToken() should return an empty array.");

        $response = $this->pi->enrollToken([
            "user" => "testUser",
            "genkey" => "",
            "type" => "totp"]);
        $this->assertEmpty($response, "Without genkey given enrollToken() should return an empty array.");

        $response = $this->pi->enrollToken([
            "user" => "testUser",
            "genkey" => "1",
            "type" => ""]);
        $this->assertEmpty($response, "Without type given enrollToken() should return an empty array.");

        $response = $this->pi->enrollToken([
            "user" => "testUser",
            "genkey" => "1",
            "type" => "totp",
            "description" => "Enrolled for Test"]);
        $this->assertNotNull($response, "Response is NULL.");
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('detail', $response, "Object have no detail attribute.");
        $this->assertEquals("data:image/png;base64,iVBORw0", $response->detail->googleurl->img, "Object have no image data.");
    }

    public function testGetAuthToken()
    {
        $respAuthToken = '{
         "id": 1,
         "jsonrpc": "2.0",
         "result": {
             "status": true,
             "value": {
                 "token": "eyJhbGciOiJIUz....jdpn9kIjuGRnGejmbFbM"
             }
         },
         "version": "privacyIDEA unknown"
        }';

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/auth')
            ->then()
            ->body($respAuthToken)
            ->end();
        $this->http->setUp();

        $response = $this->pi->getAuthToken();
        $this->assertFalse($response, "Response is not false.");

        $this->pi->serviceAccountPass = "testPass";
        $this->pi->serviceAccountName = "testAdmin";
        $this->pi->serviceAccountRealm = "testRealm";

        $response = $this->pi->getAuthToken();
        $this->assertEquals('eyJhbGciOiJIUz....jdpn9kIjuGRnGejmbFbM', $response, "Auth token did not match.");

        $this->http->mock
            ->when()
            ->methodIs('POST')
            ->pathIs('/auth')
            ->then()
            ->end();
        $this->http->setUp();

        $response = $this->pi->getAuthToken();
        $this->assertFalse($response);
    }
}
