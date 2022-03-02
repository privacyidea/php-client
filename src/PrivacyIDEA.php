<?php

//namespace PrivacyIdea\PHPClient;

const AUTHENTICATORDATA = "authenticatordata";
const CLIENTDATA = "clientdata";
const SIGNATUREDATA = "signaturedata";
const CREDENTIALID = "credentialid";
const USERHANDLE = "userhandle";
const ASSERTIONCLIENTEXTENSIONS = "assertionclientextensions";

/**
 * All the API requests which you need are already done and set to methods in this class.
 * All you have to do is include the SDK-Autoloader to your PHP file
 * and call the methods adding the needed parameters.
 *
 * @author Lukas Matusiewicz <lukas.matusiewicz@netknights.it>
 */
class PrivacyIDEA
{
    /* @var string UserAgent to use in requests made to privacyIDEA. */
    public $userAgent = "";

    /* @var string URL of the privacyIDEA server. */
    public $serverURL = "";

    /* @var string Here is realm of users account. */
    public $realm = "";

    /* @var bool Host verification can be disabled in SSL. */
    public $sslVerifyHost = true;

    /* @var bool Peer verification can be disabled in SSL. */
    public $sslVerifyPeer = true;

    /* @var string Account name for a service account to the privacyIDEA server. This is required to use the /validate/triggerchallenge endpoint. */
    public $serviceAccountName = "";

    /* @var string Password for a service account to the privacyIDEA server. This is required to use the /validate/triggerchallenge endpoint. */
    public $serviceAccountPass = "";

    /* @var string Realm for a service account to the privacyIDEA server. This is required to use the /validate/triggerchallenge endpoint. This is optional. */
    public $serviceAccountRealm = "";

    /* @var object Implementation of the PILog interface. */
    public $logger = null;

    /**
     * PrivacyIDEA constructor.
     * @param $userAgent string the user agent that should be used for the requests made
     * @param $serverURL string the url of the privacyIDEA server
     */
    public function __construct($userAgent, $serverURL)
    {
        $this->userAgent = $userAgent;
        $this->serverURL = $serverURL;
    }

    /**
     * Try to authenticate the user with the /validate/check endpoint.
     *
     * @param $username string
     * @param $pass string this can be the OTP, but also the PIN to trigger a token or PIN+OTP depending on the configuration of the server.
     * @param null $transactionID Optional transaction ID. Used to reference a challenge that was triggered beforehand.
     * @return PIResponse|null null if response was empty or malformed, or parameter missing
     * @throws PIBadRequestException
     */
    public function validateCheck($username, $pass, $transactionID = null)
    {
        assert('string' === gettype($username));
        assert('string' === gettype($pass));

        // Check if parameters are set
        if (!empty($username) || !empty($pass))
        {
            $params["user"] = $username;
            $params["pass"] = $pass;
            if (!empty($transactionID))
            {
                // Add transaction ID in case of challenge response
                $params["transaction_id"] = $transactionID;
            }
            if ($this->realm)
            {
                $params["realm"] = $this->realm;
            }

            $response = $this->sendRequest($params, array(''), 'POST', '/validate/check');

            $ret = PIResponse::fromJSON($response, $this);
            if ($ret == null)
            {
                $this->debugLog("Server did not respond.");
            }
            return $ret;
        }
        else
        {
            $this->debugLog("Missing username or pass for /validate/check.");
        }
        return null;
    }

    /**
     * Trigger all challenges for the given username.
     * This function requires a service account to be set.
     *
     * @param string $username
     * @return PIResponse|null null if response was empty or malformed, or parameter missing
     * @throws PIBadRequestException
     */
    public function triggerChallenge($username)
    {
        assert('string' === gettype($username));

        if ($username)
        {
            $authToken = $this->getAuthToken();
            $header = array("authorization:" . $authToken);

            $params = array("user" => $username);

            if ($this->realm)
            {
                $params["realm"] = $this->realm;
            }

            $response = $this->sendRequest($params, $header, 'POST', '/validate/triggerchallenge');

            return PIResponse::fromJSON($response, $this);
        }
        else
        {
            $this->debugLog("Username missing!");
        }
        return null;
    }

    /**
     * Poll for the status of a transaction (challenge).
     *
     * @param $transactionID string transactionId of the push challenge that was triggered before
     * @return bool true if the Push request has been accepted, false otherwise.
     * @throws PIBadRequestException
     */
    public function pollTransaction($transactionID)
    {
        assert('string' === gettype($transactionID));

        if (!empty($transactionID))
        {
            $params = array("transaction_id" => $transactionID);
            $responseJSON = $this->sendRequest($params, array(''), 'GET', '/validate/polltransaction');
            $response = json_decode($responseJSON, true);
            return $response['result']['value'];
        }
        else
        {
            $this->debugLog("TransactionID missing!");
        }
        return false;
    }

