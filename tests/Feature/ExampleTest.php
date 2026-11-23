<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_serves_the_storefront_homepage(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }
}
