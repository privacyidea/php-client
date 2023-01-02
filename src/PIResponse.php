<?php

//namespace PrivacyIdea\PHPClient;

class PIResponse
{
    /* @var string Combined messages of all triggered token. */
    public $messages = "";

    /* @var string Message from the response. Should be shown to the user. */
    public $message = "";

    /* @var string TransactionID is used to reference the challenges contained in this response in later requests. */
    public $transactionID = "";

    /* @var string Preferred mode in which client should work after triggering challenges. */
    public $preferredClientMode = "";

    /* @var string Raw response in JSON format. */
    public $raw = "";

    /* @var array Array of PIChallenge objects representing triggered token challenges. */
    public $multiChallenge = array();

    /* @var bool Status indicates if the request was processed successfully by the server. */
    public $status = false;

    /* @var bool Value is true if the authentication was successful. */
    public $value = false;

    /* @var string Authentication Status */
    public $authenticationStatus = "";

    /* @var array Additional attributes of the user that can be sent by the server. */
    public $detailAndAttributes = array();

    /* @var string If an error occurred, the error code will be set. */
    public $errorCode;

    /* @var string If an error occurred, the error message will be set. */
    public $errorMessage;

    /**
     * Create a PIResponse object from the json response of the server.
     *
     * @param $json
     * @param PrivacyIDEA $privacyIDEA
     * @return PIResponse|null returns null if the response of the server is empty or malformed
     */
    public static function fromJSON($json, PrivacyIDEA $privacyIDEA)
    {
        assert('string' === gettype($json));

        if ($json == null || $json == "")
        {
            $privacyIDEA->errorLog("Response from server is empty.");
            return null;
        }

        $ret = new PIResponse();
        $map = json_decode($json, true);

        if ($map == null)
        {
            $privacyIDEA->errorLog("Response from the server is malformed:\n" . $json);
            return null;
        }

        $ret->raw = $json;

        // If value is not present, an error occurred
        if (!isset($map['result']['value']))
        {
            $ret->errorCode = $map['result']['error']['code'];
            $ret->errorMessage = $map['result']['error']['message'];
            return $ret;
        }

        if (isset($map['detail']['messages']))
        {
            $ret->messages = implode(", ", array_unique($map['detail']['messages'])) ?: "";
        }
        if (isset($map['detail']['message']))
        {
            $ret->message = $map['detail']['message'];
        }
        if (isset($map['detail']['transaction_id']))
        {
            $ret->transactionID = $map['detail']['transaction_id'];
        }
        if (isset($map['detail']['preferred_client_mode']))
        {
            $pref = $map['detail']['preferred_client_mode'];
            if ($pref === "poll")
            {
                $ret->preferredClientMode = "push";
            }
            elseif ($pref === "interactive")
            {
                $ret->preferredClientMode = "otp";
            }
            else
            {
                $ret->preferredClientMode = $map['detail']['preferred_client_mode'];
            }
        }

        // Check that the authentication status is one of the allowed ones
        $r = null;
        if (!empty($map['result']['authentication']))
        {
            $r = $map['result']['authentication'];
        }
        if ($r === AuthenticationStatus::CHALLENGE)
        {
            $ret->authenticationStatus = AuthenticationStatus::CHALLENGE;
        }
        elseif ($r === AuthenticationStatus::ACCEPT)
        {
            $ret->authenticationStatus = AuthenticationStatus::ACCEPT;
        }
        elseif ($r === AuthenticationStatus::REJECT)
        {
            $ret->authenticationStatus = AuthenticationStatus::REJECT;
        }
        else
        {
            $privacyIDEA->debugLog("Unknown authentication status");
            $ret->authenticationStatus = AuthenticationStatus::NONE;
        }
        $ret->status = $map['result']['status'] ?: false;
        $ret->value = $map['result']['value'] ?: false;

        // Attributes and detail
        if (!empty($map['detail']['user']))
        {
            $attributes = $map['detail']['user'];
            $detail = $map['detail'];

            if (isset($attributes['username']))
            {
                $attributes['realm'] = $map['detail']['user-realm'] ?: "";
                $attributes['resolver'] = $map['detail']['user-resolver'] ?: "";
            }
            $ret->detailAndAttributes = array("detail" => $detail, "attributes" => $attributes);
        }

        // Add any challenges to multiChallenge
        if (isset($map['detail']['multi_challenge']))
        {
            $mc = $map['detail']['multi_challenge'];
            foreach ($mc as $challenge)
            {
                $tmp = new PIChallenge();
                $tmp->transactionID = $challenge['transaction_id'];
                $tmp->message = $challenge['message'];
                $tmp->serial = $challenge['serial'];
                $tmp->type = $challenge['type'];
                if (isset($challenge['attributes']))
                {
                    $tmp->attributes = $challenge['attributes'];
                }

                if ($tmp->type === "webauthn")
                {
                    $t = $challenge['attributes']['webAuthnSignRequest'];
                    $tmp->webAuthnSignRequest = json_encode($t);
                }

                if ($tmp->type === "u2f")
                {
                    $t = $challenge['attributes']['u2fSignRequest'];
                    $tmp->u2fSignRequest = json_encode($t);
                }

                $ret->multiChallenge[] = $tmp;
            }
        }
        return $ret;
    }

