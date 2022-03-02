<?php

//namespace PrivacyIdea\PHPClient;

class PIChallenge
{
    /* @var string Type of the token this challenge is for. */
    public $type = "";

    /* @var string Message for this challenge. */
    public $message = "";

    /* @var string TransactionId to reference this challenge in later requests. */
    public $transactionID = "";

    /* @var string Serial of the token this challenge is for. */
    public $serial = "";

    /* @var string Arbitrary attributes that can be appended to the challenge by the server. */
    public $attributes = "";

    /* @var string JSON format */
    public $webAuthnSignRequest = "";

    /* @var string JSON format */
    public $u2fSignRequest = "";
}