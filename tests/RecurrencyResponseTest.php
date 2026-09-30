<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Http\ApiResponse;
use WebPag\Http\HttpClient;
use WebPag\Resources\Recurrency;
use WebPag\Responses\Business\Business;
use WebPag\Responses\Pagination\PaginatedCollection;
use WebPag\Responses\Payers\Payer;
use WebPag\Responses\Payments\Payment;
use WebPag\Responses\Payments\Pix;
use WebPag\Responses\Recurrency\Recurrency as RecurrencyResponse;

class RecurrencyResponseTest extends TestCase
{
    /**
     * @return PaginatedCollection|RecurrencyResponse[]
     */
    private function listRecurrencies()
    {
        $body = json_decode(file_get_contents(__DIR__ . '/fixtures/recurrency-list.json'), true);

        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('api/payments/recurrency/list', [])
            ->willReturn(new ApiResponse($body, 200));

        return (new Recurrency($http))->list();
    }

    public function testListReturnsPaginatedRecurrencies()
    {
        $items = $this->listRecurrencies();

        $this->assertInstanceOf(PaginatedCollection::class, $items);
        $this->assertCount(3, $items);
        $this->assertContainsOnlyInstancesOf(RecurrencyResponse::class, $items);
        $this->assertSame(34, $items->total());
        $this->assertSame(3, $items->lastPage());
        $this->assertSame(2, $items->nextPage());
    }

    public function testRecurrencyIsAPayment()
    {
        $item = $this->listRecurrencies()[0];

        $this->assertInstanceOf(Payment::class, $item);
        $this->assertInstanceOf(RecurrencyResponse::class, RecurrencyResponse::fromArray(['id' => 1]));
        $this->assertContainsOnlyInstancesOf(
            RecurrencyResponse::class,
            RecurrencyResponse::fromArrayCollection([['id' => 1], ['id' => 2]])
        );
    }

    public function testCreditCardItemFieldTypes()
    {
        $item = $this->listRecurrencies()[0];

        $this->assertSame(670705, $item->id);
        $this->assertInstanceOf(Business::class, $item->business);
        $this->assertSame(8421, $item->business->id);
        $this->assertSame(245044, $item->payerId);
        $this->assertNull($item->cardId);
        $this->assertInstanceOf(Payer::class, $item->payer);
        $this->assertNull($item->payer->address);
        $this->assertSame(500, $item->amount);
        $this->assertSame(0, $item->amountRefunded);
        $this->assertSame(0.11, $item->feeValue);
        $this->assertSame(0, $item->refundedFee);
        $this->assertSame(1, $item->installments);
        $this->assertFalse($item->isRecurrent);
        $this->assertNull($item->recurrenceCode);
        $this->assertNull($item->frequency);
        $this->assertSame(10, $item->method);
        $this->assertSame('credit_card', $item->methodSlug);
        $this->assertNull($item->boleto);
        $this->assertSame('/api/app/webpag-webhook/6/3/d5389a2c', $item->notificationUrl);
        $this->assertNull($item->pix);
        $this->assertSame(0, $item->installmentsPaid);
        $this->assertTrue($item->spplited);
        $this->assertTrue($item->active);
        $this->assertSame(30, $item->status);
        $this->assertSame('Recusado', $item->statusLabel);
        $this->assertNull($item->startDate);
        $this->assertNull($item->nextDate);
        $this->assertNull($item->nextRecurrenceDate);
        $this->assertSame([], $item->refunds);
        $this->assertSame('2026-08-26 22:15', $item->createdAt);
        $this->assertNull($item->paidAt);
        $this->assertNull($item->cardFlag);
        $this->assertNull($item->cardFlagLabel);
    }

    public function testPixItemHasPix()
    {
        $item = $this->listRecurrencies()[1];

        $this->assertSame(40, $item->method);
        $this->assertSame('Pago', $item->statusLabel);
        $this->assertSame('2026-08-31 09:47', $item->paidAt);
        $this->assertInstanceOf(Pix::class, $item->pix);
        $this->assertSame(430053, $item->pix->id);
        $this->assertSame(671654, $item->pix->paymentId);
        $this->assertSame('LA88javgn9CJmvBBmWP42TJFrNDZYdChjdz', $item->pix->txid);
        $this->assertSame(5.0, $item->pix->amount);
        $this->assertSame(20, $item->pix->status);
    }

    public function testBankSlipItemWithNullInstallments()
    {
        $item = $this->listRecurrencies()[2];

        $this->assertSame(30, $item->method);
        $this->assertSame('bank_slip', $item->methodSlug);
        $this->assertSame(0, $item->amount);
        $this->assertSame(1.99, $item->feeValue);
        $this->assertNull($item->installments);
        $this->assertNull($item->boleto);
    }

    public function testFieldsAbsentInThisEndpointStayNull()
    {
        $item = $this->listRecurrencies()[0];

        $this->assertNull($item->transactions);
        $this->assertNull($item->splits);
        $this->assertNull($item->creditSchedule);

        $data = $item->toArray();
        $this->assertArrayNotHasKey('transactions', $data);
        $this->assertArrayNotHasKey('splits', $data);
        $this->assertArrayNotHasKey('credit_schedule', $data);
        $this->assertSame([], $data['refunds']);
    }

    public function testCardFlagIsInt()
    {
        $item = RecurrencyResponse::fromArray(['card_flag' => 20, 'card_flag_label' => 'Mastercard']);

        $this->assertSame(20, $item->cardFlag);
        $this->assertSame('Mastercard', $item->cardFlagLabel);
    }
}
