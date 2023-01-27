<?php

namespace utils;
class Utils
{
    /**
     * @return string
     */
    public static function authToken()
    {
        return "eyJ0eXAiOiJKV1Qi...NoBVmAurqcaaMAsD1S6chGIM";
    }

    /**
     * @return string
     */
    public static function u2fSignRequest()
    {
        return "{\"appId\":\"https:\/\/ttype.u2f\"," .
            "\"challenge\":\"TZKiB0VFFMFsnlz00lF5iCqtQduDJf56AeJAY_BT4NU\"," .
            "\"keyHandle\":\"UUHmZ4BUFCrt7q88MhlQJYu4G5qB9l7ScjRRxA-M35cTH-uHWyMEpxs4WBzbkjlZqzZW1lC-jDdFd2pKDUsNnA\"," .
            "\"version\":\"U2F_V2\"}";
    }

    /**
     * @return string
     */
    public static function u2fSignResponse()
    {
        return "{\"clientData\":\"eyJjaGFsbGVuZ2UiOiJpY2UBc3NlcnRpb24ifQ\"," . "\"errorCode\":0," .
            "\"keyHandle\":\"UUHmZ4BUFCrt7q88MhlQkjlZqzZW1lC-jDdFd2pKDUsNnA\"," .
            "\"signatureData\":\"AQAAAxAwRQIgZwEObruoCRRo738F9up1tdV2M0H1MdP5pkO5Eg\"}";
    }

    /**
     * @return string
     */
    public static function webauthnSignRequest()
    {
        return "{\n" . "            \"allowCredentials\": [\n" . "              {\n" .
            "                \"id\": \"83De8z_CNqogB6aCyKs6dWIqwpOpzVoNaJ74lgcpuYN7l-95QsD3z-qqPADqsFlPwBXCMqEPssq75kqHCMQHDA\",\n" .
            "                \"transports\": [\n" . "                  \"internal\",\n" .
            "                  \"nfc\",\n" . "                  \"ble\",\n" .
            "                  \"usb\"\n" . "                ],\n" .
            "                \"type\": \"public-key\"\n" . "              }\n" .
            "            ],\n" .
            "            \"challenge\": \"dHzSmZnAhxEq0szRWMY4EGg8qgjeBhJDjAPYKWfd2IE\",\n" .
            "            \"rpId\": \"office.netknights.it\",\n" .
            "            \"timeout\": 60000,\n" .
            "            \"userVerification\": \"preferred\"\n" . "          }\n";
    }

    /**
     * @return string
     */
    public static function webauthnSignResponse()
    {
        return "{" . "\"credentialid\":\"X9FrwMfmzj...saw21\"," .
            "\"authenticatordata\":\"xGzvgq0bVGR3WR0A...ZJdA7cBAAAACA\"," .
            "\"clientdata\":\"eyJjaGFsbG...dfhs\"," .
            "\"userhandle\":\"eyJjaGFsadffhs\"," .
            "\"assertionclientextensions\":\"eyJjaGFasdfasdffhs\"," .
            "\"signaturedata\":\"MEUCIQDNrG...43hc\"}";
    }

    /**
     * @return string
     */
    public static function imageData()
    {
        return "data:image/png;base64,iVBdgfgsdfgRK5CYII=";
    }

    /**
     * @return string
     */
    public static function matchingOneTokenResponseBody()
    {
        return "{\n" . "  \"detail\": {\n" . "    \"message\": \"matching 1 tokens\",\n" . "    \"otplen\": 6,\n" .
            "    \"serial\": \"PISP0001C673\",\n" . "    \"threadid\": 140536383567616,\n" .
            "    \"type\": \"totp\"\n" . "  },\n" . "  \"id\": 1,\n" . "  \"jsonrpc\": \"2.0\",\n" .
            "  \"result\": {\n" . "    \"status\": true,\n" . "    \"value\": true\n" . "  },\n" .
            "  \"time\": 1589276995.4397042,\n" . "  \"version\": \"privacyIDEA 3.2.1\",\n" .
            "  \"versionnumber\": \"3.2.1\",\n" . "  \"signature\": \"rsa_sha256_pss:AAAAAAAAAAA\"\n" . "}";
    }

