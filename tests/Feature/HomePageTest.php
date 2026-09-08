<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_without_coins(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_home_page_shows_active_coins(): void
    {
        $this->createCoin();

        $this->get('/')
            ->assertOk()
            ->assertSee('PEPEKING');
    }

    public function test_search_returns_coin_json(): void
    {
        $this->createCoin();

        $this->getJson(route('search', ['q' => 'PEPE']))
            ->assertOk()
            ->assertJsonCount(1);
    }
}
