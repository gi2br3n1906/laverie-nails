<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_view_coupon_management(): void
    {
        $this->get(route('admin.coupons.index'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.coupons.index'))
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->get(route('admin.coupons.index'))
            ->assertOk()
            ->assertSee('Kelola Promo');
    }

    public function test_admin_can_create_and_update_percentage_and_fixed_coupons(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => ' welcome10 ',
            'discount_type' => 'percentage',
            'discount_percentage' => 10,
            'discount_amount' => 0,
            'minimum_order_amount' => 150000,
            'expires_at' => now()->addDays(14)->format('Y-m-d\\TH:i'),
            'is_active' => '1',
        ])->assertRedirect(route('admin.coupons.index'));

        $coupon = Coupon::query()->sole();
        $this->assertSame('WELCOME10', $coupon->code);
        $this->assertSame('percentage', $coupon->discount_type);
        $this->assertSame(10, $coupon->discount_percentage);
        $this->assertSame(150000, $coupon->minimum_order_amount);
        $this->assertTrue($coupon->is_active);

        $this->actingAs($admin)->put(route('admin.coupons.update', $coupon), [
            'code' => 'WELCOME10',
            'discount_type' => 'fixed',
            'discount_percentage' => 0,
            'discount_amount' => 25000,
            'minimum_order_amount' => 200000,
            'expires_at' => '',
            'is_active' => '0',
        ])->assertRedirect(route('admin.coupons.index'));

        $coupon->refresh();
        $this->assertSame('fixed', $coupon->discount_type);
        $this->assertSame(25000, $coupon->discount_amount);
        $this->assertSame(200000, $coupon->minimum_order_amount);
        $this->assertNull($coupon->expires_at);
        $this->assertFalse($coupon->is_active);
    }

    public function test_coupon_code_must_be_unique_and_discount_values_valid_for_type(): void
    {
        Coupon::query()->create([
            'code' => 'EXISTING',
            'discount_type' => 'percentage',
            'discount_percentage' => 10,
            'discount_amount' => 0,
            'minimum_order_amount' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())->post(route('admin.coupons.store'), [
            'code' => 'existing',
            'discount_type' => 'percentage',
            'discount_percentage' => 101,
            'discount_amount' => 1000,
            'minimum_order_amount' => -1,
            'expires_at' => now()->subDay()->format('Y-m-d\\TH:i'),
            'is_active' => '1',
        ])->assertSessionHasErrors(['code', 'discount_percentage', 'minimum_order_amount', 'expires_at']);

        $this->assertDatabaseCount('coupons', 1);
    }

    public function test_admin_can_delete_a_coupon(): void
    {
        $coupon = Coupon::query()->create([
            'code' => 'REMOVE10',
            'discount_type' => 'percentage',
            'discount_percentage' => 10,
            'discount_amount' => 0,
            'minimum_order_amount' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.coupons.destroy', $coupon))
            ->assertRedirect(route('admin.coupons.index'));

        $this->assertModelMissing($coupon);
    }

    private function admin(): User
    {
        return User::factory()->withRole(UserRole::Admin)->create();
    }
}
