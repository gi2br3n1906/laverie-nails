<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\HeroBanner;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InformationalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_tells_the_laverie_story(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Why Laverie?')
            ->assertDontSee('OUR STORY')
            ->assertSee('font-script', false)
            ->assertSee('Laverie was born from La Vie')
            ->assertSee('press-on nails for every version of you')
            ->assertSee('nail game always on point')
            ->assertSee('data-landscape-hero', false)
            ->assertSee('data-informational-hero-image', false)
            ->assertSee('images/hero-banner.png')
            ->assertSee('font-display text-2xl leading-relaxed', false)
            ->assertSee('font-serif text-[0.9375rem] font-normal leading-[1.9] text-stone-700 sm:text-base', false)
            ->assertSee('font-script text-2xl text-stone-500', false)
            ->assertDontSee('font-serif text-4xl font-normal', false)
            ->assertDontSee('laverie</p>', false);
    }

    public function test_faq_renders_the_eight_questions_in_canva_order(): void
    {
        $response = $this->get(route('faq'))
            ->assertOk()
            ->assertSee('font-open-sans', false)
            ->assertSee('font-script', false)
            ->assertSee('data-faq-list', false)
            ->assertSee('data-informational-hero-image', false)
            ->assertDontSee('id="faq-heading"', false)
            ->assertDontSee('Everything you need to know before choosing')
            ->assertSee('Still have a question?')
            ->assertSeeInOrder([
                'How do I find my press-on nail size?',
                'How can I make sure my measurements are accurate?',
                'Does the recommended size guarantee a perfect fit?',
                'How do I apply press-on nails?',
                'How do I remove and reuse my press-on nails?',
                'Are all designs available in every size?',
                'Can I request a custom design?',
                'Do we offer refunds?',
            ]);

        $this->assertSame(8, substr_count($response->getContent(), 'data-faq-item'));
    }

    public function test_about_and_faq_display_only_their_assigned_active_banner(): void
    {
        HeroBanner::factory()->create([
            'image_path' => 'banners/about.jpg',
            'placement' => 'about_us',
            'sequence' => 1,
        ]);
        HeroBanner::factory()->create([
            'image_path' => 'banners/faq.jpg',
            'placement' => 'faq',
            'sequence' => 1,
        ]);
        HeroBanner::factory()->inactive()->create([
            'image_path' => 'banners/inactive-about.jpg',
            'placement' => 'about_us',
        ]);

        $aboutResponse = $this->get(route('about'))->assertOk()
            ->assertSee('banners/about.jpg')
            ->assertDontSee('banners/faq.jpg')
            ->assertDontSee('banners/inactive-about.jpg');
        $faqResponse = $this->get(route('faq'))->assertOk()
            ->assertSee('banners/faq.jpg')
            ->assertDontSee('banners/about.jpg')
            ->assertDontSee('banners/inactive-about.jpg');

        $this->assertStringNotContainsString('images/hero-banner.png', $aboutResponse->getContent());
        $this->assertStringNotContainsString('images/hero-banner.png', $faqResponse->getContent());
    }

    public function test_about_and_faq_fall_back_when_no_active_placement_banner_exists(): void
    {
        HeroBanner::factory()->inactive()->create(['placement' => 'about_us']);
        HeroBanner::factory()->create(['placement' => 'home_hero']);

        $this->get(route('about'))->assertOk()->assertSee('images/hero-banner.png');
        $this->get(route('faq'))->assertOk()->assertSee('images/hero-banner.png');
    }

    public function test_shipping_policy_uses_the_live_zone_rates_and_real_order_statuses(): void
    {
        $this->get(route('shipping.policy'))
            ->assertOk()
            ->assertSee('Shipping Policy')
            ->assertSee('font-open-sans', false)
            ->assertSee('7–15 days')
            ->assertSee('Rp 15.000')
            ->assertSee('Rp 20.000')
            ->assertSee('Pending')
            ->assertSee('Processing')
            ->assertSee('Shipped')
            ->assertSee('Completed')
            ->assertDontSee('Packing')
            ->assertDontSee('Done:');
    }

    public function test_shipping_policy_reflects_admin_rate_overrides(): void
    {
        Setting::putMany([
            'shipping.java_cost' => '27500',
            'shipping.outside_java_cost' => '35000',
        ]);

        $this->get(route('shipping.policy'))
            ->assertOk()
            ->assertSee('Rp 27.500')
            ->assertSee('Rp 35.000')
            ->assertDontSee('Rp 20.000');
    }

    public function test_informational_pages_are_available_in_indonesian(): void
    {
        $this->withSession(['locale' => 'id'])
            ->get(route('about'))
            ->assertOk()
            ->assertSee('Kenapa Laverie?')
            ->assertSee('bahasa Prancis untuk kehidupan');

        $this->withSession(['locale' => 'id'])
            ->get(route('faq'))
            ->assertOk()
            ->assertDontSee('id="faq-heading"', false)
            ->assertSee('Bagaimana cara menemukan ukuran press-on nails saya?');

        $this->withSession(['locale' => 'id'])
            ->get(route('shipping.policy'))
            ->assertOk()
            ->assertSee('Kebijakan Pengiriman')
            ->assertSee('Rp 20.000');
    }

    public function test_footer_and_hamburger_navigation_link_to_the_new_pages(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('about'), false)
            ->assertSee(route('faq'), false)
            ->assertSee(route('shipping.policy'), false)
            ->assertSee('About Us')
            ->assertSee('Shipping Policy');
    }
}
