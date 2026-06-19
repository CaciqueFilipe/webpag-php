<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Requests\Business\AuthenticateRequest;
use WebPag\Requests\Business\CreateFranchiseRequest;
use WebPag\Requests\Installments\CreateInstallmentRequest;
use WebPag\Requests\Installments\ListInstallmentsRequest;
use WebPag\Requests\Payers\Address;
use WebPag\Requests\Payers\CreatePayerRequest;
use WebPag\Requests\Payers\UpdatePayerRequest;
use WebPag\Requests\Payers\SaveCreditCardRequest;
use WebPag\Requests\Payments\ProcessPaymentRequest;
use WebPag\Requests\Payments\CreditCardData;
use WebPag\Requests\Payments\PaymentSplit;
use WebPag\Requests\Payments\ListPaymentsRequest;
use WebPag\Requests\Payments\RefundPaymentRequest;
use WebPag\Requests\PaymentLinks\CreatePaymentLinkRequest;
use WebPag\Requests\Recurrency\CreateRecurrencyRequest;
use WebPag\Requests\Recurrency\ListRecurrencyRequest;
use WebPag\Requests\Recurrency\UpdateRecurrencyRequest;
use WebPag\Requests\Transfers\CreateTransferRequest;
use WebPag\Requests\Transfers\ListTransfersRequest;
use WebPag\Requests\Transfers\ChangeTransferStatusDevRequest;

class RequestPayloadsTest extends TestCase
{
    public function testAuthenticateRequest()
    {
        $req = new AuthenticateRequest();
        $req->email = 'user@example.com';
        $req->password = 'secret';

        $this->assertEquals([
            'email' => 'user@example.com',
            'password' => 'secret',
        ], $req->toArray());
    }

    public function testCreateFranchiseRequest()
    {
        $address = new Address();
        $address->zipCode = '01001-000';
        $address->street = 'Rua A';
        $address->number = '100';
        $address->district = 'Centro';
        $address->city = 'São Paulo';
        $address->state = 'SP';

        $req = new CreateFranchiseRequest();
        $req->razaoSocial = 'Empresa Ltda';
        $req->cnpj = '11111111000111';
        $req->address = $address;

        $result = $req->toArray();

        $this->assertEquals('Empresa Ltda', $result['razao_social']);
        $this->assertEquals('11111111000111', $result['cnpj']);
        $this->assertEquals('01001-000', $result['address']['zip_code']);
        $this->assertArrayNotHasKey('transfer_frequency', $result);
    }

    public function testCreateInstallmentRequest()
    {
        $req = new CreateInstallmentRequest();
        $req->amount = 10000;
        $req->firstDate = '2025-01-15';
        $req->numberInstallments = 6;
        $req->payer = 42;
        $req->acceptAfterDueDate = true;

        $this->assertEquals([
            'amount' => 10000,
            'first_date' => '2025-01-15',
            'number_installments' => 6,
            'payer' => 42,
            'accept_after_due_date' => true,
        ], $req->toArray());
    }

    public function testListInstallmentsRequest()
    {
        $req = new ListInstallmentsRequest();
        $req->payerId = 42;
        $req->page = 2;

        $result = $req->toArray();

        $this->assertEquals(42, $result['payer_id']);
        $this->assertEquals(2, $result['page']);
        $this->assertArrayNotHasKey('per_page', $result);
    }

    public function testProcessPaymentRequestMinimal()
    {
        $req = new ProcessPaymentRequest();
        $req->payerId = 15;
        $req->name = 'Pedido #123';
        $req->amount = 1500;
        $req->method = 'pix';

        $this->assertEquals([
            'payer_id' => 15,
            'name' => 'Pedido #123',
            'amount' => 1500,
            'method' => 'pix',
        ], $req->toArray());
    }

    public function testProcessPaymentRequestWithCardAndSplits()
    {
        $card = new CreditCardData();
        $card->number = '4111111111111111';
        $card->name = 'John Doe';
        $card->expirationMonth = '12';
        $card->expirationYear = '2028';
        $card->securityCode = '123';

        $split1 = new PaymentSplit();
        $split1->businessId = 5;
        $split1->percentage = 50.0;

        $split2 = new PaymentSplit();
        $split2->businessId = 6;
        $split2->amount = 750;

        $req = new ProcessPaymentRequest();
        $req->payerId = 15;
        $req->name = 'Split Payment';
        $req->amount = 1500;
        $req->method = 'credit_card';
        $req->installments = 2;
        $req->card = $card;
        $req->splits = [$split1, $split2];
        $req->softDescriptor = 'LOJA ABC';

        $result = $req->toArray();

        $this->assertEquals('credit_card', $result['method']);
        $this->assertEquals(2, $result['installments']);
        $this->assertEquals('4111111111111111', $result['card']['number']);
        $this->assertCount(2, $result['splits']);
        $this->assertEquals(5, $result['splits'][0]['business_id']);
        $this->assertEquals(50.0, $result['splits'][0]['percentage']);
        $this->assertEquals(6, $result['splits'][1]['business_id']);
        $this->assertEquals('LOJA ABC', $result['soft_descriptor']);
    }

    public function testListPaymentsRequest()
    {
        $req = new ListPaymentsRequest();
        $req->payerId = 15;
        $req->status = 40;
        $req->method = 'pix';
        $req->active = true;

        $result = $req->toArray();

        $this->assertEquals(15, $result['payer_id']);
        $this->assertEquals(40, $result['status']);
        $this->assertEquals('pix', $result['method']);
        $this->assertTrue($result['active']);
        $this->assertArrayNotHasKey('payment_id', $result);
    }

    public function testRefundPaymentRequest()
    {
        $req = new RefundPaymentRequest();
        $req->value = 500;

        $this->assertEquals(['value' => 500], $req->toArray());
    }