    /**
     * Get an array with all triggered token types.
     * @return array
     */
    public function triggeredTokenTypes()
    {
        $ret = array();
        foreach ($this->multiChallenge as $challenge)
        {
            $ret[] = $challenge->type;
        }
        return array_unique($ret);
    }

    /**
     * Get the message of any token that is not Push or WebAuthn. Those are OTP token requiring an input field.
     * @return string
     */
    public function otpMessage()
    {
        foreach ($this->multiChallenge as $challenge)
        {
            if ($challenge->type !== "push" && $challenge->type !== "webauthn" && $challenge->type !== "u2f")
            {
                return $challenge->message;
            }
        }
        return "";
    }

    /**
     * Get the Push token message if any were triggered.
     * @return string
     */
    public function pushMessage()
    {
        foreach ($this->multiChallenge as $challenge)
        {
            if ($challenge->type === "push")
            {
                return $challenge->message;
            }
        }
        return "";
    }

    /**
     * Get the WebAuthn token message if any were triggered.
     * @return string
     */
    public function webauthnMessage()
    {
        foreach ($this->multiChallenge as $challenge)
        {
            if ($challenge->type === "webauthn")
            {
                return $challenge->message;
            }
        }
        return "";
    }

    /**
     * Get the WebAuthnSignRequest for any triggered WebAuthn token. If none were triggered, this returns an empty string.
     * @return string WebAuthnSignRequest or empty string
     */
    public function webAuthnSignRequest()
    {
        $arr = [];
        $webauthn = "";
        foreach ($this->multiChallenge as $challenge)
        {
            if ($challenge->type === "webauthn")
            {
                $t = json_decode($challenge->webAuthnSignRequest);
                if (empty($webauthn))
                {
                    $webauthn = $t;
                }
                $arr[] = $challenge->attributes['webAuthnSignRequest']['allowCredentials'][0];
            }
        }
        if (empty($webauthn))
        {
            return "";
        }
        else
        {
            $webauthn->allowCredentials = $arr;
            return json_encode($webauthn);
        }
    }

    /**
     * Get the U2FSignRequest for any triggered U2F token. If none were triggered, this returns an empty string.
     * @return string U2FSignRequest or empty string
     */
    public function u2fSignRequest()
    {
        $ret = "";
        foreach ($this->multiChallenge as $challenge)
        {
            if ($challenge->type === "u2f")
            {
                $ret = $challenge->u2fSignRequest;
                break;
            }
        }
        return $ret;
    }

    /**
     * Get the WebAuthn token message if any were triggered.
     * @return string
     */
    public function u2fMessage()
    {
        foreach ($this->multiChallenge as $challenge)
        {
            if ($challenge->type === "u2f")
            {
                return $challenge->message;
            }
        }
        return "";
    }
}
