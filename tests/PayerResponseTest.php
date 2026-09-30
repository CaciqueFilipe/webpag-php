<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Http\ApiResponse;
use WebPag\Http\HttpClient;
use WebPag\Requests\Payers\Address;
use WebPag\Resources\Payers;
use WebPag\Responses\Card\CreditCard;
use WebPag\Responses\Pagination\PaginatedCollection;
use WebPag\Responses\Payers\Payer;

class PayerResponseTest extends TestCase
{
    /**
     * @return PaginatedCollection|Payer[]
     */
    private function listPayers()
    {
        $body = json_decode(file_get_contents(__DIR__ . '/fixtures/payers-list.json'), true);

        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('api/payers', [])
            ->willReturn(new ApiResponse($body, 200));

        return (new Payers($http))->list();
    }

    public function testListReturnsPaginatedPayers()
    {
        $payers = $this->listPayers();

        $this->assertInstanceOf(PaginatedCollection::class, $payers);
        $this->assertCount(2, $payers);
        $this->assertContainsOnlyInstancesOf(Payer::class, $payers);
        $this->assertSame(2, $payers->total());
        $this->assertSame(1, $payers->lastPage());
        $this->assertFalse($payers->hasMorePages());
        $this->assertNull($payers->nextPage());
    }

    public function testPayerFieldTypes()
    {
        $payer = $this->listPayers()[0];

        $this->assertSame(245044, $payer->id);
        $this->assertSame('00000000191', $payer->cpfCnpj);
        $this->assertFalse($payer->isBusiness);
        $this->assertSame('pagador@example.com', $payer->email);
        $this->assertSame('FULANO', $payer->firstName);
        $this->assertSame('DE TAL', $payer->lastName);
        $this->assertSame('M', $payer->gender);
        $this->assertNull($payer->phoneNumber);
        $this->assertSame('1954-12-11', $payer->birthDate);
        $this->assertNull($payer->useBoleto);
        $this->assertSame(10, $payer->status);
        $this->assertSame('Ativo', $payer->statusLabel);
        $this->assertSame('2026-08-26 22:15:46', $payer->createdAt);
        $this->assertSame('2026-09-18 14:35:21', $payer->updatedAt);

        $this->assertInstanceOf(Address::class, $payer->address);
        $this->assertSame('90030-040', $payer->address->zipCode);
        $this->assertSame('Rua Exemplo', $payer->address->street);
        $this->assertSame('408', $payer->address->number);
        $this->assertSame('Centro', $payer->address->district);
        $this->assertSame('Porto Alegre', $payer->address->city);
        $this->assertSame('RS', $payer->address->state);
        $this->assertSame('BR', $payer->address->country);
    }

    public function testPayerCards()
    {
        $cards = $this->listPayers()[0]->cards;

        $this->assertCount(2, $cards);
        $this->assertContainsOnlyInstancesOf(CreditCard::class, $cards);
        $this->assertSame(344630, $cards[0]->id);
        $this->assertSame('8119', $cards[0]->lastNumbers);
        $this->assertTrue($cards[0]->active);
        $this->assertSame('2026-09-18 14:35:22', $cards[0]->createdAt);
        $this->assertFalse($cards[1]->active);
    }

    public function testPayerWithoutAddressAndCards()
    {
        $payer = $this->listPayers()[1];

        $this->assertNull($payer->address);
        $this->assertSame([], $payer->cards);
        $this->assertNull($payer->gender);
        $this->assertNull($payer->birthDate);
    }

    public function testToArrayIncludesCards()
    {
        $payers = $this->listPayers();

        $data = $payers[0]->toArray();
        $this->assertSame('8119', $data['cards'][0]['last_numbers']);
        $this->assertSame('Porto Alegre', $data['address']['city']);

        $this->assertSame([], $payers[1]->toArray()['cards']);
    }

    public function testFindUnwrapsDataEnvelope()
    {
        $list = json_decode(file_get_contents(__DIR__ . '/fixtures/payers-list.json'), true);

        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('api/payers/245044')
            ->willReturn(new ApiResponse(['data' => $list['data'][0]], 200));

        $payer = (new Payers($http))->find(245044);

        $this->assertInstanceOf(Payer::class, $payer);
        $this->assertSame(245044, $payer->id);
        $this->assertSame('00000000191', $payer->cpfCnpj);
        $this->assertFalse($payer->isBusiness);
        $this->assertSame('M', $payer->gender);
        $this->assertSame('1954-12-11', $payer->birthDate);
        $this->assertSame(10, $payer->status);
        $this->assertSame('Porto Alegre', $payer->address->city);
        $this->assertCount(2, $payer->cards);
        $this->assertContainsOnlyInstancesOf(CreditCard::class, $payer->cards);
        $this->assertSame('8119', $payer->cards[0]->lastNumbers);
    }

    public function testCardsAbsentStaysNull()
    {
        $payer = Payer::fromArray(['id' => 1]);

        $this->assertNull($payer->cards);
    }
}
