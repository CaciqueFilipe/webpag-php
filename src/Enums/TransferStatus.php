<?php

namespace WebPag\Enums;


final class TransferStatus
{
    const REQUESTED = 10;
    const PROCESSING = 20;
    const REJECTED = 30;
    const PAID = 40;
    const PROCESSING_ALT = 50;
    const CANCELLED = 60;
}
