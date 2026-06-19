<?php

namespace WebPag\Contracts;

interface RequestPayload
{
    /**
     * @return array<string, mixed>
     */
    public function toArray();
}
