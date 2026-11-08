<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\PlaceOrderRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PlaceOrderRequestTest extends TestCase
{
    use RequestTestHelper;

    private function valid(array $override = []): array
    {
        return array_merge([
            'customer_name' => 'Rahim Uddin',
            'phone' => '01712345678',
            'email' => 'rahim@example.com',
            'address' => 'House 12, Road 5, Dhanmondi, Dhaka',
            'quantity' => 2,
            'payment_method' => 'cod',
            'note' => 'Please call first',
        ], $override);
    }

    public function test_authorize_is_true(): void
    {
        $this->assertTrue((new PlaceOrderRequest)->authorize());
    }

    public function test_valid_data_passes(): void
    {
        $this->assertTrue($this->validate(PlaceOrderRequest::class, $this->valid())->passes());
    }

    public function test_optional_fields_may_be_omitted(): void
    {
        $data = $this->valid();
        unset($data['email'], $data['note']);

        $this->assertTrue($this->validate(PlaceOrderRequest::class, $data)->passes());
    }

    public function test_required_fields(): void
    {
        $errors = $this->validate(PlaceOrderRequest::class, [])->errors();

        foreach (['customer_name', 'phone', 'address', 'quantity', 'payment_method'] as $field) {
            $this->assertTrue($errors->has($field), "{$field} should be required");
        }
    }

    #[DataProvider('validPhones')]
    public function test_accepts_valid_bangladeshi_phone_numbers(string $phone): void
    {
        $this->assertFalse($this->validate(PlaceOrderRequest::class, $this->valid(['phone' => $phone]))->errors()->has('phone'));
    }

    public static function validPhones(): array
    {
        return [['01712345678'], ['+8801712345678'], ['8801912345678'], ['017 1234 5678'], ['017-1234-5678']];
    }

    #[DataProvider('invalidPhones')]
    public function test_rejects_invalid_phone_numbers(string $phone): void
    {
        $this->assertTrue($this->validate(PlaceOrderRequest::class, $this->valid(['phone' => $phone]))->errors()->has('phone'));
    }

    public static function invalidPhones(): array
    {
        return [['0123456789'], ['01212345678'], ['1712345678'], ['0171234567'], ['abcdefghijk'], ['017123456789']];
    }

    public function test_phone_error_message_is_friendly(): void
    {
        $v = $this->validate(PlaceOrderRequest::class, $this->valid(['phone' => '123']));

        $this->assertStringContainsString('valid Bangladeshi mobile number', $v->errors()->first('phone'));
    }

    public function test_email_must_be_valid_when_given(): void
    {
        $this->assertTrue($this->validate(PlaceOrderRequest::class, $this->valid(['email' => 'nope']))->errors()->has('email'));
    }

    public function test_quantity_must_be_between_1_and_100(): void
    {
        foreach ([0, -1, 101, 'abc', 1.5] as $bad) {
            $this->assertTrue($this->validate(PlaceOrderRequest::class, $this->valid(['quantity' => $bad]))->errors()->has('quantity'), "quantity {$bad}");
        }
        foreach ([1, 100] as $good) {
            $this->assertFalse($this->validate(PlaceOrderRequest::class, $this->valid(['quantity' => $good]))->errors()->has('quantity'));
        }
    }

    public function test_payment_method_must_be_a_known_value(): void
    {
        $this->assertTrue($this->validate(PlaceOrderRequest::class, $this->valid(['payment_method' => 'bitcoin']))->errors()->has('payment_method'));
    }

    public function test_address_needs_ten_characters(): void
    {
        $this->assertTrue($this->validate(PlaceOrderRequest::class, $this->valid(['address' => 'Dhaka']))->errors()->has('address'));
    }

    public function test_name_and_note_length_limits(): void
    {
        $errors = $this->validate(PlaceOrderRequest::class, $this->valid([
            'customer_name' => str_repeat('a', 101),
            'note' => str_repeat('n', 501),
        ]))->errors();

        $this->assertTrue($errors->has('customer_name'));
        $this->assertTrue($errors->has('note'));
    }

    public function test_attribute_names_are_human_readable(): void
    {
        $attributes = (new PlaceOrderRequest)->attributes();

        $this->assertSame('name', $attributes['customer_name']);
        $this->assertSame('payment method', $attributes['payment_method']);
    }
}
