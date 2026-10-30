<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use App\Services\ShippingRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_update_shipping_rates(): void
    {
        $admin = User::factory()->create(['roles' => [UserRole::Admin->value]]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Pengaturan Ongkir')
            ->assertSee('15000')
            ->assertSee('20000');

        $this->actingAs($admin)
            ->patch(route('admin.settings.update'), [
                'java_cost' => 25000,
                'outside_java_cost' => 30000,
            ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('status');

        $this->assertSame('25000', Setting::get('shipping.java_cost'));
        $this->assertSame('30000', Setting::get('shipping.outside_java_cost'));

        $service = app(ShippingRateService::class);

        $this->assertSame(25000, $service->quote('31')['cost']);
        $this->assertSame('outside_java', $service->quote('51')['zone']->value);
        $this->assertSame(30000, $service->quote('51')['cost']);
    }

    public function test_non_admin_cannot_access_shipping_settings(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.settings.edit'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.settings.update'), [
                'java_cost' => 25000,
                'outside_java_cost' => 30000,
            ])
            ->assertForbidden();
    }

    public function test_shipping_rates_must_be_valid_non_negative_amounts(): void
    {
        $admin = User::factory()->create(['roles' => [UserRole::Admin->value]]);

        $this->actingAs($admin)
            ->from(route('admin.settings.edit'))
            ->patch(route('admin.settings.update'), [
                'java_cost' => -1,
                'outside_java_cost' => 'gratis',
            ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors(['java_cost', 'outside_java_cost']);
    }
}
