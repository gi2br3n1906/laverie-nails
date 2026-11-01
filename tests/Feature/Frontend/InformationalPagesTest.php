<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

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
            ->assertSee('images/hero-banner.png');
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
