<?php

namespace App\Parsers;

/**
 * Class GatewayService
 * Service class
 * @package App\Services
 */
class RequestParser
{
    private $merchant;
    protected $app;
    protected $view;
    private $apiOperations;
    static $DEFAULT_RECEIPT = 'receipt.phtml';

    public function __construct($app)
    {
        $this->merchant = $app['Merchant'];
        $this->app = $app;
        $this->view = $app['renderer'];
        $this->apiOperations = $app['apiOperations'];
    }

    /**
     * Build the gatewayurl based on transaction type and parameters
     * Possible formats:
     * NVP : http://<Gateway_HOST>/api/nvp/version/<VERSION>
     * 3DSecure: http://<Gateway_HOST>/api/rest/version/<VERSION>/merchant/<MERCHANT_ID>/3DSecureId/<SECURE_ID>
     * DEFAULT: http://<Gateway_HOST>/api/rest/version/<VERSION>/merchant/<MERCHANT_ID>/order/<ORDER_ID>/transaction/<TRANSACTION_ID>
     * @param $requestBody
     * @param $args
     * @return mixed|string
     */
    public function getGatewayUrl($requestBody, $args)
    {
        $protocol = $requestBody["protocol"];
        $gatewayUrl = $this->merchant->GetGatewayUrl();

        $gatewayUrl .= "/version/" . $this->merchant->GetVersion();

        //Gateway url for NVP transactions of the format : http://<Gateway_HOST>/api/nvp/version/<VERSION>
        if ($protocol === 'NVP') {
            $gatewayUrl = str_replace('rest', 'nvp', $gatewayUrl);
            return $gatewayUrl;
        }

        //Append merchant ID: http://<Gateway_HOST>/api/rest/version/<VERSION>/merchant/<MERCHANT_ID>
        $gatewayUrl .= "/merchant/" . $this->merchant->GetMerchantId();

        switch ($requestBody['apiOperation']) {
            case 'CHECK_3DS_ENROLLMENT':
            case 'PROCESS_ACS_RESULT':
                $gatewayUrl .= '/3DSecureId/' . $args['secureId'];
                break;
            case 'PAYMENT_OPTIONS_INQUIRY':
                $gatewayUrl .= '/paymentOptionsInquiry';
                break;
            default:
                $orderId = $args['orderId'] ?: $requestBody['orderId'];
                $transactionId = $args['transactionId'] ?: $requestBody['transactionId'];
                $sessionId = $args['sessionId'];

                $customUri = "";

                if (isset($sessionId)) {
                    $customUri .= "/session/" . $sessionId;
                }
                if ($orderId) {
                    $customUri .= "/order/" . $orderId;
                }
                if ($transactionId) {
                    $customUri .= "/transaction/" . $transactionId;
                }

                $gatewayUrl .= $customUri;
                break;
        }

        return $gatewayUrl;
    }

    public function getBrowserPaymentReturnUrl($requestBody)
    {
        $orderId = $requestBody['orderId'];
        $transactionId = $requestBody['transactionId'];

        $returnUrl = isset($_SERVER['HTTPS']) ? 'https://' : 'http://' . $_SERVER['HTTP_HOST'];
        $returnUrl .= "/browserPaymentReceipt/order/" . $orderId . "/transaction/" . $transactionId;

        return $returnUrl;
    }

    private function parseBrowserPaymentResponse($parsedResponse)
    {
        $response = array("orderId" => $parsedResponse['order']['id'], "responseStatus" => $parsedResponse['result'],  "orderAmount" => $parsedResponse['order']['amount'], "currency" => $parsedResponse['order']['currency']);
        return $response;
    }

    private function parseHostedPaymentResponse($parsedResponse)
    {
        $response = array("orderId" => $parsedResponse['id'], "responseStatus" => $parsedResponse['result'], "orderDescription" => $parsedResponse['description'], "orderAmount" => $parsedResponse['amount'], "currency" => $parsedResponse['currency']);
        return $response;
    }

    private function getApiOperation($request)
    {
        $path = ltrim($request->getUri()->getPath(), '/');
        return $this->apiOperations[$path];
    }

    public function formRequestBody($requestBody)
    {
        $hostedSession = $requestBody["hostedSession"];
        $apiOperation = $requestBody['apiOperation'];

        //Requests using a hosted session ID
        if ($hostedSession) {
            $requestData = [apiOperation => $apiOperation,
                order => [
                    "amount" => $requestBody["amount"],
                    "currency" => $requestBody["currency"]
                ],
                session => [id => $requestBody["sessionId"]]
            ];

            if ($apiOperation === 'CHECK_3DS_ENROLLMENT') {
                $secureArray = [authenticationRedirect =>
                    [
                        pageGenerationMode => 'CUSTOMIZED',
                        responseUrl => 'http://' . $_SERVER['HTTP_HOST'] . '/process3ds'
                    ]
                ];
                $requestData['3DSecure'] = $secureArray;
            }

            $jsonRequest = json_encode($requestData, JSON_PRETTY_PRINT);

        } else {
            $unsetNames = ["orderId", "transactionId", "submit", "method", "version", "_METHOD", "process"];

            foreach ($unsetNames as $fieldName) {
                if (array_key_exists($fieldName, $requestBody))
                    unset($requestBody[$fieldName]);
            }

            $jsonRequest = json_encode($requestBody, JSON_PRETTY_PRINT);
        }
        return $jsonRequest;
    }

    public function parseTransactionParams($request, $args)
    {
        $requestBody = $request->getParsedBody();

        $apiOperation = $this->getApiOperation($request);

        $apiOperation = $apiOperation ?: $requestBody['apiOperation'];

        $gatewayUrl = $this->getGatewayUrl($requestBody, $args);

        return array(requestBody => $requestBody, apiOperation => $apiOperation, gatewayUrl => $gatewayUrl);
    }

     public function buildNVPPayRequest($requestBody)
        {
            $requestData = [
                            "transaction.id" => $requestBody['transactionId'],
                            "order.id" => $requestBody['orderId'],
                            "sourceOfFunds.type" => $requestBody['sourceOfFunds'],
                            "session.id" => $requestBody['sessionId'],
                            "order.currency" => $requestBody['currency'],
                            "apiOperation" => $requestBody['apiOperation'],
                            "order.amount" => $requestBody['amount']
                            ];
            $nvpRequestString = "";

            foreach ($requestData as $fieldName => $fieldValue) {
                // replace underscores in the fieldnames with decimals
                for ($i = 0; $i < strlen($fieldName); $i++) {
                    if ($fieldName[$i] == '_')
                        $fieldName[$i] = '.';
                }
                $nvpRequestString .= $fieldName . "=" . urlencode($fieldValue) . "&";
            }

            $nvpRequestString .= "merchant=" . urlencode($this->merchant->GetMerchantId()) . "&";
            $nvpRequestString .= "apiPassword=" . urlencode($this->merchant->GetPassword()) . "&";
            $nvpRequestString .= "apiUsername=" . urlencode($this->merchant->GetApiUsername());

            return $nvpRequestString;

        }
}

?>