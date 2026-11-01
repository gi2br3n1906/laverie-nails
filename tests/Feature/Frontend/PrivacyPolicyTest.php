<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_privacy_policy_is_public_and_explains_key_processors(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('font-open-sans', false)
            ->assertSee('laverienails@gmail.com')
            ->assertSee('Midtrans')
            ->assertSee('Google')
            ->assertSee('Cloudflare');
    }

    public function test_the_privacy_policy_can_be_read_in_indonesian(): void
    {
        $this->withSession(['locale' => 'id'])
            ->get(route('privacy'))
            ->assertOk()
            ->assertSee('Kebijakan Privasi')
            ->assertSee('laverienails@gmail.com')
            ->assertSee('Midtrans');
    }

    public function test_the_footer_links_to_the_privacy_policy(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('privacy'), false)
            ->assertSee('Privacy Policy');
    }
}
