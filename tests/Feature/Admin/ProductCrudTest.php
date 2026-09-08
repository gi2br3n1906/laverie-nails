<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_manage_products(): void
    {
        $this->get('/admin/products')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get('/admin/products')->assertForbidden();
        $this->actingAs($this->admin())->get('/admin/products')->assertOk()->assertSee('Kelola Produk');
    }

    public function test_admin_can_create_product_with_multiple_ordered_images_and_a_primary_image(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $this->actingAs($this->admin())->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Pearl Muse',
            'description' => 'Hand-painted pearl press-on nails.',
            'price' => '175000.00',
            'stock' => 12,
            'available_sizes' => ['XS', 'M'],
            'is_active' => '1',
            'images' => [
                UploadedFile::fake()->image('front.jpg'),
                UploadedFile::fake()->image('detail.png'),
            ],
            'new_primary_image_index' => 1,
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::query()->sole();
        $images = $product->images()->orderBy('sequence')->get();
        $this->assertSame('pearl-muse', $product->slug);
        $this->assertSame(['XS', 'M'], $product->available_sizes);
        $this->assertCount(2, $images);
        $this->assertFalse($images[0]->is_primary);
        $this->assertTrue($images[1]->is_primary);
        $images->each(fn (ProductImage $image) => Storage::disk('public')->assertExists($image->image_path));
    }

    public function test_product_validation_rejects_invalid_business_and_image_data(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/admin/products', [
            'category_id' => 999,
            'name' => '',
            'description' => '',
            'price' => -1,
            'stock' => -1,
            'images' => [UploadedFile::fake()->create('manual.pdf', 50, 'application/pdf')],
        ])->assertSessionHasErrors(['category_id', 'name', 'description', 'price', 'stock', 'images.0']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_admin_can_update_product_and_select_an_existing_primary_image(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create(['is_active' => true]);
        $first = ProductImage::factory()->for($product)->primary()->create(['sequence' => 0]);
        $second = ProductImage::factory()->for($product)->create(['sequence' => 1]);

        $this->actingAs($this->admin())->put('/admin/products/'.$product->id, [
            'category_id' => $product->category_id,
            'name' => 'Updated Product',
            'description' => 'Updated description.',
            'price' => '190000',
            'stock' => 4,
            'available_sizes' => ['S', 'L'],
            'primary_image_id' => $second->id,
        ])->assertRedirect(route('admin.products.index'));

        $this->assertFalse($product->refresh()->is_active);
        $this->assertSame('updated-product', $product->slug);
        $this->assertSame(['S', 'L'], $product->available_sizes);
        $this->assertFalse($first->refresh()->is_primary);
        $this->assertTrue($second->refresh()->is_primary);
    }

    public function test_product_form_exposes_canonical_available_size_checkboxes(): void
    {
        Category::factory()->create();

        $content = $this->actingAs($this->admin())->get(route('admin.products.create'))
            ->assertOk()
            ->getContent();

        foreach (['XS', 'S', 'M', 'L'] as $size) {
            $this->assertStringContainsString('name="available_sizes[]"', $content);
            $this->assertStringContainsString('value="'.$size.'"', $content);
        }
    }

    public function test_admin_can_delete_one_image_and_primary_falls_back_to_the_first_remaining_image(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        Storage::disk('public')->put('products/primary.jpg', 'primary');
        Storage::disk('public')->put('products/remaining.jpg', 'remaining');
        $primary = ProductImage::factory()->for($product)->primary()->create(['image_path' => 'products/primary.jpg', 'sequence' => 0]);
        $remaining = ProductImage::factory()->for($product)->create(['image_path' => 'products/remaining.jpg', 'sequence' => 1]);

        $this->actingAs($this->admin())->delete('/admin/products/'.$product->id.'/images/'.$primary->id)
            ->assertRedirect(route('admin.products.edit', $product));

        Storage::disk('public')->assertMissing('products/primary.jpg');
        Storage::disk('public')->assertExists('products/remaining.jpg');
        $this->assertTrue($remaining->refresh()->is_primary);
    }

    public function test_deleting_product_removes_all_database_images_and_files(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        Storage::disk('public')->put('products/one.jpg', 'one');
        Storage::disk('public')->put('products/two.jpg', 'two');
        ProductImage::factory()->for($product)->primary()->create(['image_path' => 'products/one.jpg']);
        ProductImage::factory()->for($product)->create(['image_path' => 'products/two.jpg']);

        $this->actingAs($this->admin())->delete('/admin/products/'.$product->id)
            ->assertRedirect(route('admin.products.index'));

        $this->assertModelMissing($product);
        $this->assertDatabaseCount('product_images', 0);
        Storage::disk('public')->assertMissing(['products/one.jpg', 'products/two.jpg']);
    }

    private function admin(): User
    {
        return User::factory()->withRole(UserRole::Admin)->create();
    }
}
