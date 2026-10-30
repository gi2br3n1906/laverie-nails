<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEntryPointTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_root_redirects_to_the_orders_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/admin/orders');

        $this->actingAs(User::factory()->withRole(UserRole::Admin)->create())
            ->get('/admin')
            ->assertRedirect('/admin/orders');
    }
}
