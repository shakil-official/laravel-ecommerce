<?php

namespace Tests\Unit\Support;

use App\Support\Money;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    public function test_formats_with_currency_and_two_decimals(): void
    {
        config(['shop.currency' => '$']);

        $this->assertSame('$ 1,250.00', Money::format(1250));
        $this->assertSame('$ 9.50', Money::format('9.5'));
    }

    public function test_null_is_formatted_as_zero(): void
    {
        config(['shop.currency' => '৳']);

        $this->assertSame('৳ 0.00', Money::format(null));
    }
}
