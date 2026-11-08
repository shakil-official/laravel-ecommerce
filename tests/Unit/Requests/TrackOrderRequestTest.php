<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\TrackOrderRequest;
use Tests\TestCase;

class TrackOrderRequestTest extends TestCase
{
    use RequestTestHelper;

    public function test_authorize_is_true(): void
    {
        $this->assertTrue((new TrackOrderRequest)->authorize());
    }

    public function test_valid_token_passes(): void
    {
        $this->assertTrue($this->validate(TrackOrderRequest::class, ['token' => 'K7M2-9QXA-W4TP'])->passes());
    }

    public function test_token_is_normalised_before_validation(): void
    {
        $this->assertTrue($this->validate(TrackOrderRequest::class, ['token' => ' k7m2 9qxa w4tp '])->passes());
        $this->assertTrue($this->validate(TrackOrderRequest::class, ['token' => 'k7m29qxaw4tp'])->passes());
    }

    public function test_token_is_required(): void
    {
        $this->assertTrue($this->validate(TrackOrderRequest::class, [])->errors()->has('token'));
    }

    public function test_wrong_length_is_rejected(): void
    {
        foreach (['K7M2', 'K7M2-9QXA', 'K7M2-9QXA-W4TP-ZZZZ'] as $bad) {
            $this->assertTrue($this->validate(TrackOrderRequest::class, ['token' => $bad])->errors()->has('token'), $bad);
        }
    }

    public function test_error_messages_are_friendly(): void
    {
        $this->assertSame('Please enter your tracking token.', $this->validate(TrackOrderRequest::class, [])->errors()->first('token'));
        $this->assertStringContainsString('12 characters', $this->validate(TrackOrderRequest::class, ['token' => 'AB'])->errors()->first('token'));
    }
}
