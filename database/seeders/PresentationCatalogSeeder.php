<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\CatalogReview;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PresentationCatalogSeeder extends Seeder
{
    /** @var list<array{name: string, slug: string}> */
    private const CATEGORIES = [
        ['name' => 'Classy', 'slug' => 'classy'],
        ['name' => 'Coquette', 'slug' => 'coquette'],
        ['name' => 'Y2K', 'slug' => 'y2k'],
        ['name' => 'Floral', 'slug' => 'floral'],
        ['name' => 'Grunge', 'slug' => 'grunge'],
    ];

    /** @var list<array{name: string, slug: string, description: string, legacy_description: string, image_asset: string, category: string, price: int, stock: int, sizes: list<string>, lengths: list<string>}> */
    private const PRODUCTS = [
        [
            'name' => 'Pearl Muse',
            'slug' => 'demo-presentation-pearl-muse',
            'description' => 'Press-on bernuansa nude milky dengan hasil glossy dan aksen mutiara yang memberi tampilan elegan untuk acara maupun pemakaian harian.',
            'legacy_description' => 'An elegant milky-white set with pearl accents and delicate hand-painted details for polished everyday looks.',
            'image_asset' => 'pearl-muse.jpg',
            'category' => 'classy',
            'price' => 189000,
            'stock' => 12,
            'sizes' => ['XS', 'S', 'M', 'L'],
            'lengths' => ['Short', 'Medium', 'Long'],
        ],
        [
            'name' => 'Satin Bow',
            'slug' => 'demo-presentation-satin-bow',
            'description' => 'Warna soft blush dengan kilau shimmer halus untuk tampilan manis dan rapi. Mudah dipadukan dengan gaya pastel maupun acara kasual.',
            'legacy_description' => 'Soft blush nails finished with miniature ribbon details and a glossy salon-style top coat.',
            'image_asset' => 'satin-bow.jpg',
            'category' => 'coquette',
            'price' => 179000,
            'stock' => 9,
            'sizes' => ['XS', 'S', 'M', 'L'],
            'lengths' => ['Short', 'Medium'],
        ],
        [
            'name' => 'Pixel Star',
            'slug' => 'demo-presentation-pixel-star',
            'description' => 'Perpaduan biru dan lavender dengan aksen grafis berkilau, memberi sentuhan Y2K yang playful tanpa terlihat terlalu ramai.',
            'legacy_description' => 'A playful Y2K mix of chrome stars, translucent accents, and cool lavender tones.',
            'image_asset' => 'pixel-star.jpg',
            'category' => 'y2k',
            'price' => 199000,
            'stock' => 8,
            'sizes' => ['S', 'M', 'L'],
            'lengths' => ['Medium', 'Long'],
        ],
        [
            'name' => 'Daisy Daydream',
            'slug' => 'demo-presentation-daisy-daydream',
            'description' => 'Dasar nude glossy dengan aksen floral bernuansa emas, cocok untuk tampilan feminin yang tetap lembut.',
            'legacy_description' => 'Creamy nude press-ons decorated with tiny white daisies and sunny yellow centers.',
            'image_asset' => 'daisy-daydream.jpg',
            'category' => 'floral',
            'price' => 169000,
            'stock' => 14,
            'sizes' => ['XS', 'S', 'M', 'L'],
            'lengths' => ['Short', 'Medium', 'Long'],
        ],
        [
            'name' => 'Velvet Thorn',
            'slug' => 'demo-presentation-velvet-thorn',
            'description' => 'Merah cherry glossy dipadukan garis gelombang putih dan sentuhan glitter. Pilihan statement untuk malam atau acara spesial.',
            'legacy_description' => 'Deep burgundy and charcoal tones with subtle botanical linework for a moody statement set.',
            'image_asset' => 'velvet-thorn.jpg',
            'category' => 'grunge',
            'price' => 195000,
            'stock' => 6,
            'sizes' => ['S', 'M', 'L'],
            'lengths' => ['Medium', 'Long'],
        ],
        [
            'name' => 'Everyday French',
            'slug' => 'demo-presentation-everyday-french',
            'description' => 'Gaya French tip klasik dengan dasar pink sheer dan ujung putih bersih; tampilan minimal yang mudah dipadukan.',
            'legacy_description' => 'A modern sheer-pink French manicure set designed for a clean, timeless finish.',
            'image_asset' => 'everyday-french.jpg',
            'category' => 'classy',
            'price' => 159000,
            'stock' => 16,
            'sizes' => ['XS', 'S', 'M', 'L'],
            'lengths' => ['Short', 'Medium'],
        ],
        [
            'name' => 'Cherry Charm',
            'slug' => 'demo-presentation-cherry-charm',
            'description' => 'Pink lembut dengan hasil glossy yang terinspirasi suasana cherry blossom—manis, ringan, dan cocok untuk gaya sehari-hari.',
            'legacy_description' => 'A romantic pink set with tiny cherry motifs and a smooth high-shine finish.',
            'image_asset' => 'cherry-charm.jpg',
            'category' => 'coquette',
            'price' => 175000,
            'stock' => 10,
            'sizes' => ['XS', 'S', 'M'],
            'lengths' => ['Short', 'Medium', 'Long'],
        ],
        [
            'name' => 'Chrome Orbit',
            'slug' => 'demo-presentation-chrome-orbit',
            'description' => 'Kilau chrome warna emas memberi efek metalik futuristik. Cocok jadi statement look tanpa tambahan pola yang terlalu ramai.',
            'legacy_description' => 'Reflective silver accents and celestial details bring a futuristic Y2K feel to this reusable set.',
            'image_asset' => 'chrome-orbit.jpg',
            'category' => 'y2k',
            'price' => 209000,
            'stock' => 7,
            'sizes' => ['S', 'M', 'L'],
            'lengths' => ['Medium', 'Long'],
        ],
    ];

    /** @var list<array{product_slug: string, product_name: string, category_slug: string, name: string, email: string, rating: int, comment: string}> */
    private const DEMO_REVIEWS = [
        [
            'product_slug' => 'demo-presentation-pearl-muse',
            'product_name' => 'Pearl Muse',
            'category_slug' => 'demo-classy',
            'name' => 'Alya (Demo)',
            'email' => 'demo.alya@example.test',
            'rating' => 5,
            'comment' => 'Detail mutiaranya terlihat cantik dan ukurannya pas; nyaman dipakai seharian.',
        ],
        [
            'product_slug' => 'demo-presentation-satin-bow',
            'product_name' => 'Satin Bow',
            'category_slug' => 'demo-coquette',
            'name' => 'Nadia (Demo)',
            'email' => 'demo.nadia@example.test',
            'rating' => 5,
            'comment' => 'Warnanya lembut dan hasilnya rapi. Mudah dipadukan untuk acara maupun sehari-hari.',
        ],
        [
            'product_slug' => 'demo-presentation-everyday-french',
            'product_name' => 'Everyday French',
            'category_slug' => 'demo-classy',
            'name' => 'Sari (Demo)',
            'email' => 'demo.sari@example.test',
            'rating' => 5,
            'comment' => 'French tip-nya terlihat bersih, proses pasangnya juga praktis.',
        ],
    ];

    public function run(): void
    {
        if (! $this->demoSeedingAllowed()) {
            throw new RuntimeException('Presentation catalog seeding is not allowed in production.');
        }

        DB::transaction(function (): void {
            $categories = [];
            $products = [];

            foreach (self::CATEGORIES as $category) {
                $slug = 'demo-'.$category['slug'];
                $categories[$category['slug']] = Category::query()->firstOrCreate(
                    ['slug' => $slug],
                    ['name' => 'Demo '.$category['name']],
                );
            }

            foreach (self::PRODUCTS as $productData) {
                $product = Product::query()->firstOrCreate(
                    ['slug' => $productData['slug']],
                    [
                        'category_id' => $categories[$productData['category']]->id,
                        'name' => $productData['name'],
                        'description' => $productData['description'],
                        'price' => $productData['price'],
                        'stock' => $productData['stock'],
                        'available_sizes' => $productData['sizes'],
                        'available_lengths' => $productData['lengths'],
                        'is_active' => true,
                    ],
                );

                $this->syncProductImage($product, $productData['slug'], $productData['image_asset']);
                $products[$productData['slug']] = $product->load(['category', 'primaryImage']);

                if (! $product->wasRecentlyCreated && $product->description === $productData['legacy_description']) {
                    $product->update(['description' => $productData['description']]);
                }
            }

            $this->seedDemoReviews($products);

            Coupon::query()->firstOrCreate(
                ['code' => 'PRESENTASI10'],
                ['discount_percentage' => 10, 'is_active' => true],
            );
        });
    }

    /** @param array<string, Product> $products */
    private function seedDemoReviews(array $products): void
    {
        foreach (self::DEMO_REVIEWS as $reviewData) {
            $product = $products[$reviewData['product_slug']] ?? null;

            if (
                $product === null
                || $product->name !== $reviewData['product_name']
                || $product->category?->slug !== $reviewData['category_slug']
                || $product->primaryImage?->image_path !== 'demo-products/'.$reviewData['product_slug'].'.jpg'
            ) {
                continue;
            }

            $reviewer = User::query()->firstOrCreate(
                ['email' => $reviewData['email']],
                [
                    'name' => $reviewData['name'],
                    'password' => Hash::make(Str::random(48)),
                    'roles' => [UserRole::User->value],
                ],
            );

            if ($reviewer->name !== $reviewData['name'] || $reviewer->roles !== [UserRole::User->value]) {
                continue;
            }

            CatalogReview::query()->firstOrCreate(
                ['product_id' => $product->id, 'user_id' => $reviewer->id],
                [
                    'rating' => $reviewData['rating'],
                    'comment' => $reviewData['comment'],
                    'is_demo' => true,
                ],
            );
        }
    }

    private function demoSeedingAllowed(): bool
    {
        if (! app()->environment('production')) {
            return true;
        }

        if (config('laverie.demo_seed.allowed', false)) {
            return true;
        }

        // Read the live environment too: a config cache must not force this
        // one-off presentation seed to require a full cache rebuild.
        return filter_var(env('LAVERIE_ALLOW_DEMO_SEED', false), FILTER_VALIDATE_BOOLEAN);
    }

    private function syncProductImage(Product $product, string $slug, string $assetName): void
    {
        $imagePath = 'demo-products/'.$slug.'.jpg';
        $legacyImagePath = 'demo-products/'.$slug.'.svg';
        $primaryImage = $product->primaryImage;
        $isLegacyImage = $primaryImage?->image_path === $legacyImagePath;
        $isSeedImage = $product->wasRecentlyCreated || $isLegacyImage || $primaryImage?->image_path === $imagePath;

        if (! $isSeedImage) {
            return;
        }

        $disk = Storage::disk('public');

        if ($product->wasRecentlyCreated || $isLegacyImage || ! $disk->exists($imagePath)) {
            $assetPath = database_path('seeders/assets/products/'.$assetName);
            $contents = is_file($assetPath) ? file_get_contents($assetPath) : false;

            if (! is_string($contents) || $contents === '') {
                throw new RuntimeException("Unable to read presentation product photo: {$assetPath}");
            }

            if (! $disk->put($imagePath, $contents)) {
                throw new RuntimeException("Unable to write presentation product photo: {$imagePath}");
            }
        }

        if ($product->wasRecentlyCreated) {
            $product->images()->create([
                'image_path' => $imagePath,
                'is_primary' => true,
                'sequence' => 1,
            ]);
        } elseif ($isLegacyImage) {
            $primaryImage->update(['image_path' => $imagePath]);
        }
    }
}
