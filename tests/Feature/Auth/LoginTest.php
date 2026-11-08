<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_shown_to_guests(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Admin login');
    }

    public function test_logged_in_user_is_redirected_away_from_login_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com', 'password' => 'secret123']);

        $this->post(route('login.store'), ['email' => 'admin@example.com', 'password' => 'secret123'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_redirects_to_the_page_originally_requested(): void
    {
        User::factory()->create(['email' => 'admin@example.com', 'password' => 'secret123']);

        $this->get(route('admin.orders.index'))->assertRedirect(route('login'));
        $this->post(route('login.store'), ['email' => 'admin@example.com', 'password' => 'secret123'])
            ->assertRedirect(route('admin.orders.index'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'admin@example.com', 'password' => 'secret123']);

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => 'admin@example.com', 'password' => 'wrong'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_fails_for_unknown_email(): void
    {
        $this->post(route('login.store'), ['email' => 'nobody@example.com', 'password' => 'x'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_validates_required_fields(): void
    {
        $this->post(route('login.store'), [])->assertSessionHasErrors(['email', 'password']);
        $this->post(route('login.store'), ['email' => 'not-an-email', 'password' => 'x'])->assertSessionHasErrors('email');
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post(route('login.store'), ['email' => 'a@example.com', 'password' => 'bad']);
        }

        $this->post(route('login.store'), ['email' => 'a@example.com', 'password' => 'bad'])->assertStatus(429);
    }

    public function test_logout_ends_the_session(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('logout'))
            ->assertRedirect(route('shop.index'))
            ->assertSessionHas('success');

        $this->assertGuest();
    }

    public function test_guest_cannot_post_logout(): void
    {
        $this->post(route('logout'))->assertRedirect(route('login'));
    }

    public function test_storefront_never_shows_admin_links(): void
    {
        $this->get(route('shop.index'))
            ->assertDontSee('Admin login')
            ->assertDontSee('Logout')
            ->assertDontSee(route('login'), false)
            ->assertDontSee(route('admin.dashboard'), false);

        $this->actingAs(User::factory()->create())
            ->get(route('shop.index'))
            ->assertDontSee('Logout')
            ->assertDontSee(route('admin.dashboard'), false);
    }
}
