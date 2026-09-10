<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_sent_on_every_response(): void
    {
        $response = $this->get('/')->assertOk();

        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        $this->assertSame('0', $response->headers->get('X-XSS-Protection'));
        $this->assertNotNull($response->headers->get('Permissions-Policy'));
    }

    public function test_hsts_is_only_sent_over_secure_connections(): void
    {
        $insecure = $this->get('/')->assertOk();
        $this->assertNull($insecure->headers->get('Strict-Transport-Security'));

        $secure = $this->get('https://localhost/')->assertOk();
        $this->assertStringStartsWith('max-age=31536000', $secure->headers->get('Strict-Transport-Security'));
    }

    public function test_force_https_redirects_insecure_requests_when_enabled(): void
    {
        config()->set('app.force_https', true);

        $this->get('/')->assertRedirect('https://localhost:8000');
        $this->get('https://localhost/')->assertOk();
    }

    public function test_trading_endpoint_is_rate_limited(): void
    {
        $route = $this->app['router']->getRoutes()->getByName('coins.trade');

        $this->assertContains('throttle:trades', $route->gatherMiddleware());
    }

    public function test_wallet_endpoints_are_rate_limited(): void
    {
        foreach (['wallet.swap', 'wallet.deposit', 'wallet.withdraw'] as $name) {
            $route = $this->app['router']->getRoutes()->getByName($name);

            $this->assertContains('throttle:wallet', $route->gatherMiddleware());
        }
    }

    public function test_public_polling_endpoints_are_rate_limited(): void
    {
        foreach (['api.market.live', 'search', 'api.coins.chart', 'api.coins.trades'] as $name) {
            $route = $this->app['router']->getRoutes()->getByName($name);

            $this->assertContains('throttle:public', $route->gatherMiddleware());
        }
    }
}
