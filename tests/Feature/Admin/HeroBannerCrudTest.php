<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\HeroBanner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HeroBannerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_access_banner_management(): void
    {
        $user = User::factory()->create();

        $this->get('/admin/banners')->assertRedirect(route('login'));
        $this->actingAs($user)->get('/admin/banners')->assertForbidden();
        $this->actingAs($this->admin())->get('/admin/banners')
            ->assertOk()
            ->assertSee('Hero Banners')
            ->assertSee(route('admin.banners.create'), false);
    }

    public function test_admin_can_create_an_ordered_active_banner_image(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())->post('/admin/banners', [
            'image' => UploadedFile::fake()->image('campaign.webp', 1600, 900),
            'sequence' => 7,
            'is_active' => '1',
        ]);

        $banner = HeroBanner::query()->sole();

        $response->assertRedirect(route('admin.banners.index'));
        $this->assertSame(7, $banner->sequence);
        $this->assertTrue($banner->is_active);
        $this->assertStringStartsWith('banners/', $banner->image_path);
        Storage::disk('public')->assertExists($banner->image_path);
    }

    public function test_banner_validation_requires_a_supported_image_and_valid_sequence(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/admin/banners', [
            'image' => UploadedFile::fake()->create('banner.pdf', 100, 'application/pdf'),
            'sequence' => -1,
        ])->assertSessionHasErrors(['image', 'sequence']);

        $this->assertDatabaseCount('hero_banners', 0);
        Storage::disk('public')->assertDirectoryEmpty('banners');
    }

    public function test_admin_can_update_metadata_without_replacing_the_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('banners/original.jpg', 'original');
        $banner = HeroBanner::factory()->create([
            'image_path' => 'banners/original.jpg',
            'sequence' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())->put('/admin/banners/'.$banner->id, [
            'sequence' => 10,
        ])->assertRedirect(route('admin.banners.index'));

        $banner->refresh();
        $this->assertSame('banners/original.jpg', $banner->image_path);
        $this->assertSame(10, $banner->sequence);
        $this->assertFalse($banner->is_active);
        Storage::disk('public')->assertExists('banners/original.jpg');
    }

    public function test_replacing_a_banner_deletes_the_old_image_after_success(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('banners/old.jpg', 'old');
        $banner = HeroBanner::factory()->create(['image_path' => 'banners/old.jpg']);

        $this->actingAs($this->admin())->put('/admin/banners/'.$banner->id, [
            'image' => UploadedFile::fake()->image('replacement.png', 1600, 900),
            'sequence' => 2,
            'is_active' => '1',
        ])->assertRedirect(route('admin.banners.index'));

        $newPath = $banner->refresh()->image_path;
        $this->assertNotSame('banners/old.jpg', $newPath);
        Storage::disk('public')->assertMissing('banners/old.jpg');
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_deleting_a_banner_removes_its_database_record_and_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('banners/delete-me.jpg', 'image');
        $banner = HeroBanner::factory()->create(['image_path' => 'banners/delete-me.jpg']);

        $this->actingAs($this->admin())
            ->delete('/admin/banners/'.$banner->id)
            ->assertRedirect(route('admin.banners.index'));

        $this->assertModelMissing($banner);
        Storage::disk('public')->assertMissing('banners/delete-me.jpg');
    }

    private function admin(): User
    {
        return User::factory()->withRole(UserRole::Admin)->create();
    }
}