    /**
     * @return string
     */
    public static function postAuthResponseBody()
    {
        return "{\n" . "    \"id\": 1,\n" . "    \"jsonrpc\": \"2.0\",\n" .
            "    \"result\": {\n" . "        \"status\": true,\n" .
            "        \"value\": {\n" . "            \"log_level\": 20,\n" .
            "            \"menus\": [\n" . "                \"components\",\n" .
            "                \"machines\"\n" . "            ],\n" .
            "            \"realm\": \"\",\n" . "            \"rights\": [\n" .
            "                \"policydelete\",\n" .
            "                \"resync\"\n" . "            ],\n" .
            "            \"role\": \"admin\",\n" . "            \"token\": \"" .
            self::authToken() . "\",\n" . "            \"username\": \"admin\",\n" .
            "            \"logout_time\": 120,\n" .
            "            \"default_tokentype\": \"hotp\",\n" .
            "            \"user_details\": false,\n" .
            "            \"subscription_status\": 0\n" . "        }\n" .
            "    },\n" . "    \"time\": 1589446794.8502703,\n" .
            "    \"version\": \"privacyIDEA 3.2.1\",\n" .
            "    \"versionnumber\": \"3.2.1\",\n" .
            "    \"signature\": \"rsa_sha256_pss:\"\n" . "}";
    }

    /**
     * @return string
     */
    public static function tokenInitResponseBody()
    {
        return "{\n" . "    \"detail\": {\n" . "        \"googleurl\": {\n" .
            "            \"description\": \"URL for google Authenticator\",\n" .
            "            \"img\": \"" . self::imageData() . "\",\n" .
            "            \"value\": \"otpauth://hotp/OATH0003A0AA?secret=4DK5JEEQMWY3VES7EWB4M36TAW4YC2YH&counter=1&digits=6&issuer=privacyIDEA\"\n" .
            "        },\n" . "        \"oathurl\": {\n" .
            "            \"description\": \"URL for OATH token\",\n" .
            "            \"img\": \"" . self::imageData() . "\",\n" .
            "            \"value\": \"oathtoken:///addToken?name=OATH0003A0AA&lockdown=true&key=e0d5d4909065b1ba925f2583c66fd305b9816b07\"\n" .
            "        },\n" . "        \"otpkey\": {\n" .
            "            \"description\": \"OTP seed\",\n" .
            "            \"img\": \"" . self::imageData() . "\",\n" .
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
    }

    /**
     * @return string
     */
    public static function getTokenResponseBody()
    {
        return "{\"id\":1," . "\"jsonrpc\":\"2.0\"," . "\"result\":{" . "\"status\":true," . "\"value\":{" .
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
    }

