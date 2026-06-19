<?php

namespace WebPag;

use WebPag\Http\HttpClient;
use WebPag\Resources\Business;
use WebPag\Resources\Installments;
use WebPag\Resources\PaymentLinks;
use WebPag\Resources\Payers;
use WebPag\Resources\Payments;
use WebPag\Resources\Recurrency;
use WebPag\Resources\Transfers;
use WebPag\Webhooks\WebhookParser;

class WebPag
{
    /** @var Installments */
    public $installments;

    /** @var Business */
    public $business;

    /** @var PaymentLinks */
    public $paymentLinks;

    /** @var Payers */
    public $payers;

    /** @var Payments */
    public $payments;

    /** @var Recurrency */
    public $recurrency;

    /** @var Transfers */
    public $transfers;

    /** @var WebhookParser */
    public $webhooks;

    /** @var Configuration */
    private $config;

    /** @var HttpClient */
    private $http;

    /**
     * @param Configuration   $config
     * @param HttpClient|null $httpClient
     */
    public function __construct(Configuration $config, HttpClient $httpClient = null)
    {
        $this->config = $config;
        $this->http = $httpClient !== null ? $httpClient : new HttpClient($config);

        $this->installments = new Installments($this->http);
        $this->business = new Business($this->http);
        $this->paymentLinks = new PaymentLinks($this->http);
        $this->payers = new Payers($this->http);
        $this->payments = new Payments($this->http);
        $this->recurrency = new Recurrency($this->http);
        $this->transfers = new Transfers($this->http);
        $this->webhooks = new WebhookParser();
    }

    /**
     * Factory method para criação rápida do client.
     *
     * @param string      $apiToken
     * @param string|null $baseUrl
     *
     * @return self
     */
    public static function create($apiToken, $baseUrl = null)
    {
        return new self(new Configuration($apiToken, $baseUrl));
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->http;
    }
}
