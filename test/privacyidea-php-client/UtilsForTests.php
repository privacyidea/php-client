<?php

class UtilsForTests
{
    /**
     * @return string
     */
    public static function responseBodySuccess()
    {
        return "{\n" . "  \"detail\": {\n" . "    \"message\": \"matching 1 tokens\",\n" . "    \"otplen\": 6,\n" .
            "    \"serial\": \"PISP0001C673\",\n" . "    \"threadid\": 140536383567616,\n" .
            "    \"type\": \"totp\"\n" . "  },\n" . "  \"id\": 1,\n" . "  \"jsonrpc\": \"2.0\",\n" .
            "  \"result\": {\n" . "    \"status\": true,\n" . "    \"value\": true\n" . "  },\n" .
            "  \"time\": 1589276995.4397042,\n" . "  \"version\": \"privacyIDEA 3.2.1\",\n" .
            "  \"versionnumber\": \"3.2.1\",\n" . "  \"signature\": \"rsa_sha256_pss:AAAAAAAAAAA\"\n" . "}";
    }

    /**
     * @param string $authToken
     * @return string
     */
    public static function authToken($authToken)
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
            $authToken . "\",\n" . "            \"username\": \"admin\",\n" .
            "            \"logout_time\": 120,\n" .
            "            \"default_tokentype\": \"hotp\",\n" .
            "            \"user_details\": false,\n" .
            "            \"subscription_status\": 0\n" . "        }\n" .
            "    },\n" . "    \"time\": 1589446794.8502703,\n" .
            "    \"version\": \"privacyIDEA 3.2.1\",\n" .
            "    \"versionnumber\": \"3.2.1\",\n" .
            "    \"signature\": \"rsa_sha256_pss:\"\n" . "}";
    }
}