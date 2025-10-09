<?php

namespace Provydon\JsonToKeyvalue\Tests;

use PHPUnit\Framework\TestCase;
use Provydon\JsonToKeyvalue\Formatters;

class FormattersTest extends TestCase
{
    public function test_currency_formatter()
    {
        $formatter = Formatters::currency('$', 2);
        $result = $formatter(1234.56);

        $this->assertEquals('$1,234.56', $result);
    }

    public function test_date_formatter()
    {
        $formatter = Formatters::date('Y-m-d');
        $result = $formatter('2025-01-15');

        $this->assertEquals('2025-01-15', $result);
    }

    public function test_boolean_formatter()
    {
        $formatter = Formatters::boolean('Active', 'Inactive');

        $this->assertEquals('Active', $formatter(true));
        $this->assertEquals('Inactive', $formatter(false));
    }

    public function test_uppercase_formatter()
    {
        $formatter = Formatters::uppercase();
        $result = $formatter('hello');

        $this->assertEquals('HELLO', $result);
    }

    public function test_percentage_formatter()
    {
        $formatter = Formatters::percentage(2);
        $result = $formatter(45.678);

        $this->assertEquals('45.68%', $result);
    }

    public function test_file_size_formatter()
    {
        $formatter = Formatters::fileSize();

        $this->assertEquals('1 KB', $formatter(1024));
        $this->assertEquals('1 MB', $formatter(1048576));
    }

    public function test_truncate_formatter()
    {
        $formatter = Formatters::truncate(10, '...');
        $result = $formatter('This is a long text');

        $this->assertEquals('This is a ...', $result);
    }

    public function test_enum_label_formatter()
    {
        $formatter = Formatters::enumLabel([
            'pending' => 'Pending Payment',
            'completed' => 'Payment Completed',
        ]);

        $this->assertEquals('Pending Payment', $formatter('pending'));
        $this->assertEquals('Payment Completed', $formatter('completed'));
        $this->assertEquals('Unknown Status', $formatter('unknown_status'));
    }
}
