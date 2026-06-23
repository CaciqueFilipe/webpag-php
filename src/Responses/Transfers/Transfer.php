<?php

namespace WebPag\Responses\Transfers;

use WebPag\Contracts\ResponsePayload;

class Transfer implements ResponsePayload
{
    /** @var int|null */
    public $id;

    /** @var int|null */
    public $amount;

    /** @var int|null */
    public $destinationType;

    /** @var string|null */
    public $destinationTypeName;

    /** @var int|null */
    public $destinationBusinessId;

    /** @var int|null */
    public $serviceFee;

    /** @var int|null */
    public $feeService;

    /** @var int|null */
    public $debitedAmount;

    /** @var string|null */
    public $serviceFeeType;

    /** @var int|null */
    public $transferType;

    /** @var string|null */
    public $transferTypeName;

    /** @var string|null */
    public $businessNotes;

    /** @var string|null */
    public $adminNotes;

    /** @var string|null */
    public $notificationUrl;

    /** @var int|null */
    public $status;

    /** @var string|null */
    public $statusName;

    /** @var string|null */
    public $createdAt;

    /** @var string|null */
    public $finishedAt;

    /** @var string|null */
    public $updatedAt;

    /** @var string|null */
    public $pixKey;

    /** @var int|string|null */
    public $pixKeyType;

    /** @var string|null */
    public $accountDocument;

    /** @var string|null */
    public $accountHolder;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->id = isset($data['id']) ? (int)$data['id'] : null;
        $instance->amount = isset($data['amount']) ? (int)$data['amount'] : null;
        $instance->destinationType = isset($data['destination_type']) ? (int)$data['destination_type'] : null;
        $instance->destinationTypeName = $data['destination_type_name'] ?? null;
        $instance->destinationBusinessId = isset($data['destination_business_id']) ? (int)$data['destination_business_id'] : null;
        $instance->serviceFee = isset($data['service_fee']) ? (int)$data['service_fee'] : null;
        $instance->feeService = isset($data['fee_service']) ? (int)$data['fee_service'] : null;
        $instance->debitedAmount = isset($data['debited_amount']) ? (int)$data['debited_amount'] : null;
        $instance->serviceFeeType = $data['service_fee_type'] ?? null;
        $instance->transferType = isset($data['transfer_type']) ? (int)$data['transfer_type'] : null;
        $instance->transferTypeName = $data['transfer_type_name'] ?? null;
        $instance->businessNotes = $data['business_notes'] ?? null;
        $instance->adminNotes = $data['admin_notes'] ?? null;
        $instance->notificationUrl = $data['notification_url'] ?? null;
        $instance->status = isset($data['status']) ? (int)$data['status'] : null;
        $instance->statusName = $data['status_name'] ?? null;
        $instance->createdAt = $data['created_at'] ?? null;
        $instance->finishedAt = $data['finished_at'] ?? null;
        $instance->updatedAt = $data['updated_at'] ?? null;
        $instance->pixKey = $data['pix_key'] ?? null;
        $instance->pixKeyType = $data['pix_key_type'] ?? null;
        $instance->accountDocument = $data['account_document'] ?? null;
        $instance->accountHolder = $data['account_holder'] ?? null;

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_filter(get_object_vars($this), function ($value) {
            return $value !== null;
        });
    }

    /**
     * Cria uma coleção de instâncias a partir de um array de dados da API.
     *
     * @param array<array<string, mixed>> $collection
     * @return self[]
     */
    public static function fromArrayCollection(array $collection): array
    {
        return array_map(function ($data) {
            return self::fromArray($data);
        }, $collection);
    }
}