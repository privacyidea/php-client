<?php

class UtilsForTests
{
    public static function responseBodySuccess()
    {
        return "{\n" . "  \"detail\": {\n" . "    \"message\": \"matching 1 tokens\",\n" . "    \"otplen\": 6,\n" .
            "    \"serial\": \"PISP0001C673\",\n" . "    \"threadid\": 140536383567616,\n" .
            "    \"type\": \"totp\"\n" . "  },\n" . "  \"id\": 1,\n" . "  \"jsonrpc\": \"2.0\",\n" .
            "  \"result\": {\n" . "    \"status\": true,\n" . "    \"value\": true\n" . "  },\n" .
            "  \"time\": 1589276995.4397042,\n" . "  \"version\": \"privacyIDEA 3.2.1\",\n" .
            "  \"versionnumber\": \"3.2.1\",\n" . "  \"signature\": \"rsa_sha256_pss:AAAAAAAAAAA\"\n" . "}";
    }
}