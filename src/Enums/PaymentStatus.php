<?php

namespace WebPag\Enums;

/**
 * Representa os status de um pagamento.
 */
class PaymentStatus
{
    public const PENDING = 10;
    public const PROCESSING = 20;
    public const CANCELED = 30;
    public const PAID = 40;
    public const REFUNDED = 50;
    public const CHARGEBACK = 60;
    public const FAILED = 70;
    public const IN_PROTEST = 80;
    public const CONTESTATION = 90;
}
