<?php

namespace Provydon\JsonToKeyvalue\Tests;

use PHPUnit\Framework\TestCase;

class ArrayFlattenTest extends TestCase
{
    public function test_flatten_simple_nested_array()
    {
        $input = [
            'user' => ['name' => 'John', 'address' => ['city' => 'Lagos']]
        ];

        $expected = [
            'user → name' => 'John',
            'user → address → city' => 'Lagos'
        ];

        $result = array_flatten_with_keys($input, '', ' → ');
        $this->assertEquals($expected, $result);
    }
}
