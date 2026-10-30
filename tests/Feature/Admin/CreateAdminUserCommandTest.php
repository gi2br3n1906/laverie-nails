<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_verified_admin_account(): void
    {
        $this->artisan('laverie:create-admin', [
            'email' => 'owner@example.com',
            '--name' => 'Store Owner',
            '--password' => 'correct-horse-battery',
        ])->assertSuccessful();

        $user = User::query()->where('email', 'owner@example.com')->sole();

        $this->assertTrue($user->hasRole(UserRole::Admin));
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('correct-horse-battery', $user->password));
    }

    public function test_it_refuses_to_overwrite_an_existing_account(): void
    {
        User::factory()->create(['email' => 'owner@example.com']);

        $this->artisan('laverie:create-admin', [
            'email' => 'owner@example.com',
            '--password' => 'correct-horse-battery',
        ])->assertFailed();

        $this->assertSame(1, User::query()->where('email', 'owner@example.com')->count());
    }
}