    /**
     * @return string
     */
    public static function triggerPushTokenResponseBody()
    {
        return "{\n" . "  \"detail\": {\n" . "\"preferred_client_mode\":\"poll\"," . "    \"attributes\": null,\n" .
            "    \"message\": \"Bitte geben Sie einen OTP-Wert ein: , Please confirm the authentication on your mobile device!\",\n" .
            "    \"messages\": [\n" . "      \"Bitte geben Sie einen OTP-Wert ein: \",\n" .
            "      \"Please confirm the authentication on your mobile device!\"\n" . "    ],\n" .
            "    \"multi_challenge\": [\n" . "      {\n" . "        \"attributes\": null,\n" .
            "        \"message\": \"Bitte geben Sie einen OTP-Wert ein: \",\n" .
            "        \"serial\": \"OATH00020121\",\n" .
            "        \"transaction_id\": \"02659936574063359702\",\n" . "        \"type\": \"hotp\"\n" .
            "      },\n" . "      {\n" . "        \"attributes\": null,\n" .
            "        \"message\": \"Please confirm the authentication on your mobile device!\",\n" .
            "        \"serial\": \"PIPU0001F75E\",\n" .
            "        \"transaction_id\": \"02659936574063359702\",\n" . "        \"type\": \"push\"\n" .
            "      }\n" . "    ],\n" . "    \"serial\": \"PIPU0001F75E\",\n" .
            "    \"threadid\": 140040525666048,\n" . "    \"transaction_id\": \"02659936574063359702\",\n" .
            "    \"transaction_ids\": [\n" . "      \"02659936574063359702\",\n" .
            "      \"02659936574063359702\"\n" . "    ],\n" . "    \"type\": \"push\"\n" . "  },\n" .
            "  \"id\": 1,\n" . "  \"jsonrpc\": \"2.0\",\n" . "  \"result\": {\n" .
            "    \"status\": true,\n" . "    \"value\": false\n" . "  },\n" .
            "  \"time\": 1589360175.594304,\n" . "  \"version\": \"privacyIDEA 3.2.1\",\n" .
            "  \"versionnumber\": \"3.2.1\",\n" . "  \"signature\": \"rsa_sha256_pss:AAAAAAAAAA\"\n" . "}";
    }

    /**
     * @return string
     */
    public static function pollingResponseBody()
    {
        return '{
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
    }

    /**
     * @return string
     */
    public static function tcSuccessResponseBody()
    {
        return "{\"detail\":{" . "\"preferred_client_mode\":\"interactive\"," .
            "\"image\": \"" . self::imageData() . "\",\n" .
            "\"attributes\":null," . "\"message\":\"BittegebenSieeinenOTP-Wertein:\"," .
            "\"messages\":[\"BittegebenSieeinenOTP-Wertein:\"]," . "\"multi_challenge\":[{" .
            "\"attributes\":null," . "\"message\":\"BittegebenSieeinenOTP-Wertein:\"," .
            "\"serial\":\"TOTP00021198\"," . "\"client_mode\":\"interactive\"," . "\"image\":\"" . self::imageData() . "\"," .
            "\"transaction_id\":\"16734787285577957577\"," . "\"type\":\"totp\"}]," . "\"serial\":\"TOTP00021198\"," .
            "\"threadid\":140050885818112," . "\"transaction_id\":\"16734787285577957577\"," .
            "\"transaction_ids\":[\"16734787285577957577\"]," . "\"type\":\"totp\"}," . "\"id\":1," .
            "\"jsonrpc\":\"2.0\"," . "\"result\":{" . "\"status\":true," . "\"value\":false}," .
            "\"time\":1649666174.5351279," . "\"version\":\"privacyIDEA3.6.3\"," .
            "\"versionnumber\":\"3.6.3\"," .
            "\"signature\":\"rsa_sha256_pss:4b0f0e12c2...89409a2e65c87d27b\"}";
    }

    /**
     * @return string
     */
    public static function errorUserNotFoundResponseBody()
    {
        return "{" . "\"detail\":null," . "\"id\":1," . "\"jsonrpc\":\"2.0\"," . "\"result\":{" . "\"error\":{" .
            "\"code\":904," . "\"message\":\"ERR904: The user can not be found in any resolver in this realm!\"}," .
            "\"status\":false}," . "\"time\":1649752303.65651," . "\"version\":\"privacyIDEA 3.6.3\"," .
            "\"signature\":\"rsa_sha256_pss:1c64db29cad0dc127d6...5ec143ee52a7804ea1dc8e23ab2fc90ac0ac147c0\"}";
    }

