<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_access_customer_management(): void
    {
        $this->get('/admin/customers')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get('/admin/customers')->assertForbidden();
        $this->actingAs(User::factory()->withRole(UserRole::Admin)->create())
            ->get('/admin/customers')->assertOk()->assertSeeText('Kelola Pelanggan');
    }

    public function test_admin_list_contains_only_customers_with_order_count_and_paid_spend(): void
    {
        $customer = User::factory()->create([
            'name' => 'Alya Customer',
            'email' => 'alya@example.com',
            'created_at' => now()->subDays(3),
        ]);
        $otherCustomer = User::factory()->create(['name' => 'Bela Customer']);
        $admin = User::factory()->withRole(UserRole::Admin)->create(['name' => 'Hidden Admin']);
        Order::factory()->for($customer)->paid()->create(['grand_total' => 250000]);
        Order::factory()->for($customer)->create(['grand_total' => 900000, 'payment_status' => PaymentStatus::Failed]);

        $this->actingAs($admin)->get('/admin/customers')
            ->assertOk()
            ->assertSeeText('Alya Customer')
            ->assertSeeText('alya@example.com')
            ->assertSeeText('2 pesanan')
            ->assertSeeText('Rp 250.000')
            ->assertSeeText('Bela Customer')
            ->assertDontSeeText('Hidden Admin');
    }
}