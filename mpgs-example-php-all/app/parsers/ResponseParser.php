<?php

namespace App\Parsers;

class ResponseParser
{

    private $merchant;
    protected $app;
    protected $view;
    static $DEFAULT_RECEIPT = 'receipt.phtml';

    public function __construct($app)
    {
        $this->merchant = $app['Merchant'];
        $this->app = $app;
        $this->view = $app['renderer'];
    }

    public function parseBrowserPaymentResponse($parsedResponse)
    {
        $response = array("orderId" => $parsedResponse['order']['id'], "responseStatus" => $parsedResponse['result'], "orderAmount" => $parsedResponse['order']['amount'], "currency" => $parsedResponse['order']['currency']);
        return $response;
    }

    public function parseHostedPaymentResponse($parsedResponse)
    {
        $response = array("orderId" => $parsedResponse['id'], "responseStatus" => $parsedResponse['result'], "orderDescription" => $parsedResponse['description'], "orderAmount" => $parsedResponse['amount'], "currency" => $parsedResponse['currency']);
        return $response;
    }




}