<?php

/**
 * All the API requests which you need are already done and set to methods in this class.
 * All you have to do is include the SDK-Autoloader to your PHP file
 * and call the methods adding the needed parameters.
 *
 * @author Lukas Matusiewicz <lukas.matusiewicz@netknights.it>
 */

require_once('SDK-Autoloader.php');

class PrivacyIDEA
{
    /**
     * @var string Plugins name which must to be verified in privacyIDEA.
     */
    public $userAgent = "";
    /**
     * @var string This is the URL to your privacyIDEA server.
     */
    public $serverURL = "";
    /**
     * @var string Here is realm of users account.
     */
    public $realm = "";
    /**
     * @var bool You can decide if you want to verify your ssl certificate.
     */
    public $sslVerifyHost = true;
    /**
     * @var bool You can decide if you want to verify your ssl certificate.
     */
    public $sslVerifyPeer = true;
    /**
     * @var string Username to your service account. You need it to get auth token which is needed by some PI API requests.
     */
    public $serviceAccountName = "";
    /**
     * @var string Password to your service account. You need it to get auth token which is needed by some PI API requests.
     */
    public $serviceAccountPass = "";
    /**
     * @var string If needed you can add it too.
     */
    public $serviceAccountRealm = "";
    /**
     * @var bool You can disable the log function by setting this variable to true.
     */
    public $disableLog = false;
    /**
     * @var object This object will deliver PI debug and error messages to your plugin so you can log it wherever you want.
     */
    public $logger = null;

    /**
     * PrivacyIDEA constructor.
     * @param $userAgent string the user agent that should set in the http header
     * @param $serverURL string the url of the privacyIDEA server
     */
    public function __construct($userAgent, $serverURL)
    {
        $this->userAgent = $userAgent;
        $this->serverURL = $serverURL;
    }

    /**
     * This function collect the debug messages and send it to PILog.php
     * @param $message
     */
    function debugLog($message)
    {
        if (!$this->disableLog && $this->logger != null) {
            $this->logger->pi_debug($message);
        }
    }
    /**
     * This function collect the error messages and send it to PILog.php
     * @param $message
     */
    function errorLog($message)
    {
        if (!$this->disableLog && $this->logger != null) {
            $this->logger->pi_error($message);
        }
    }

    /**
     * Handle validateCheck using user's username, password and if challenge response - transaction_id.
     *
     * @param $params array 'user' and 'pass' keys are required and optionally 'realm' if set.
     * @param null $transaction_id optional transaction id. Used to reference a challenge that was triggered beforehand.
     * @return \PIResponse|null This method returns an PIResponse object which contains all the useful information from the PI server. In case of error returns null.
     */
    public function validateCheck($params, $transaction_id = null)
    {
        //Check if parameters are set
        if (!empty($params['user']) || !empty($params['pass'])) {

            if ($transaction_id) {
                //Add transaction ID in case of challenge response
                $params["transaction_id"] = $transaction_id;
            }
            if ($this->realm) {
                $params["realm"] = $this->realm;
            }

            //Call send_request function to handle an API Request using $parameters and return it.
            $response = $this->sendRequest($params, array(''), 'POST', '/validate/check');

            //Return the response from /validate/check as PIResponse object
            $ret = PIResponse::fromJSON($response, $this);
            if ($ret == null) {
                $this->debugLog("privacyIDEA - Validate Check: no response from PI-server");
            }
            return $ret;
        } else {
            //Handle error if $username is empty
            $this->debugLog("privacyIDEA - Validate Check: params incomplete!");
        }
        return null;
    }

    /**
     * Trigger all challenges for the given username.
     * This function requires a service account to be set.
     *
     * @param $username
     * @return \PIResponse|null This method returns an PIResponse object which contains all the useful information from the PI server.
     */
    public function triggerChallenge($username)
    {
        if ($username) {
            $authToken = $this->getAuthToken();

            // Set header to: "'authorization' : auth token" and set username as parameter
            $header = array("authorization:" . $authToken);
            $parameter = array("user" => $username);

            //Call /validate/triggerchallenge with username as paramter and return it.
            $response = $this->sendRequest($parameter, $header, 'POST', '/validate/triggerchallenge');
            //Return the response from /validate/triggerchallenge as PIResponse object
            $ret = PIResponse::fromJSON($response, $this);

            if ($ret == null) {
                $this->debugLog("privacyIDEA - Trigger Challenge: no response from PI-server");
            }
            return $ret;

        } else {
            //Handle error if empty $username
            $this->debugLog("privacyIDEA - Trigger Challenge: no username");
        }
        return null;
    }

    /**
     * Call /validate/polltransaction using transaction_id
     *
     * @param $transaction_id string An unique ID which is needed by some API requests.
     * @return bool Returns true if PUSH is accepted, false otherwise.
     */
    public function pollTransaction($transaction_id): bool
    {
        if (!empty($transaction_id)) {
            $params = array("transaction_id" => $transaction_id);
            // Call /validate/polltransaction using transaction_id and decode it from JSON
            $responseJSON = $this->sendRequest($params, array(''), 'GET', '/validate/polltransaction');
            $response = json_decode($responseJSON, true);
            //Return the response from /validate/polltransaction
            return $response['result']['value'];

        } else {
            //Handle error if $transaction_id is empty
            $this->debugLog("privacyIDEA - Poll Transaction: No transaction_id");
        }
        return false;
    }

