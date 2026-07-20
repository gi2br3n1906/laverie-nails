<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_role_protected_routes(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.catalogs.index'))->assertRedirect(route('login'));
    }

    public function test_standard_users_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.catalogs.index'))->assertForbidden();
    }

    public function test_admin_role_can_access_the_admin_area(): void
    {
        $admin = User::factory()->withRole(UserRole::Admin)->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.catalogs.index'))->assertOk();
        $this->assertTrue(Gate::forUser($admin)->allows('access-admin'));
    }

    public function test_only_user_and_admin_role_values_exist(): void
    {
        $this->assertSame(['user', 'admin'], array_column(UserRole::cases(), 'value'));
    }

    public function test_role_checks_support_user_and_admin_json_roles(): void
    {
        $user = User::factory()->create([
            'roles' => [UserRole::User->value, UserRole::Admin->value],
        ]);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
    }
}
