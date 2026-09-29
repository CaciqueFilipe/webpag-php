<?php

namespace WebPag\Tests;

use LogicException;
use PHPUnit\Framework\TestCase;
use WebPag\Http\ApiResponse;
use WebPag\Http\HttpClient;
use WebPag\Resources\Installments;
use WebPag\Resources\Payers;
use WebPag\Resources\PaymentLinks;
use WebPag\Resources\Payments;
use WebPag\Resources\Recurrency;
use WebPag\Resources\Transfers;
use WebPag\Responses\Installments\InstallmentPlan;
use WebPag\Responses\Pagination\PageLink;
use WebPag\Responses\Pagination\PaginatedCollection;
use WebPag\Responses\Pagination\PaginationLinks;
use WebPag\Responses\Pagination\PaginationMeta;
use WebPag\Responses\Payers\Payer;
use WebPag\Responses\PaymentLinks\PaymentLink;
use WebPag\Responses\Payments\Payment;
use WebPag\Responses\Recurrency\Recurrency as RecurrencyResponse;
use WebPag\Responses\Transfers\Transfer;

class PaginatedCollectionTest extends TestCase
{
    /**
     * @param array<string, mixed> $body
     *
     * @return PaginatedCollection
     */
    private function collectionFrom(array $body)
    {
        return PaginatedCollection::fromResponse(new ApiResponse($body, 200), [Payment::class, 'fromArray']);
    }

    /**
     * @return array<string, mixed>
     */
    private function paginatedBody()
    {
        return [
            'data' => [['id' => 1], ['id' => 2]],
            'links' => [
                'first' => 'https://api.webpag.com.br/api/payments?page=1',
                'last' => 'https://api.webpag.com.br/api/payments?page=3',
                'prev' => null,
                'next' => 'https://api.webpag.com.br/api/payments?page=2',
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 3,
                'links' => [
                    ['url' => null, 'label' => '&laquo; Anterior', 'active' => false],
                    ['url' => 'https://api.webpag.com.br/api/payments?page=1', 'label' => '1', 'active' => true],
                ],
                'path' => 'https://api.webpag.com.br/api/payments',
                'per_page' => 15,
                'to' => 15,
                'total' => 34,
            ],
        ];
    }

    public function testParsesItemsLinksAndMeta()
    {
        $collection = $this->collectionFrom($this->paginatedBody());

        $this->assertCount(2, $collection);
        $this->assertContainsOnlyInstancesOf(Payment::class, $collection);
        $this->assertSame(1, $collection[0]->id);
        $this->assertSame(2, $collection->all()[1]->id);
        $this->assertSame(1, $collection->first()->id);
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->isPaginated());

        $links = $collection->getLinks();
        $this->assertInstanceOf(PaginationLinks::class, $links);
        $this->assertSame('https://api.webpag.com.br/api/payments?page=1', $links->first);
        $this->assertSame('https://api.webpag.com.br/api/payments?page=3', $links->last);
        $this->assertNull($links->prev);
        $this->assertSame('https://api.webpag.com.br/api/payments?page=2', $links->next);

