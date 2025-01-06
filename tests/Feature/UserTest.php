<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itShouldReturnBadRequestIfHasNoUser(): void
    {
        $url = route('podcasts.index');
        $response = $this->get($url);

        $response->assertStatus(400);
    }

    #[Test]
    public function itShouldReturnCorrectlyIfHasUser(): void
    {
        $url = route('podcasts.index');
        $headers = [
            'APP-USERNAME' => 'laravel',
            'APP-SERVER-URL' => 'http://laravel.local.host'
        ];
        $response = $this->get($url, $headers);

        $response->assertStatus(200);
    }
}