    /**
     * Check if user already has token and if not, enroll a new token
     *
     * @param string $username
     * @param string $genkey
     * @param string $type
     * @param string $description
     * @return mixed Object representing the response of the server or null if parameters are missing
     * @throws PIBadRequestException
     */
    public function enrollToken($username, $genkey, $type, $description = "") // No return type because mixed not allowed yet
    {
        assert('string' === gettype($username));
        assert('string' === gettype($type));
        assert('string' === gettype($genkey));
        if (isset($description))
        {
            assert('string' === gettype($description));
        }

        // Check if parameters contain the required keys
        if (empty($username) || empty($type))
        {
            $this->debugLog("Token enrollment not possible because parameters are not complete");
            return null;
        }

        $params["user"] = $username;
        $params["genkey"] = $genkey;
        $params["type"] = $type;
        $params["description"] = in_array("description", $params) ? $description : "";

        $authToken = $this->getAuthToken();

        // If error occurred in getAuthToken() - return this error in PIResponse object
        $header = array("authorization:" . $authToken);

        // Check if user has token
        $tokenInfo = json_decode($this->sendRequest(array("user" => $params['user']), $header, 'GET', '/token/'));

        if (!empty($tokenInfo->result->value->tokens))
        {
            $this->debugLog("enrollToken: User already has a token.");
            return null;
        }
        else
        {
            // Call /token/init endpoint and return the response
            return json_decode($this->sendRequest($params, $header, 'POST', '/token/init'));
        }
    }

    /**
     * Sends a request to /validate/check with the data required to authenticate with a WebAuthn token.
     *
     * @param string $username
     * @param string $transactionID
     * @param string $webAuthnSignResponse
     * @param string $origin
     * @return PIResponse|null returns null if the response was empty or malformed
     * @throws PIBadRequestException
     */
    public function validateCheckWebAuthn($username, $transactionID, $webAuthnSignResponse, $origin)
    {
        assert('string' === gettype($username));
        assert('string' === gettype($transactionID));
        assert('string' === gettype($webAuthnSignResponse));
        assert('string' === gettype($origin));

        if (!empty($username) || !empty($transactionID))
        {
            // Compose standard validate/check params
            $params["user"] = $username;
            $params["pass"] = "";
            $params["transaction_id"] = $transactionID;

            if ($this->realm)
            {
                $params["realm"] = $this->realm;
            }

            // Additional WebAuthn params
            $tmp = json_decode($webAuthnSignResponse, true);

            $params[CREDENTIALID] = $tmp[CREDENTIALID];
            $params[CLIENTDATA] = $tmp[CLIENTDATA];
            $params[SIGNATUREDATA] = $tmp[SIGNATUREDATA];
            $params[AUTHENTICATORDATA] = $tmp[AUTHENTICATORDATA];

            if (!empty($tmp[USERHANDLE]))
            {
                $params[USERHANDLE] = $tmp[USERHANDLE];
            }
            if (!empty($tmp[ASSERTIONCLIENTEXTENSIONS]))
            {
                $params[ASSERTIONCLIENTEXTENSIONS] = $tmp[ASSERTIONCLIENTEXTENSIONS];
            }

            $header = array("Origin:" . $origin);

            $response = $this->sendRequest($params, $header, 'POST', '/validate/check');

            return PIResponse::fromJSON($response, $this);
        }
        else
        {
            // Handle debug message if $username is empty
            $this->debugLog("validateCheckWebAuthn: parameters are incomplete!");
        }
        return null;
    }

    /**
     * Sends a request to /validate/check with the data required to authenticate with an U2F token.
     *
     * @param string $username
     * @param string $transactionID
     * @param string $u2fSignResponse
     * @return PIResponse|null
     * @throws PIBadRequestException
     */
    public function validateCheckU2F($username, $transactionID, $u2fSignResponse)
    {
        assert('string' === gettype($username));
        assert('string' === gettype($transactionID));
        assert('string' === gettype($u2fSignResponse));

        // Check if required parameters are set
        if (!empty($username) || !empty($transactionID) || !empty($u2fSignResponse))
        {
            // Compose standard validate/check params
            $params["user"] = $username;
            $params["pass"] = "";
            $params["transaction_id"] = $transactionID;

            if ($this->realm)
            {
                $params["realm"] = $this->realm;
            }

            // Additional U2F params from $u2fSignResponse
            $tmp = json_decode($u2fSignResponse, true);
            $params[CLIENTDATA] = $tmp["clientData"];
            $params[SIGNATUREDATA] = $tmp["signatureData"];

            $response = $this->sendRequest($params, array(), 'POST', '/validate/check');

            return PIResponse::fromJSON($response, $this);
        }
        else
        {
            $this->debugLog("validateCheckU2F parameters are incomplete!");
        }
        return null;
    }

