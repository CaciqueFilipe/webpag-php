<?php

namespace WebPag\Enums;

final class PaymentMethod
{
    const CREDIT_CARD = 'credit_card';
    const PIX = 'pix';
    const BANK_SLIP = 'bank_slip';
}

final class RecurrencyFrequency
{
    const MONTHLY = 'monthly';
    const BIMONTHLY = 'bimonthly';
    const QUARTERLY = 'quarterly';
    const SEMIANNUAL = 'semiannual';
    const YEARLY = 'yearly';
}

final class PaymentLinkMethod
{
    const CREDIT_CARD = 'credit_card';
    const PIX = 'pix';
    const BANKSLIP = 'bankslip';
}

final class PaymentStatus
{
    const AVAILABLE = 10;
    const PROCESSING = 20;
    const FAILED = 30;
    const PAID = 40;
    const PARTIAL_REFUND = 45;
    const REFUNDED = 50;
    const CANCELLED = 60;
    const CHARGEBACK = 70;
    const IN_DISPUTE = 80;
    const IN_CONTESTATION = 90;
}

final class TransferDestinationType
{
    const MY_BANK_ACCOUNT = 10;
    const OTHER_BANK_ACCOUNT = 30;
}

final class TransferType
{
    const NORMAL = 10;
    const EXPRESS = 20;
}

final class TransferStatus
{
    const REQUESTED = 10;
    const PROCESSING = 20;
    const REJECTED = 30;
    const PAID = 40;
    const PROCESSING_ALT = 50;
    const CANCELLED = 60;
}

final class PixKeyType
{
    const NONE = 0;
    const CPF = 10;
    const CNPJ = 20;
    const EMAIL = 30;
    const PHONE = 40;
    const RANDOM = 50;
}

final class PixKeyTypeString
{
    const CPF = 'cpf';
    const CNPJ = 'cnpj';
    const EMAIL = 'email';
    const PHONE = 'phone';
    const RANDOM = 'random';
}

final class Gender
{
    const MALE = 'M';
    const FEMALE = 'F';
}
