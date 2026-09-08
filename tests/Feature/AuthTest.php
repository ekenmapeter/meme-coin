<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_wallet(): void
    {
        $this->get(route('wallet.index'))->assertRedirect(route('login'));
        $this->get(route('swap'))->assertRedirect(route('login'));
        $this->get(route('deposit'))->assertRedirect(route('login'));
        $this->get(route('withdraw'))->assertRedirect(route('login'));
    }

    public function test_guests_are_redirected_to_login_from_admin_area(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_guests_are_redirected_to_login_from_launch(): void
    {
        $this->get(route('coins.launch'))->assertRedirect(route('login'));
    }

    public function test_registration_creates_user_with_zero_balances_and_user_role(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'MoonDegen',
            'email' => 'new@example.com',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertSame(User::ROLE_USER, $user->role);
        $this->assertFalse($user->isAdmin());
        $this->assertEquals(0.0, $user->sol_balance);
        $this->assertEquals(0.0, $user->btc_balance);
        $this->assertEquals(0.0, $user->usd_balance);
        $this->assertNotEmpty($user->wallet_address);
    }

    public function test_registration_rejects_weak_passwords(): void
    {
        $this->post(route('register'), [
            'name' => 'MoonDegen',
            'email' => 'new@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    public function test_registration_cannot_promote_to_admin(): void
    {
        $this->post(route('register'), [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
            'role' => User::ROLE_ADMIN,
        ]);

        $user = User::where('email', 'sneaky@example.com')->firstOrFail();
        $this->assertFalse($user->isAdmin());
    }

    public function test_connect_wallet_endpoint_has_been_removed(): void
    {
        $this->post('/connect-wallet', ['account' => 'admin'])
            ->assertStatus(404);
    }

    public function test_authenticated_users_cannot_view_login_or_register_pages(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->get(route('login'))->assertRedirect(route('home'));
        $this->actingAs($user)->get(route('register'))->assertRedirect(route('home'));
    }

    public function test_login_redirects_admins_to_dashboard_and_users_to_home(): void
    {
        $admin = $this->createAdmin(['password' => 'admin-secret-pass']);
        $user = $this->createUser(['password' => 'user-secret-pass']);

        $this->post(route('login'), ['email' => $admin->email, 'password' => 'admin-secret-pass'])
            ->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'));

        $this->post(route('login'), ['email' => $user->email, 'password' => 'user-secret-pass'])
            ->assertRedirect(route('home'));
    }

    public function test_admin_login_rejects_non_admin_users(): void
    {
        $user = $this->createUser(['password' => 'user-secret-pass']);

        $this->post(route('admin.login.submit'), ['email' => $user->email, 'password' => 'user-secret-pass'])
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_login_route_is_throttled(): void
    {
        $postLogin = collect(app('router')->getRoutes()->getRoutes())
            ->first(fn ($route) => $route->getName() === 'login' && in_array('POST', $route->methods(), true));

        $this->assertNotNull($postLogin);
        $this->assertContains('throttle:auth', $postLogin->gatherMiddleware());
    }

    public function test_logout_invalidates_session(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
