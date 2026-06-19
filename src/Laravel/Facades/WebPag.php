<?php

namespace WebPag\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \WebPag\Resources\Installments installments()
 * @method static \WebPag\Resources\Business business()
 * @method static \WebPag\Resources\PaymentLinks paymentLinks()
 * @method static \WebPag\Resources\Payers payers()
 * @method static \WebPag\Resources\Payments payments()
 * @method static \WebPag\Resources\Recurrency recurrency()
 * @method static \WebPag\Resources\Transfers transfers()
 * @method static \WebPag\Webhooks\WebhookParser webhooks()
 *
 * @see \WebPag\WebPag
 */
class WebPag extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'webpag';
    }
}
