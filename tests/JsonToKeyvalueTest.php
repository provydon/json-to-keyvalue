<?php

namespace Provydon\JsonToKeyvalue\Tests;

use PHPUnit\Framework\TestCase;
use Provydon\JsonToKeyvalue\JsonToKeyvalue;

class JsonToKeyvalueTest extends TestCase
{
    public function test_make_instance()
    {
        $instance = JsonToKeyvalue::make(['name' => 'John'], 'User');

        $this->assertInstanceOf(JsonToKeyvalue::class, $instance);
    }

    public function test_fluent_api_skip()
    {
        $data = ['name' => 'John', 'password' => 'secret'];

        $result = JsonToKeyvalue::make($data, 'User')
            ->skip(['password'])
            ->toArray();

        $this->assertArrayHasKey('Name', $result[0]);
        $this->assertArrayNotHasKey('Password', $result[0]);
    }

    public function test_fluent_api_exclude_suffixes()
    {
        $data = ['name' => 'John', 'field_error' => 'error'];

        $result = JsonToKeyvalue::make($data, 'User')
            ->excludeSuffixes(['_error'])
            ->toArray();

        $this->assertArrayHasKey('Name', $result[0]);
        $this->assertArrayNotHasKey('Field Error', $result[0]);
    }

    public function test_fluent_api_labels()
    {
        $data = ['first_name' => 'John'];

        $result = JsonToKeyvalue::make($data, 'User')
            ->labels(['first_name' => 'First Name'])
            ->toArray();

        $this->assertArrayHasKey('First Name', $result[0]);
        $this->assertEquals('John', $result[0]['First Name']);
    }

    public function test_fluent_api_formatters()
    {
        $data = ['amount' => 1000];

        $result = JsonToKeyvalue::make($data, 'Payment')
            ->formatters([
                'amount' => fn ($value) => '$'.number_format($value, 2),
            ])
            ->toArray();

        $this->assertEquals('$1,000.00', $result[0]['Amount']);
    }

    public function test_nested_separator()
    {
        $data = ['user' => ['name' => 'John']];

        $result = JsonToKeyvalue::make($data, 'Data')
            ->nestedSeparator(' > ')
            ->toArray();

        $this->assertArrayHasKey('User > Name', $result[0]);
    }

    public function test_handles_empty_data()
    {
        $result = JsonToKeyvalue::make([], 'Empty')->toArray();

        $this->assertEmpty($result);
    }

    public function test_handles_json_string()
    {
        $json = json_encode(['name' => 'John']);

        $result = JsonToKeyvalue::make($json, 'User')->toArray();

        $this->assertArrayHasKey('Name', $result[0]);
        $this->assertEquals('John', $result[0]['Name']);
    }

    public function test_handles_multiple_items()
    {
        $data = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
        ];

        $result = JsonToKeyvalue::make($data, 'Users')->toArray();

        $this->assertCount(2, $result);
        $this->assertEquals('John', $result[0]['Name']);
        $this->assertEquals('Jane', $result[1]['Name']);
    }
}
