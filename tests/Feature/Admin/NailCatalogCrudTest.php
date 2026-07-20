<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\NailCatalog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NailCatalogCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_access_catalog_management(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->withRole(UserRole::Admin)->create();

        $this->get(route('admin.catalogs.index'))->assertRedirect(route('login'));
        $this->actingAs($user)->get(route('admin.catalogs.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.catalogs.index'))->assertOk();
    }

    public function test_admin_can_create_an_active_laverie_catalog_with_images(): void
    {
        Storage::fake('public');
        $admin = User::factory()->withRole(UserRole::Admin)->create();

        $response = $this->actingAs($admin)->post(route('admin.catalogs.store'), [
            'title' => 'Laverie Rose Petal Set',
            'description' => 'A delicate Laverie press-on nail collection.',
            'price' => '175000.00',
            'size' => 'M',
            'is_active' => '1',
            'images' => [
                UploadedFile::fake()->image('rose-one.jpg'),
                UploadedFile::fake()->image('rose-two.png'),
            ],
        ]);

        $catalog = NailCatalog::query()->sole();
        $response->assertRedirect(route('admin.catalogs.show', $catalog));
        $this->assertSame('M', $catalog->size->value);
        $this->assertTrue($catalog->is_active);
        $this->assertCount(2, $catalog->images);
        foreach ($catalog->images as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_admin_catalog_validation_rejects_invalid_size_price_and_images(): void
    {
        Storage::fake('public');
        $admin = User::factory()->withRole(UserRole::Admin)->create();

        $this->actingAs($admin)->post(route('admin.catalogs.store'), [
            'title' => '',
            'description' => '',
            'price' => '-1',
            'size' => 'XL',
            'images' => [UploadedFile::fake()->create('catalog.pdf', 10, 'application/pdf')],
        ])->assertSessionHasErrors(['title', 'description', 'price', 'size', 'images.0']);

        $this->assertDatabaseCount('nail_catalogs', 0);
    }

    public function test_admin_can_replace_images_update_and_deactivate_catalog_directly(): void
    {
        Storage::fake('public');
        $admin = User::factory()->withRole(UserRole::Admin)->create();
        Storage::disk('public')->put('catalogs/old.jpg', 'old');
        $catalog = NailCatalog::factory()->create(['images' => ['catalogs/old.jpg']]);

        $this->actingAs($admin)->put(route('admin.catalogs.update', $catalog), [
            'title' => 'Updated Laverie Set',
            'description' => 'Updated single-vendor description.',
            'price' => '190000',
            'size' => 'L',
            'images' => [UploadedFile::fake()->image('replacement.jpg')],
        ])->assertRedirect(route('admin.catalogs.show', $catalog));

        $catalog->refresh();
        $this->assertSame('L', $catalog->size->value);
        $this->assertFalse($catalog->is_active);
        Storage::disk('public')->assertMissing('catalogs/old.jpg');
        Storage::disk('public')->assertExists($catalog->images[0]);
        $this->get(route('products.show', $catalog))->assertNotFound();
    }

    public function test_admin_can_delete_catalog_and_images(): void
    {
        Storage::fake('public');
        $admin = User::factory()->withRole(UserRole::Admin)->create();
        Storage::disk('public')->put('catalogs/delete-me.jpg', 'image');
        $catalog = NailCatalog::factory()->create(['images' => ['catalogs/delete-me.jpg']]);

        $this->actingAs($admin)->delete(route('admin.catalogs.destroy', $catalog))
            ->assertRedirect(route('admin.catalogs.index'));

        $this->assertModelMissing($catalog);
        Storage::disk('public')->assertMissing('catalogs/delete-me.jpg');
    }
}
