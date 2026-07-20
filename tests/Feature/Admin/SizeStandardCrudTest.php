<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\SizeStandard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SizeStandardCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_size_standard_management(): void
    {
        $this->get(route('admin.size-standards.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_access_or_mutate_size_standards(): void
    {
        $user = User::factory()->create();
        $standard = SizeStandard::factory()->create();

        $this->actingAs($user)->get(route('admin.size-standards.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.size-standards.create'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.size-standards.show', $standard))->assertForbidden();
        $this->actingAs($user)->get(route('admin.size-standards.edit', $standard))->assertForbidden();
        $this->actingAs($user)->post(route('admin.size-standards.store'), $this->validPayload('S'))->assertForbidden();
        $this->actingAs($user)->put(route('admin.size-standards.update', $standard), $this->validPayload('M'))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.size-standards.destroy', $standard))->assertForbidden();
    }

    public function test_admin_can_list_and_read_size_standards(): void
    {
        $admin = $this->admin();
        $standard = SizeStandard::factory()->create([
            'size_name' => 'XS',
            'jempol' => 14.0,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.size-standards.index'))
            ->assertOk()
            ->assertSee('XS')
            ->assertSee('14.0');

        $this->actingAs($admin)
            ->get(route('admin.size-standards.show', $standard))
            ->assertOk()
            ->assertSee('XS')
            ->assertSee('14.0');
    }

    public function test_admin_can_render_create_and_edit_forms(): void
    {
        $admin = $this->admin();
        $standard = SizeStandard::factory()->create(['size_name' => 'XS']);

        $this->actingAs($admin)
            ->get(route('admin.size-standards.create'))
            ->assertOk()
            ->assertSee('Create size standard');

        $this->actingAs($admin)
            ->get(route('admin.size-standards.edit', $standard))
            ->assertOk()
            ->assertSee('Edit XS');
    }

    public function test_admin_can_create_a_size_standard(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(
            route('admin.size-standards.store'),
            $this->validPayload('XS'),
        );

        $standard = SizeStandard::query()->sole();

        $response->assertRedirect(route('admin.size-standards.show', $standard));
        $this->assertSame('XS', $standard->size_name);
        $this->assertSame('14.0', $standard->jempol);
        $this->assertSame('1.0', $standard->tolerance);
        $this->assertTrue($standard->is_active);
    }

    public function test_admin_create_requires_a_canonical_unique_size_and_valid_millimeters(): void
    {
        $admin = $this->admin();
        SizeStandard::factory()->create(['size_name' => 'XS']);

        $response = $this->actingAs($admin)->post(route('admin.size-standards.store'), [
            'size_name' => 'XL',
            'jempol' => -0.1,
            'telunjuk' => 25.1,
            'tengah' => 'not-numeric',
            'manis' => null,
            'kelingking' => 8.25,
            'tolerance' => 25.1,
            'is_active' => 'not-boolean',
        ]);

        $response->assertSessionHasErrors([
            'size_name',
            'jempol',
            'telunjuk',
            'tengah',
            'manis',
            'kelingking',
            'tolerance',
            'is_active',
        ]);

        $duplicateResponse = $this->actingAs($admin)->post(
            route('admin.size-standards.store'),
            $this->validPayload('XS'),
        );

        $duplicateResponse->assertSessionHasErrors('size_name');
        $this->assertDatabaseCount('size_standards', 1);
    }

    public function test_admin_can_update_a_size_standard(): void
    {
        $admin = $this->admin();
        $standard = SizeStandard::factory()->create(['size_name' => 'XS']);

        $response = $this->actingAs($admin)->put(
            route('admin.size-standards.update', $standard),
            $this->validPayload('S', 15.0),
        );

        $response->assertRedirect(route('admin.size-standards.show', $standard));

        $standard->refresh();

        $this->assertSame('S', $standard->size_name);
        $this->assertSame('15.0', $standard->jempol);
        $this->assertSame('1.0', $standard->tolerance);
        $this->assertTrue($standard->is_active);
    }

    public function test_admin_can_deactivate_a_size_standard_when_checkbox_is_absent(): void
    {
        $admin = $this->admin();
        $standard = SizeStandard::factory()->create([
            'size_name' => 'XS',
            'is_active' => true,
        ]);
        $payload = $this->validPayload('XS');
        unset($payload['is_active']);

        $this->actingAs($admin)
            ->put(route('admin.size-standards.update', $standard), $payload)
            ->assertRedirect(route('admin.size-standards.show', $standard));

        $this->assertFalse($standard->refresh()->is_active);
    }

    public function test_admin_can_delete_a_size_standard(): void
    {
        $admin = $this->admin();
        $standard = SizeStandard::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.size-standards.destroy', $standard))
            ->assertRedirect(route('admin.size-standards.index'));

        $this->assertModelMissing($standard);
    }

    private function admin(): User
    {
        return User::factory()->withRole(UserRole::Admin)->create();
    }

    /**
     * @return array<string, bool|float|string>
     */
    private function validPayload(string $sizeName, float $jempol = 14.0): array
    {
        return [
            'size_name' => $sizeName,
            'jempol' => $jempol,
            'telunjuk' => 10.0,
            'tengah' => 11.0,
            'manis' => 10.0,
            'kelingking' => 8.0,
            'tolerance' => 1.0,
            'is_active' => true,
        ];
    }
}
