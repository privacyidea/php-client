<?php

require_once 'SDK-Autoloader.php';

class PIResponse
{
    /**
     * @var string All tokens messages which are sended by PI and can be used in UI to help user interact with service.
     */
    public $messages = "";
    /**
     * @var string Transaction ID which is needed by some PI API requests.
     */
    public $transaction_id = "";
    /**
     * @var string This is the raw PI response in JSON format.
     */
    public $raw = "";
    /**
     * @var array Here are all triggered challenges delivered as object of PIChallenge class.
     */
    public $multi_challenge = array();
    /**
     * @var bool The status indicates if the request was processed correctly by the server.
     */
    public $status = false;
    /**
     * @var bool The value tell us if authentication was successfull.
     */
    public $value = false;
    /**
     * @var array All interessing details about user which can be shown in the UI at the end of the authentication.
     */
    public $detailAndAttributes = array();
    /**
     * @var string PI error messages with error codes will be delivered here.
     */
    public $error;

    /**
     * Prepare a good readable PI response and return it as an object
     * @param $json
     * @param \PrivacyIDEA $privacyIDEA
     * @return \PIResponse|null
     */
    public static function fromJSON($json, PrivacyIDEA $privacyIDEA) // No mixed type declaration possible here
    {
        if ($json == null || $json == "") {
            $privacyIDEA->errorLog("PrivacyIDEA - PIResponse: No response from PI.");
            return null;
        }

        // Build an PIResponse object and decode the response from JSON to PHP
        $ret = new PIResponse();
        $map = json_decode($json, true);

        // If wrong response format - throw error
        if ($map == null) {
            $privacyIDEA->errorLog("PrivacyIDEA - PIResponse: Response from PI was in wrong format. JSON expected.");
            return null;
        }

        // Prepare raw JSON Response if needed
        $ret->raw = $json;

        // Possibility to show an error if no value
        if (!isset($map['result']['value'])) {
            $ret->error = $map['result']['error']['message'];
            return $ret;
        }

        // Set information from PI response to property
        if (isset($map['detail']['messages'])) {
            $ret->messages = implode(", ", array_unique($map['detail']['messages'])) ?: "";
        }
        if (isset($map['detail']['transaction_id'])) {
            $ret->transaction_id = $map['detail']['transaction_id'];
        }

        $ret->status = $map['result']['status'] ?: false;
        $ret->value = $map['result']['value'] ?: false;

        // Prepare attributes and detail
        if (!empty($map['detail']['user'])) {
            $attributes = $map['detail']['user'];
            $detail = $map['detail'];

            if (isset($attributes['username'])) {
                $attributes['realm'] = $map['detail']['user-realm'] ?: "";
                $attributes['resolver'] = $map['detail']['user-resolver'] ?: "";
            }
            $ret->detailAndAttributes = array("detail" => $detail, "attributes" => $attributes);
        }

        // Set all challenges to objects and set it all to one array
        if (isset($map['detail']['multi_challenge'])) {
            $mc = $map['detail']['multi_challenge'];
            foreach ($mc as $challenge) {
                $tmp = new PIChallenge();
                $tmp->transaction_id = $challenge['transaction_id'];
                $tmp->message = $challenge['message'];
                $tmp->serial = $challenge['serial'];
                $tmp->type = $challenge['type'];
                $tmp->attributes = $challenge['attributes'];
                array_push($ret->multi_challenge, $tmp);
            }
        }
        return $ret;
    }

    /**
     * Get array with all triggered token types
     * @return array
     */
    public function triggeredTokenTypes(): array
    {
        $ret = array();
        foreach ($this->multi_challenge as $challenge) {

            array_push($ret, $challenge->type);
        }
        return array_unique($ret);
    }

    /**
     * Get OTP message if OTP token(s) triggered
     * @return array
     */
    public function otpMessage(): array
    {
        $ret = array();
        foreach ($this->multi_challenge as $challenge) {
            if ($challenge['type'] !== "push" || $challenge['type'] !== "webauthn") {
                array_push($ret, $challenge['message']);
            }
        }
        return array_unique($ret);
    }

    /**
     * Get push message if push token triggered
     * @return string
     */
    public function pushMessage(): string
    {
        foreach ($this->multi_challenge as $challenge) {
            if ($challenge['type'] === "push") {
                return $challenge['message'];
            }
        }
        return false;
    }

    /**
     * Check if push token is available
     * @return bool
     */
    public function pushAvailability(): bool
    {
        foreach ($this->multi_challenge as $challenge) {
            if ($challenge['type'] === "push") {
                return true;
            }
        }
        return false;
    }
}