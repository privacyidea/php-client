<?php

//namespace PrivacyIdea\PHPClient;

/**
 * Logging interface. This is used to relay the log messages of the PHP-Client to the logger implementation of the project that uses the client.
 */
interface PILog
{
    public function piDebug($message);

    public function piError($message);
}