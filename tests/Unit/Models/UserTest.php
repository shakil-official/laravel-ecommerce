<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_is_hashed_automatically(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass']);

        $this->assertNotSame('secret-pass', $user->password);
        $this->assertTrue(Hash::check('secret-pass', $user->password));
    }

    public function test_password_and_remember_token_are_hidden_from_serialisation(): void
    {
        $array = User::factory()->create()->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'a@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => 'a@example.com']);
    }
}