    /**
     * Check if service account and pass are set
     * @return bool
     */
    public function serviceAccountAvailable()
    {
        return (!empty($this->serviceAccountName) && !empty($this->serviceAccountPass));
    }

    /**
     * Retrieves an auth token from the server using the service account. An auth token is required for some requests to privacyIDEA.
     *
     * @return string the auth token or empty string if the response did not contain a token or no service account is configured.
     * @throws PIBadRequestException if an error occurs during the request
     */
    public function getAuthToken()
    {
        if (!$this->serviceAccountAvailable())
        {
            $this->errorLog("Cannot retrieve auth token without service account!");
            return "";
        }

        $params = array(
            "username" => $this->serviceAccountName,
            "password" => $this->serviceAccountPass
        );

        if ($this->serviceAccountRealm != null && $this->serviceAccountRealm != "")
        {
            $params["realm"] = $this->serviceAccountRealm;
        }

        $response = json_decode($this->sendRequest($params, array(''), 'POST', '/auth'), true);

        if (!empty($response['result']['value']))
        {
            return @$response['result']['value']['token'] ?: "";
        }

        $this->debugLog("/auth response did not contain a auth token.");
        return "";
    }

    /**
     * Send a request to an endpoint with the specified parameters and headers.
     *
     * @param $params array request parameters
     * @param $headers array headers fields
     * @param $httpMethod string GET or POST
     * @param $endpoint string endpoint of the privacyIDEA API (e.g. /validate/check)
     * @return string returns a string with the response from server
     * @throws PIBadRequestException if an error occurres
     */
    public function sendRequest(array $params, array $headers, $httpMethod, $endpoint)
    {
        assert('array' === gettype($params));
        assert('array' === gettype($headers));
        assert('string' === gettype($httpMethod));
        assert('string' === gettype($endpoint));

        $this->debugLog("Sending " . http_build_query($params, '', ', ') . " to " . $endpoint);

        $completeUrl = $this->serverURL . $endpoint;

        $curlInstance = curl_init();
        curl_setopt($curlInstance, CURLOPT_URL, $completeUrl);
        curl_setopt($curlInstance, CURLOPT_HEADER, true);
        if ($headers)
        {
            curl_setopt($curlInstance, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curlInstance, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlInstance, CURLOPT_USERAGENT, $this->userAgent);
        if ($httpMethod === "POST")
        {
            curl_setopt($curlInstance, CURLOPT_POST, true);
            curl_setopt($curlInstance, CURLOPT_POSTFIELDS, $params);
        }
        elseif ($httpMethod === "GET")
        {
            $paramsStr = '?';
            if (!empty($params))
            {
                foreach ($params as $key => $value)
                {
                    $paramsStr .= $key . "=" . $value . "&";
                }
            }
            curl_setopt($curlInstance, CURLOPT_URL, $completeUrl . $paramsStr);
        }

        // Disable host and/or peer verification for SSL if configured.
        if ($this->sslVerifyHost === true)
        {
            curl_setopt($curlInstance, CURLOPT_SSL_VERIFYHOST, 2);
        }
        else
        {
            curl_setopt($curlInstance, CURLOPT_SSL_VERIFYHOST, 0);
        }

        if ($this->sslVerifyPeer === true)
        {
            curl_setopt($curlInstance, CURLOPT_SSL_VERIFYPEER, 2);
        }
        else
        {
            curl_setopt($curlInstance, CURLOPT_SSL_VERIFYPEER, 0);
        }

        $response = curl_exec($curlInstance);

        if (!$response)
        {
            // Handle error
            $curlErrno = curl_errno($curlInstance);
            $this->errorLog("Bad request: " . curl_error($curlInstance) . " errno: " . $curlErrno);
            throw new PIBadRequestException("Unable to reach the authentication server (" . $curlErrno . ")");
        }

        $headerSize = curl_getinfo($curlInstance, CURLINFO_HEADER_SIZE);
        $ret = substr($response, $headerSize);
        curl_close($curlInstance);

        // Log the response
        if ($endpoint != "/auth" && $this->logger != null)
        {
            $retJson = json_decode($ret, true);
            $this->debugLog($endpoint . " returned " . json_encode($retJson, JSON_PRETTY_PRINT));
        }

        // Return decoded response
        return $ret;
    }

    /**
     * This function relays messages to the PILogger implementation
     * @param $message
     */
    function debugLog($message)
    {
        if ($this->logger != null)
        {
            $this->logger->piDebug("privacyIDEA-PHP-Client: " . $message);
        }
    }

    /**
     * This function relays messages to the PILogger implementation
     * @param $message
     */
    function errorLog($message)
    {
        if ($this->logger != null)
        {
            $this->logger->piError("privacyIDEA-PHP-Client: " . $message);
        }
    }
}