    /**
     * Check if user already has token
     * Enroll a new token
     *
     * @param $params array as parameters you need to set: user, genkey, type, description.
     * @return mixed
     */
    public function enrollToken($params) // No return type because mixed not allowed yet
    {
        // Check if parameters contain the required keys
        if (empty($params["user"]) ||
            empty($params["genkey"]) ||
            empty($params["type"])) {
            $this->debugLog("privacyIDEA - Enroll Token: Token enrollment not possible because params are not complete");
            return array();
        }

        $authToken = $this->getAuthToken();
        // Set header to: "'authorization' : auth token"
        $header = array("authorization:" . $authToken);

        // Check if user has token
        $tokenInfo = json_decode($this->sendRequest(array("user" => $params['user']), $header, 'GET', '/token/'));

        if (!empty($tokenInfo->result->value->tokens)) {
            $this->debugLog("privacyIDEA - Enroll Token: User already has a token. No need to enroll a new one.");
            return array();

        } else {
            // Call /token/init endpoint and return the PI response
            return json_decode($this->sendRequest($params, $header, 'POST', '/token/init'));
        }
    }

    /**
     * Retrieves an auth token from the server using the service account. The auth token is required to make certain requests to privacyIDEA.
     * If no service account is set or an error occured, this function returns false.
     *
     * @return string|bool the auth token or false.
     */
    public function getAuthToken()
    {
        // Check if service account is available
        if (empty($this->serviceAccountName) || empty($this->serviceAccountPass)) {
            return false;
        }

        // To get auth token from server use API Request: /auth with added service account and service pass
        $params = array(
            "username" => $this->serviceAccountName,
            "password" => $this->serviceAccountPass
        );

        if ($this->serviceAccountRealm != null && $this->serviceAccountRealm != "") {
            $params["realm"] = $this->serviceAccountRealm;
        }

        // Call /auth endpoint and decode the response from JSON to PHP
        $response = json_decode($this->sendRequest($params, array(''), 'POST', '/auth'), true);

        if ($response) {
            // Get auth token from response->result->value->token and return the token
            return $response['result']['value']['token'];

        }
        // If no response return false
        $this->debugLog("privacyIDEA - getAuthToken: No response from PI-Server");
        return false;
    }

    /**
     * Prepare send_request and make curl_init.
     *
     * @param $params array request parameters in an array
     * @param $headers array headers fields in array
     * @param $http_method string
     * @param $endpoint string endpoint of the privacyIDEA API (e.g. /validate/check)
     * @return string returns string with response from server or an empty string if error occurs
     */
    public function sendRequest($params, $headers, $http_method, $endpoint): string
    {
        assert('array' === gettype($params));
        assert('array' === gettype($headers));
        assert('string' === gettype($http_method));
        assert('string' === gettype($endpoint));

        $curl_instance = curl_init();

        // Compose an API Request using privacyIDEA's URL from config and endpoint created in function
        $completeUrl = $this->serverURL . $endpoint;

        curl_setopt($curl_instance, CURLOPT_URL, $completeUrl);
        curl_setopt($curl_instance, CURLOPT_HEADER, true);
        if ($headers) {
            curl_setopt($curl_instance, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl_instance, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_instance, CURLOPT_USERAGENT, $this->userAgent);
        if ($http_method === "POST") {
            curl_setopt($curl_instance, CURLOPT_POST, true);
            curl_setopt($curl_instance, CURLOPT_POSTFIELDS, $params);

        } elseif ($http_method === "GET") {
            $params_str = '?';
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $params_str .= $key . "=" . $value . "&";
                }
            }
            curl_setopt($curl_instance, CURLOPT_URL, $completeUrl . $params_str);
        }

        // Check if you schould to verify privacyIDEA's SSL certificate in your config
        // If true - do it, if false - don't verify
        if ($this->sslVerifyHost == false) {
            curl_setopt($curl_instance, CURLOPT_SSL_VERIFYHOST, 0);
        } else {
            curl_setopt($curl_instance, CURLOPT_SSL_VERIFYHOST, 2);
        }
        if ($this->sslVerifyPeer == false) {
            curl_setopt($curl_instance, CURLOPT_SSL_VERIFYPEER, 0);
        } else {
            curl_setopt($curl_instance, CURLOPT_SSL_VERIFYPEER, 2);
        }

        //Store response in the variable
        $response = curl_exec($curl_instance);

        if (!$response) {
            //Handle error if no response and return an empty string
            $this->errorLog("privacyIDEA-SDK: Bad request to PI server. " . curl_error($curl_instance));
            return '';
        }

        $header_size = curl_getinfo($curl_instance, CURLINFO_HEADER_SIZE);

        curl_close($curl_instance);

        //Return decoded response from API Request
        return substr($response, $header_size);
    }
}