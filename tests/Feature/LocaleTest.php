<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_defaults_to_english(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('FIND YOUR PERFECT FIT')
            ->assertSee('Login')
            ->assertDontSee('TEMUKAN UKURAN SEMPURNA');
    }

    public function test_switching_locale_stores_session_and_cookie_then_translates_storefront(): void
    {
        $this->from('/')
            ->get(route('locale.switch', 'id'))
            ->assertRedirect('/')
            ->assertSessionHas('locale', 'id')
            ->assertCookie('locale', 'id');

        $this->get('/')
            ->assertOk()
            ->assertSee('TEMUKAN UKURAN SEMPURNA')
            ->assertSee('Masuk')
            ->assertDontSee('FIND YOUR PERFECT FIT');
    }

    public function test_locale_can_be_restored_from_cookie_without_a_session_value(): void
    {
        $response = $this->from('/')->get(route('locale.switch', 'id'));
        $response->assertRedirect('/');

        $cookie = collect($response->headers->getCookies())
            ->firstWhere(fn ($cookie) => $cookie->getName() === 'locale');

        $this->assertNotNull($cookie);

        $this->flushSession();

        $this->withCookie('locale', $cookie->getValue())
            ->get('/')
            ->assertOk()
            ->assertSee('TEMUKAN UKURAN SEMPURNA');
    }

    public function test_switching_back_to_english_restores_english_copy(): void
    {
        $this->withSession(['locale' => 'id'])->get('/')->assertSee('TEMUKAN UKURAN SEMPURNA');

        $this->from('/')
            ->get(route('locale.switch', 'en'))
            ->assertRedirect('/')
            ->assertSessionHas('locale', 'en');

        $this->get('/')
            ->assertOk()
            ->assertSee('FIND YOUR PERFECT FIT')
            ->assertDontSee('TEMUKAN UKURAN SEMPURNA');
    }

    public function test_unsupported_locale_returns_not_found(): void
    {
        $this->get(route('locale.switch', 'fr'))->assertNotFound();
    }

    public function test_region_fields_and_validation_translate_in_indonesian(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['locale' => 'id'])
            ->get('/profile')
            ->assertOk()
            ->assertSee('Provinsi')
            ->assertSee('Kabupaten/Kota')
            ->assertSee('Pilih provinsi')
            ->assertSee('Pilih kelurahan/desa');
    }

    public function test_language_switcher_is_available_on_overlay_and_regular_navigation(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-language-switcher', false)
            ->assertSee(route('locale.switch', 'id'), false)
            ->assertSee('hreflang="id"', false)
            ->assertSee('aria-current="true"', false);

        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSee('data-language-switcher', false)
            ->assertSee(route('locale.switch', 'id'), false);
    }
}