    public function testRefundPaymentRequestEmpty()
    {
        $req = new RefundPaymentRequest();

        $this->assertEquals([], $req->toArray());
    }

    public function testCreatePayerRequest()
    {
        $address = new Address();
        $address->zipCode = '20000-000';
        $address->street = 'Rua X';
        $address->number = '200';

        $req = new CreatePayerRequest();
        $req->firstName = 'João';
        $req->lastName = 'Silva';
        $req->email = 'joao@example.com';
        $req->isBusiness = false;
        $req->cpfCnpj = '11122233344';
        $req->address = $address;

        $result = $req->toArray();

        $this->assertEquals('João', $result['first_name']);
        $this->assertEquals('Silva', $result['last_name']);
        $this->assertEquals('20000-000', $result['address']['zip_code']);
        $this->assertArrayNotHasKey('gender', $result);
    }

    public function testUpdatePayerRequest()
    {
        $req = new UpdatePayerRequest();
        $req->firstName = 'Maria';
        $req->email = 'maria@example.com';

        $result = $req->toArray();

        $this->assertEquals('Maria', $result['first_name']);
        $this->assertEquals('maria@example.com', $result['email']);
        $this->assertArrayNotHasKey('last_name', $result);
    }

    public function testSaveCreditCardRequest()
    {
        $req = new SaveCreditCardRequest();
        $req->cardToken = 'tok_abc123';
        $req->name = 'João Silva';

        $result = $req->toArray();

        $this->assertEquals('tok_abc123', $result['card_token']);
        $this->assertEquals('João Silva', $result['name']);
        $this->assertArrayNotHasKey('number', $result);
    }

    public function testCreatePaymentLinkRequest()
    {
        $req = new CreatePaymentLinkRequest();
        $req->acceptedMethods = ['credit_card', 'pix'];
        $req->name = 'Produto X';
        $req->description = 'Descrição do produto';
        $req->value = 2990;
        $req->validity = '2025-12-31';
        $req->validityBoleto = 3;
        $req->numberInstallments = 6;

        $result = $req->toArray();

        $this->assertEquals(['credit_card', 'pix'], $result['accepted_methods']);
        $this->assertEquals('Produto X', $result['name']);
        $this->assertEquals(2990, $result['value']);
        $this->assertEquals('2025-12-31', $result['validity']);
        $this->assertEquals(6, $result['number_installments']);
        $this->assertArrayNotHasKey('is_recurrence', $result);
    }

    public function testCreateRecurrencyRequest()
    {
        $req = new CreateRecurrencyRequest();
        $req->payerId = 15;
        $req->name = 'Assinatura Premium';
        $req->frequency = 'monthly';
        $req->startDate = '2025-01-01 00:00';
        $req->amount = 2990;

        $result = $req->toArray();

        $this->assertEquals(15, $result['payer_id']);
        $this->assertEquals('monthly', $result['frequency']);
        $this->assertEquals(2990, $result['amount']);
        $this->assertArrayNotHasKey('installments', $result);
    }

    public function testListRecurrencyRequest()
    {
        $req = new ListRecurrencyRequest();
        $req->payerId = 15;
        $req->page = 1;

        $result = $req->toArray();

        $this->assertEquals(15, $result['payer_id']);
        $this->assertEquals(1, $result['page']);
        $this->assertArrayNotHasKey('recurrence_code', $result);
    }

    public function testUpdateRecurrencyRequest()
    {
        $req = new UpdateRecurrencyRequest();
        $req->amount = 4990;
        $req->installments = 12;

        $result = $req->toArray();

        $this->assertEquals(4990, $result['amount']);
        $this->assertEquals(12, $result['installments']);
        $this->assertArrayNotHasKey('name', $result);
    }

    public function testCreateTransferRequest()
    {
        $req = new CreateTransferRequest();
        $req->amount = 50000;
        $req->type = 10;

        $result = $req->toArray();

        $this->assertEquals(50000, $result['amount']);
        $this->assertEquals(10, $result['type']);
        $this->assertArrayNotHasKey('notes', $result);
    }

    public function testCreateTransferRequestForPix()
    {
        $req = new CreateTransferRequest();
        $req->amount = 10000;
        $req->type = 30;
        $req->pixKey = 'joao@example.com';
        $req->pixKeyType = 'email';
        $req->accountHolder = 'João Silva';

        $result = $req->toArray();

        $this->assertEquals('joao@example.com', $result['pix_key']);
        $this->assertEquals('email', $result['pix_key_type']);
        $this->assertEquals('João Silva', $result['account_holder']);
    }

    public function testListTransfersRequest()
    {
        $req = new ListTransfersRequest();
        $req->page = 1;
        $req->perPage = 20;

        $result = $req->toArray();

        $this->assertEquals(1, $result['page']);
        $this->assertEquals(20, $result['per_page']);
    }

    public function testListTransfersRequestEmpty()
    {
        $req = new ListTransfersRequest();

        $this->assertEquals([], $req->toArray());
    }

    public function testChangeTransferStatusDevRequest()
    {
        $req = new ChangeTransferStatusDevRequest();
        $req->status = 40;

        $this->assertEquals(['status' => 40], $req->toArray());
    }

    public function testAddressToArray()
    {
        $address = new Address();
        $address->zipCode = '01001-000';
        $address->street = 'Rua Exemplo';
        $address->number = '123';
        $address->district = 'Centro';
        $address->city = 'São Paulo';
        $address->state = 'SP';

        $result = $address->toArray();

        $this->assertEquals([
            'zip_code' => '01001-000',
            'street' => 'Rua Exemplo',
            'number' => '123',
            'district' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
        ], $result);
    }
}
