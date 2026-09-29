<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Http\ApiResponse;
use WebPag\Http\HttpClient;
use WebPag\Resources\Payments;
use WebPag\Responses\Business\Business;
use WebPag\Responses\Pagination\PaginatedCollection;
use WebPag\Responses\Payers\Payer;
use WebPag\Responses\Payments\CreditSchedule;
use WebPag\Responses\Payments\Payment;
use WebPag\Responses\Payments\Pix;
use WebPag\Responses\Payments\Split;
use WebPag\Responses\Payments\Transaction;

class PaymentResponseTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function loadFixture()
    {
        return json_decode(file_get_contents(__DIR__ . '/fixtures/payments-list.json'), true);
    }

    /**
     * @return PaginatedCollection|Payment[]
     */
    private function listPayments()
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('api/payments', ['page' => 1])
            ->willReturn(new ApiResponse($this->loadFixture(), 200));

        $payments = new Payments($http);

        return $payments->list(['page' => 1]);
    }

    public function testListReturnsPaymentCollection()
    {
        $payments = $this->listPayments();

        $this->assertInstanceOf(PaginatedCollection::class, $payments);
        $this->assertCount(2, $payments);
        $this->assertContainsOnlyInstancesOf(Payment::class, $payments);
    }

    public function testListExposesPaginationFromResponse()
    {
        $payments = $this->listPayments();

        $this->assertSame(1, $payments->currentPage());
        $this->assertSame(3, $payments->lastPage());
        $this->assertSame(15, $payments->perPage());
        $this->assertSame(34, $payments->total());
        $this->assertTrue($payments->hasMorePages());
        $this->assertSame(2, $payments->nextPage());
        $this->assertSame('https://api.webpag.com.br/api/payments?page=2', $payments->getLinks()->next);
    }

    public function testPixPaymentFieldTypes()
    {
        $payment = $this->listPayments()[0];

        $this->assertSame(676426, $payment->id);
        $this->assertInstanceOf(Business::class, $payment->business);
        $this->assertSame(8421, $payment->business->id);
        $this->assertSame('EMPRESA TESTE', $payment->business->name);
        $this->assertSame(245044, $payment->payerId);
        $this->assertNull($payment->cardId);
        $this->assertSame(156, $payment->amount);
        $this->assertSame(0, $payment->amountRefunded);
        $this->assertSame(0.36, $payment->feeValue);
        $this->assertSame(0, $payment->refundedFee);
        $this->assertSame(1, $payment->installments);
        $this->assertFalse($payment->isRecurrent);
        $this->assertNull($payment->recurrenceCode);
        $this->assertNull($payment->frequency);
        $this->assertSame(40, $payment->method);
        $this->assertSame('Pix', $payment->methodLabel);
        $this->assertSame('pix', $payment->methodSlug);
        $this->assertNull($payment->boleto);
        $this->assertNull($payment->notificationUrl);
        $this->assertSame(0, $payment->installmentsPaid);
        $this->assertTrue($payment->spplited);
        $this->assertNull($payment->softDescriptor);
        $this->assertSame('ba19a4a4-b774-11f1-98cf-e6864b1f6b7e', $payment->orderId);
        $this->assertTrue($payment->active);
        $this->assertSame(40, $payment->status);
        $this->assertSame('Pago', $payment->statusLabel);
        $this->assertNull($payment->startDate);
        $this->assertSame([], $payment->refunds);
        $this->assertSame('2026-09-23 14:32', $payment->createdAt);
        $this->assertSame('2026-09-23 14:33', $payment->paidAt);
        $this->assertSame('https://api.webpag.com.br/payments/01M37N6MZ9SSNA213SB1R6BZTB/pdf', $payment->receiptPdfPath);
        $this->assertNull($payment->cardFlag);
        $this->assertNull($payment->cardFlagLabel);
    }

    public function testPayerFieldTypes()
    {
        $payer = $this->listPayments()[0]->payer;

        $this->assertInstanceOf(Payer::class, $payer);
        $this->assertSame(245044, $payer->id);
        $this->assertSame('00000000191', $payer->cpfCnpj);
        $this->assertFalse($payer->isBusiness);
        $this->assertSame('M', $payer->gender);
        $this->assertNull($payer->phoneNumber);
        $this->assertSame('1954-12-11', $payer->birthDate);
        $this->assertNull($payer->useBoleto);
        $this->assertSame(10, $payer->status);
        $this->assertSame('Porto Alegre', $payer->address->city);
        $this->assertSame('408', $payer->address->number);
    }

    public function testPixFieldTypes()
    {
        $pix = $this->listPayments()[0]->pix;

        $this->assertInstanceOf(Pix::class, $pix);
        $this->assertSame(431507, $pix->id);
        $this->assertSame('3233b3a7-0ed4-4526-9bcf-6630dfb219a4', $pix->uuid);
        $this->assertSame(8421, $pix->businessId);
        $this->assertSame(676426, $pix->paymentId);
        $this->assertNull($pix->name);
        $this->assertSame('bdV4p5XIWImMQix4cIgH0kQbFFRxfenUsTc', $pix->txid);
        $this->assertSame(2.55, $pix->amount);
        $this->assertSame('2026-09-23T18:32:19.000000Z', $pix->expirationDate);
        $this->assertSame(20, $pix->status);
    }

    public function testTransactionsSplitsAndCreditSchedule()
    {
        $payment = $this->listPayments()[0];

        $this->assertCount(1, $payment->transactions);
        $transaction = $payment->transactions[0];
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertSame(827211, $transaction->id);
        $this->assertSame(10, $transaction->type);
        $this->assertSame('Aprovação', $transaction->typeLabel);
        $this->assertSame('OK', $transaction->responseStatus);
        $this->assertNull($transaction->errors);
        $this->assertSame(10, $transaction->status);
        $this->assertNull($transaction->refundedAmount);
        $this->assertSame('2026-09-23 14:32', $transaction->createdAt);

        $this->assertCount(1, $payment->splits);
        $split = $payment->splits[0];
        $this->assertInstanceOf(Split::class, $split);
        $this->assertSame(7461, $split->businessDestinationId);
        $this->assertSame('Parceiro LTDA', $split->businessDestinationName);
        $this->assertSame(0.0, $split->value);
        $this->assertSame(0.0, $split->percentage);
        $this->assertSame(99, $split->amount);

        $this->assertCount(1, $payment->creditSchedule);
        $schedule = $payment->creditSchedule[0];
        $this->assertInstanceOf(CreditSchedule::class, $schedule);
        $this->assertSame(618306, $schedule->id);
        $this->assertSame(120, $schedule->amount);
        $this->assertSame('2026-09-23T17:33:05.000000Z', $schedule->dateScheduled);
        $this->assertTrue($schedule->credited);
        $this->assertSame(10, $schedule->status);
        $this->assertNull($schedule->transferId);
        $this->assertNull($schedule->transferredAt);
    }

    public function testCreditCardPaymentFieldTypes()
    {
        $payment = $this->listPayments()[1];

        $this->assertSame(346569, $payment->cardId);
        $this->assertNull($payment->pix);
        $this->assertNull($payment->payer);
        $this->assertSame(10, $payment->method);
        $this->assertSame('credit_card', $payment->methodSlug);
        $this->assertSame(0.04, $payment->feeValue);
        $this->assertSame(20, $payment->cardFlag);
        $this->assertSame('Mastercard', $payment->cardFlagLabel);
        $this->assertSame(3.2, $payment->splits[0]->value);
        $this->assertSame(3.2, $payment->splits[0]->percentage);
        $this->assertSame(0, $payment->splits[0]->amount);
        $this->assertSame(6255, $payment->creditSchedule[0]->transferId);
        $this->assertSame('2026-09-22T03:00:03.000000Z', $payment->creditSchedule[0]->transferredAt);
    }

    public function testToArrayUsesSnakeCaseKeys()
    {
        $data = $this->listPayments()[0]->toArray();

        $this->assertSame(2.55, $data['pix']['amount']);
        $this->assertSame('bdV4p5XIWImMQix4cIgH0kQbFFRxfenUsTc', $data['pix']['txid']);
        $this->assertArrayHasKey('qrcode_data', $data['pix']);
        $this->assertSame('Aprovação', $data['transactions'][0]['type_label']);
        $this->assertSame(99, $data['splits'][0]['amount']);
        $this->assertSame(120, $data['credit_schedule'][0]['amount']);
        $this->assertSame([], $data['refunds']);
        $this->assertArrayNotHasKey('card_flag', $data);
    }
}
