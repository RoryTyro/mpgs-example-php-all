<?php

namespace App\Models;

/**
 * Class Merchant
 * Merchant object is instantiated using DI (dependencies.php) with the settings in $configArray (settings.php)
 * Holds the Merchant Credentials, URLS used across most of the Transactions that require Merchant Info
 *
 * @package App\Models
 */
class Merchant
{
    private $gatewayBaseUrl = "";
    private $gatewayUrl = "";
    private $debug = FALSE;
    private $version = "";
    private $currency = "";
    private $merchantId = "";
    private $password = "";
    private $apiUsername = "";
    private $sessionJsUrl = "";
    private $checkoutJsUrl = "";
    private $checkoutSessionUrl = "";
    private $pkiBaseUrl = "";
    private $certificatePath = "";
    private $certificateAuth = FALSE;
    //NOTE: THESE VALUES ARE SET FOR PRODUCTION ENV, for DEVELOPMENT mode settings - follow the authentication section in the README guide.
    private $certificateVerifyPeer = TRUE;
    private $certificateVerifyHost = 1;

    /**
     * Merchant constructor.
     * @param $configArray
     */
    function __construct($configArray)
    {
        if (array_key_exists("gatewayBaseUrl", $configArray))
            $this->gatewayBaseUrl = $configArray["gatewayBaseUrl"];

        if (array_key_exists("pkiBaseUrl", $configArray))
            $this->pkiBaseUrl = $configArray["pkiBaseUrl"];

        if (array_key_exists("hostedSessionUrl", $configArray))
            $this->hostedSessionUrl = $configArray["hostedSessionUrl"];

        if (array_key_exists("gatewayUrl", $configArray))
            $this->gatewayUrl = $configArray["gatewayUrl"];

        if (array_key_exists("debug", $configArray))
            $this->debug = $configArray["debug"];

        if (array_key_exists("version", $configArray))
            $this->version = $configArray["version"];

        if (array_key_exists("currency", $configArray))
            $this->currency = $configArray["currency"];

        if (array_key_exists("merchantId", $configArray))
            $this->merchantId = $configArray["merchantId"];

        if (array_key_exists("password", $configArray))
            $this->password = $configArray["password"];

        if (array_key_exists("apiUsername", $configArray))
            $this->apiUsername = $configArray["apiUsername"];

        $this->sessionJsUrl = $this->hostedSessionUrl . '/version/' . $this->version . '/merchant/' . $this->merchantId . '/session.js';
        $this->checkoutSessionUrl = $this->gatewayUrl . '/version/' . $this->version . '/merchant/' . $this->merchantId . '/session';
        $this->checkoutJsUrl = $this->gatewayBaseUrl . '/checkout/version/' . $this->version . '/checkout.js';

        if (array_key_exists("certificatePath", $configArray) && !empty($configArray["certificatePath"])) {
            $this->certificatePath = $configArray["certificatePath"];

            $this->certificateAuth = true;

            if (array_key_exists("verifyPeer", $configArray))
                $this->certificateVerifyPeer = $configArray["verifyPeer"];

            if (array_key_exists("verifyHost", $configArray))
                $this->certificateVerifyHost = $configArray["verifyHost"];

            //Set the gateway-url back to SSL URL if Certificate Auth is being used
            $this->gatewayUrl = $this->pkiBaseUrl . '/api/rest';
        }
    }

    public function GetGatewayUrl()
    {
        return $this->gatewayUrl;
    }

    public function GetGatewayBaseUrl()
    {
        return $this->gatewayBaseUrl;
    }

    public function GetPKIBaseUrl()
    {
        return $this->pkiBaseUrl;
    }

    public function GetHostedSessionUrl()
    {
        return $this->hostedSessionUrl;
    }

    public function GetDebug()
    {
        return $this->debug;
    }

    public function GetVersion()
    {
        return $this->version;
    }

    public function GetCurrency()
    {
        return $this->currency;
    }

    public function GetMerchantId()
    {
        return $this->merchantId;
    }

    public function GetPassword()
    {
        return $this->password;
    }

    public function GetApiUsername()
    {
        return $this->apiUsername;
    }

    public function GetSessionJsUrl()
    {
        return $this->sessionJsUrl;
    }

    public function GetCheckoutJSUrl()
    {
        return $this->checkoutJsUrl;
    }

    public function GetCheckoutSessionUrl()
    {
        return $this->checkoutSessionUrl;
    }

    public function GetCertificatePath()
    {
        return $this->certificatePath;
    }

    public function GetCertificateAuth()
    {
        return $this->certificateAuth;
    }

    public function isCertificateVerifyPeer()
    {
        return $this->certificateVerifyPeer;
    }

    public function GetCertificateVerifyHost()
    {
        return $this->certificateVerifyHost;
    }

    public function isCertificateAuth()
    {
        return $this->certificateAuth;
    }
}

?>