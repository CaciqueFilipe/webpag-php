<?php

namespace WebPag\Enums;

final class TransferStatus
{
    public const REQUESTED = 10;
    public const PROCESSING = 20;
    public const REJECTED = 30;
    public const PAID = 40;
    public const PROCESSING_ALT = 50;
    public const CANCELLED = 60;
}
