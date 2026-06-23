<?php

namespace WebPag\Enums;

/**
 * Representa os status de um pagamento.
 */
class PaymentStatus
{
    const PENDING = 10;
    const PROCESSING = 20;
    const CANCELED = 30;
    const PAID = 40;
    const REFUNDED = 50;
    const CHARGEBACK = 60;
    const FAILED = 70;
    const IN_PROTEST = 80;
    const CONTESTATION = 90;
}
