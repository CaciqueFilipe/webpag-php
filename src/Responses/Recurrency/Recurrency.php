<?php

namespace WebPag\Responses\Recurrency;

use WebPag\Responses\Payments\Payment;

/**
 * Item retornado pelos endpoints de recorrência (api/payments/recurrency/*).
 *
 * A API devolve exatamente a mesma estrutura de um pagamento, então todos os
 * campos e tipos são herdados de Payment (pix, boleto, refunds, cardFlag etc.).
 * A classe própria é mantida para distinguir o contexto e permitir campos
 * específicos de recorrência no futuro.
 */
class Recurrency extends Payment
{
}