        $meta = $collection->getMeta();
        $this->assertInstanceOf(PaginationMeta::class, $meta);
        $this->assertSame(1, $meta->currentPage);
        $this->assertSame(1, $meta->from);
        $this->assertSame(3, $meta->lastPage);
        $this->assertSame('https://api.webpag.com.br/api/payments', $meta->path);
        $this->assertSame(15, $meta->perPage);
        $this->assertSame(15, $meta->to);
        $this->assertSame(34, $meta->total);
        $this->assertCount(2, $meta->links);
        $this->assertInstanceOf(PageLink::class, $meta->links[0]);
        $this->assertNull($meta->links[0]->url);
        $this->assertSame('1', $meta->links[1]->label);
        $this->assertTrue($meta->links[1]->active);
    }

    public function testPaginationHelpers()
    {
        $collection = $this->collectionFrom($this->paginatedBody());

        $this->assertSame(1, $collection->currentPage());
        $this->assertSame(3, $collection->lastPage());
        $this->assertSame(15, $collection->perPage());
        $this->assertSame(34, $collection->total());
        $this->assertTrue($collection->hasMorePages());
        $this->assertSame(2, $collection->nextPage());
    }

    public function testLastPageHasNoNextPage()
    {
        $body = $this->paginatedBody();
        $body['meta']['current_page'] = 3;
        $body['links']['next'] = null;

        $collection = $this->collectionFrom($body);

        $this->assertFalse($collection->hasMorePages());
        $this->assertNull($collection->nextPage());
    }

    public function testEmptyPage()
    {
        $body = $this->paginatedBody();
        $body['data'] = [];
        $body['meta']['from'] = null;
        $body['meta']['to'] = null;

        $collection = $this->collectionFrom($body);

        $this->assertCount(0, $collection);
        $this->assertTrue($collection->isEmpty());
        $this->assertNull($collection->first());
        $this->assertNull($collection->getMeta()->from);
        $this->assertNull($collection->getMeta()->to);
    }

    public function testPlainListWithoutPagination()
    {
        $collection = $this->collectionFrom(['data' => [['id' => 7]]]);

        $this->assertCount(1, $collection);
        $this->assertSame(7, $collection[0]->id);
        $this->assertFalse($collection->isPaginated());
        $this->assertNull($collection->getLinks());
        $this->assertNull($collection->getMeta());
        $this->assertNull($collection->total());
        $this->assertFalse($collection->hasMorePages());
        $this->assertNull($collection->nextPage());
    }

    public function testBodyWithoutDataKeyIsTreatedAsList()
    {
        $collection = $this->collectionFrom([['id' => 1], ['id' => 2], ['id' => 3]]);

        $this->assertCount(3, $collection);
        $this->assertSame(3, $collection[2]->id);
    }

    public function testSingleObjectIsNotTreatedAsList()
    {
        $collection = $this->collectionFrom(['data' => ['id' => 1, 'business' => ['id' => 5]]]);

        $this->assertTrue($collection->isEmpty());
    }

    public function testIgnoresNonArrayItems()
    {
        $collection = $this->collectionFrom(['data' => [['id' => 1], null, 'x']]);

        $this->assertCount(1, $collection);
    }

    public function testIsIterable()
    {
        $ids = [];
        foreach ($this->collectionFrom($this->paginatedBody()) as $index => $payment) {
            $ids[$index] = $payment->id;
        }

        $this->assertSame([0 => 1, 1 => 2], $ids);
    }

    public function testArrayAccess()
    {
        $collection = $this->collectionFrom($this->paginatedBody());

        $this->assertTrue(isset($collection[1]));
        $this->assertFalse(isset($collection[5]));
        $this->assertNull($collection[5]);
    }

    public function testIsReadOnly()
    {
        $collection = $this->collectionFrom($this->paginatedBody());

        $this->expectException(LogicException::class);
        $collection[0] = null;
    }

    public function testUnsetIsNotAllowed()
    {
        $collection = $this->collectionFrom($this->paginatedBody());

        $this->expectException(LogicException::class);
        unset($collection[0]);
    }

    public function testToArrayAndJson()
    {
        $collection = $this->collectionFrom($this->paginatedBody());

        $array = $collection->toArray();
        $this->assertSame([['id' => 1], ['id' => 2]], $array['data']);
        $this->assertSame('https://api.webpag.com.br/api/payments?page=2', $array['links']['next']);
        $this->assertSame(34, $array['meta']['total']);
        $this->assertSame('1', $array['meta']['links'][1]['label']);

        $this->assertSame(json_encode($array), json_encode($collection));
    }

    public function testToArrayWithoutPagination()
    {
        $collection = $this->collectionFrom(['data' => [['id' => 7]]]);

        $this->assertSame(['data' => [['id' => 7]]], $collection->toArray());
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public function listResourcesProvider()
    {
        return [
            'payments' => [Payments::class, 'api/payments', Payment::class],
            'payers' => [Payers::class, 'api/payers', Payer::class],
            'paymentLinks' => [PaymentLinks::class, 'api/payment-links', PaymentLink::class],
            'installments' => [Installments::class, 'api/installments', InstallmentPlan::class],
            'recurrency' => [Recurrency::class, 'api/payments/recurrency/list', RecurrencyResponse::class],
            'transfers' => [Transfers::class, 'api/transfers', Transfer::class],
        ];
    }

    /**
     * @dataProvider listResourcesProvider
     *
     * @param string $resourceClass
     * @param string $uri
     * @param string $dtoClass
     */
    public function testResourceListReturnsPaginatedCollection($resourceClass, $uri, $dtoClass)
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with($uri, ['page' => 2])
            ->willReturn(new ApiResponse($this->paginatedBody(), 200));

        $resource = new $resourceClass($http);
        $result = $resource->list(['page' => 2]);

        $this->assertInstanceOf(PaginatedCollection::class, $result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf($dtoClass, $result);
        $this->assertSame(34, $result->total());
    }

    public function testPaymentLinksListWithoutFilters()
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('api/payment-links', [])
            ->willReturn(new ApiResponse(['data' => []], 200));

        $result = (new PaymentLinks($http))->list();

        $this->assertTrue($result->isEmpty());
    }
}