    /**
     * @return string
     */
    public static function triggerU2FResponseBody()
    {
        return "{" . "\"detail\":{" . "\"preferred_client_mode\":\"u2f\"," . "\"attributes\":{" . "\"hideResponseInput\":true," .
            "\"image\":\"" . self::imageData() . "\"," . "\"u2fSignRequest\":{" .
            "\"appId\":\"http//ttype.u2f\"," . "\"challenge\":\"TZKiB0VFFMF...tQduDJf56AeJAY_BT4NU\"," .
            "\"keyHandle\":\"UUHmZ4BUFCrt7q88MhlQ...qzZW1lC-jDdFd2pKDUsNnA\"," .
            "\"version\":\"U2F_V2\"}}," .
            "\"message\":\"Please confirm with your U2F token (Yubico U2F EE Serial 61730834)\"," .
            "\"messages\":[\"Please confirm with your U2F token (Yubico U2F EE Serial 61730834)\"]," .
            "\"multi_challenge\":[{" . "\"attributes\":{" . "\"hideResponseInput\":true," .
            "\"image\":\"" . self::imageData() . "\"," . "\"u2fSignRequest\":" .
            self::u2fSignRequest() . "}," .
            "\"message\":\"Please confirm with your U2F token (Yubico U2F EE Serial 61730834)\"," .
            "\"serial\":\"U2F00014651\"," . "\"transaction_id\":\"12399202888279169736\"," .
            "\"type\":\"u2f\"}]," . "\"serial\":\"U2F00014651\"," . "\"threadid\":140050978137856," .
            "\"transaction_id\":\"12399202888279169736\"," .
            "\"transaction_ids\":[\"12399202888279169736\"]," . "\"type\":\"u2f\"}," . "\"id\":1," .
            "\"jsonrpc\":\"2.0\"," . "\"result\":{" . "\"status\":true," . "\"value\":false}," .
            "\"time\":1649769348.7552881," . "\"version\":\"privacyIDEA 3.6.3\"," .
            "\"versionnumber\":\"3.6.3\"," .
            "\"signature\":\"rsa_sha256_pss:3e51d814...dccd5694b8c15943e37e1\"}";
    }

    /**
     * @return string
     */
    public static function triggerWebauthnResponseBody()
    {
        return "{\n" . "  \"detail\": {\n" . "\"preferred_client_mode\":\"webauthn\"," . "    \"attributes\": {\n" . "      \"hideResponseInput\": true,\n" .
            "      \"image\": \"" . self::imageData() . "\",\n" .
            "      \"webAuthnSignRequest\": " . self::webauthnSignRequest() . "\n" . "    },\n" .
            "    \"message\": \"Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)\",\n" .
            "    \"messages\": [\n" .
            "      \"Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)\"\n" . "    ],\n" .
            "    \"multi_challenge\": [\n" . "      {\n" . "        \"attributes\": {\n" .
            "          \"hideResponseInput\": true,\n" .
            "          \"webAuthnSignRequest\": " . self::webauthnSignRequest() . "        },\n" .
            "        \"message\": \"Please confirm with your WebAuthn token (Yubico U2F EE Serial 61730834)\",\n" .
            "        \"serial\": \"WAN00025CE7\",\n" . "          \"image\": \"" . self::imageData() . "\",\n" .
            "        \"transaction_id\": \"16786665691788289392\",\n" .
            "        \"type\": \"webauthn\"\n" . "      }\n" . "    ],\n" . "    \"serial\": \"WAN00025CE7\",\n" .
            "    \"threadid\": 140040275289856,\n" . "    \"transaction_id\": \"16786665691788289392\",\n" .
            "    \"transaction_ids\": [\n" . "      \"16786665691788289392\"\n" . "    ],\n" .
            "    \"type\": \"webauthn\"\n" . "  },\n" . "  \"id\": 1,\n" . "  \"jsonrpc\": \"2.0\",\n" .
            "  \"result\": {\n" . "    \"authentication\": \"CHALLENGE\",\n" . "    \"status\": true,\n" .
            "    \"value\": false\n" . "  },\n" . "  \"time\": 1611916339.8448942\n" . "}";
    }
}