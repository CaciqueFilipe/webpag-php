<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Contracts\RequestPayload;
use WebPag\Http\HttpClient;
use WebPag\Resources\AbstractResource;

class AbstractResourceTest extends TestCase
{
    public function testResolvePayloadWithRequestPayload()
    {
        $http = $this->createMock(HttpClient::class);

        /** @var mixed $resource */
        $resource = new class ($http) extends AbstractResource {
            public function resolve($payload)
            {
                return $this->resolvePayload($payload);
            }
        };

        $dto = new class () implements RequestPayload {
            public function toArray(): array
            {
                return ['key' => 'value', 'num' => 42];
            }

            public static function fromArray(array $data)
            {
                return new self();
            }
        };

        $result = $resource->resolve($dto);

        $this->assertEquals(['key' => 'value', 'num' => 42], $result);
    }

    public function testResolvePayloadWithArray()
    {
        $http = $this->createMock(HttpClient::class);

        /** @var mixed $resource */
        $resource = new class ($http) extends AbstractResource {
            public function resolve($payload)
            {
                return $this->resolvePayload($payload);
            }
        };

        $result = $resource->resolve(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $result);
    }

    public function testResolvePayloadWithNull()
    {
        $http = $this->createMock(HttpClient::class);

        /** @var mixed $resource */
        $resource = new class ($http) extends AbstractResource {
            public function resolve($payload)
            {
                return $this->resolvePayload($payload);
            }
        };

        $result = $resource->resolve(null);

        $this->assertEquals([], $result);
    }
}
