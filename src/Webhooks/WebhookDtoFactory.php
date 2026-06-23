<?php

namespace WebPag\Webhooks;

use WebPag\Contracts\ResponsePayload;
use WebPag\Responses\Payments\Payment;
use WebPag\Responses\Payments\Refund;
use WebPag\Responses\Recurrency\Recurrency;
use WebPag\Responses\Transfers\Transfer;

/**
 * Classe auxiliar para criar o DTO correto com base no tipo de evento.
 */
class WebhookDtoFactory
{
    /**
     * @param string $type
     * @param array<string, mixed> $payload
     * @return ResponsePayload
     */
    public static function create(string $type, array $payload): ResponsePayload
    {
        switch ($type) {
            case WebhookEvent::TYPE_PAYMENT:
                return Payment::fromArray($payload);
            case WebhookEvent::TYPE_TRANSFER:
                return Transfer::fromArray($payload);
            case WebhookEvent::TYPE_REFUND:
                return Refund::fromArray($payload);
            case WebhookEvent::TYPE_RECURRENCY:
                return Recurrency::fromArray($payload);
            case WebhookEvent::TYPE_UNKNOWN:
            default:
                // Para tipos desconhecidos, retornamos um DTO genérico que apenas armazena o array.
                return new class($payload) implements ResponsePayload {
                    private $data;
                    public function __construct(array $data)
                    {
                        $this->data = $data;
                    }
                    public static function fromArray(array $data): self
                    {
                        return new self($data);
                    }
                    public function toArray(): array
                    {
                        return $this->data;
                    }
                };
        }
    }
}