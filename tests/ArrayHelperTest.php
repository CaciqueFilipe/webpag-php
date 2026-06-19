<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Support\ArrayHelper;

class ArrayHelperTest extends TestCase
{
    public function testFilterNullRemovesNullValues()
    {
        $input = ['a' => 1, 'b' => null, 'c' => 'hello', 'd' => null];
        $result = ArrayHelper::filterNull($input);

        $this->assertEquals(['a' => 1, 'c' => 'hello'], $result);
    }

    public function testFilterNullPreservesFalseAndZero()
    {
        $input = ['a' => 0, 'b' => false, 'c' => '', 'd' => null];
        $result = ArrayHelper::filterNull($input);

        $this->assertArrayHasKey('a', $result);
        $this->assertArrayHasKey('b', $result);
        $this->assertArrayHasKey('c', $result);
        $this->assertArrayNotHasKey('d', $result);
    }

    public function testFilterNullOnEmptyArray()
    {
        $this->assertEquals([], ArrayHelper::filterNull([]));
    }

    public function testFilterEmptyRemovesNullAndEmptyArrays()
    {
        $input = [
            'name' => 'John',
            'email' => null,
            'tags' => [],
            'scores' => [1, 2],
            'count' => 0,
        ];
        $result = ArrayHelper::filterEmpty($input);

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayNotHasKey('email', $result);
        $this->assertArrayNotHasKey('tags', $result);
        $this->assertArrayHasKey('scores', $result);
        $this->assertArrayHasKey('count', $result);
    }
}
