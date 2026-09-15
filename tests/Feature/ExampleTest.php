<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     * Trang chủ redirect tới admin login, nên expect 302.
     */
    public function test_the_application_returns_a_redirect_to_admin_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
    }
}
