<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PresentationCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class PresentationCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_an_isolated_presentable_catalog_with_local_artwork_and_coupon(): void
    {
        Storage::fake('public');
        Category::factory()->create(['name' => 'Classy', 'slug' => 'classy']);

        $this->seed(PresentationCatalogSeeder::class);

        $this->assertDatabaseCount('categories', 6);
        $this->assertDatabaseCount('products', 8);
        $this->assertDatabaseCount('product_images', 8);
        $this->assertDatabaseHas('categories', ['name' => 'Demo Classy', 'slug' => 'demo-classy']);
        $this->assertDatabaseHas('categories', ['name' => 'Classy', 'slug' => 'classy']);
        $this->assertDatabaseHas('coupons', [
            'code' => 'PRESENTASI10',
            'discount_percentage' => 10,
            'is_active' => true,
        ]);

        $product = Product::query()->where('slug', 'demo-presentation-pearl-muse')->with('category', 'primaryImage')->sole();
        $this->assertSame('Pearl Muse', $product->name);
        $this->assertSame('Demo Classy', $product->category->name);
        $this->assertSame('189000.00', $product->price);
        $this->assertSame(12, $product->stock);
        $this->assertSame(['XS', 'S', 'M', 'L'], $product->available_sizes);
        $this->assertSame(['Short', 'Medium', 'Long'], $product->available_lengths);
        $this->assertTrue($product->is_active);
        $this->assertSame('demo-products/demo-presentation-pearl-muse.svg', $product->primaryImage->image_path);
        $this->assertTrue(Storage::disk('public')->exists($product->primaryImage->image_path));

        foreach (Product::query()->where('slug', 'like', 'demo-presentation-%')->get() as $demoProduct) {
            $this->assertNotEmpty($demoProduct->available_sizes, "{$demoProduct->slug} should have selectable standard sizes.");
            $this->assertNotEmpty($demoProduct->available_lengths, "{$demoProduct->slug} should have selectable lengths.");
            $this->assertNotNull($demoProduct->primaryImage, "{$demoProduct->slug} should have a presentation thumbnail.");
            $svg = Storage::disk('public')->get($demoProduct->primaryImage->image_path);
            $this->assertStringNotContainsString("\0", $svg);
            $this->assertTrue((new \DOMDocument)->loadXML($svg), "{$demoProduct->slug} should have valid SVG artwork.");
        }
    }

    public function test_demo_seeder_is_repeatable_and_does_not_overwrite_existing_catalog_or_coupons(): void
    {
        Storage::fake('public');
        $existingCategory = Category::factory()->create(['name' => 'Original category', 'slug' => 'demo-classy']);
        $existingProduct = Product::factory()->create([
            'category_id' => $existingCategory->id,
            'name' => 'My original design',
            'slug' => 'demo-presentation-pearl-muse',
            'price' => '245000.00',
            'stock' => 3,
        ]);
        $existingCoupon = Coupon::query()->create([
            'code' => 'PRESENTASI10',
            'discount_percentage' => 25,
            'is_active' => false,
        ]);

        $this->seed(PresentationCatalogSeeder::class);
        $this->seed(PresentationCatalogSeeder::class);

        $this->assertDatabaseCount('products', 8);
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('coupons', 1);
        $this->assertDatabaseCount('product_images', 7);
        $this->assertSame('My original design', $existingProduct->refresh()->name);
        $this->assertSame('245000.00', $existingProduct->refresh()->price);
        $this->assertSame($existingCategory->id, $existingProduct->refresh()->category_id);
        $this->assertSame(25, $existingCoupon->refresh()->discount_percentage);
        $this->assertFalse($existingCoupon->refresh()->is_active);
    }

    public function test_demo_seeder_refuses_to_run_in_production(): void
    {
        config(['app.env' => 'production']);
        $this->app->instance('env', 'production');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Presentation catalog seeding is not allowed in production.');

        app(PresentationCatalogSeeder::class)->run();
    }

    public function test_regular_database_seeder_does_not_load_demo_catalog_or_coupon(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('categories', 0);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('coupons', 0);
    }
}
