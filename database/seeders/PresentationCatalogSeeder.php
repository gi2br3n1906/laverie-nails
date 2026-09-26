<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

    /** @var array<string, array{background: string, primary: string, secondary: string, accent: string, foreground: string}> */
    private const PALETTES = [
        'classy' => ['background' => '#F2EADF', 'primary' => '#D6B59E', 'secondary' => '#F7E4D8', 'accent' => '#A98770', 'foreground' => '#42372F'],
        'coquette' => ['background' => '#F8E8ED', 'primary' => '#D997AD', 'secondary' => '#F7D8E3', 'accent' => '#A95E79', 'foreground' => '#553747'],
        'y2k' => ['background' => '#E8EBFA', 'primary' => '#A8B8E8', 'secondary' => '#D8C8F2', 'accent' => '#696BB4', 'foreground' => '#303452'],
        'floral' => ['background' => '#EDF0E2', 'primary' => '#C5D39D', 'secondary' => '#F7E8C4', 'accent' => '#7B9360', 'foreground' => '#3C4936'],
        'grunge' => ['background' => '#33323A', 'primary' => '#815461', 'secondary' => '#B88186', 'accent' => '#D6B58D', 'foreground' => '#F4ECE3'],
    ];

    /** @var list<array{name: string, slug: string, description: string, category: string, price: int, stock: int, sizes: list<string>, lengths: list<string>}> */
    private const PRODUCTS = [
        [
            'name' => 'Pearl Muse',
            'slug' => 'demo-presentation-pearl-muse',
            'description' => 'An elegant milky-white set with pearl accents and delicate hand-painted details for polished everyday looks.',
            'category' => 'classy',
            'price' => 189000,
            'stock' => 12,
            'sizes' => ['XS', 'S', 'M', 'L'],
            'lengths' => ['Short', 'Medium', 'Long'],
        ],
        [
            'name' => 'Satin Bow',
            'slug' => 'demo-presentation-satin-bow',
            'description' => 'Soft blush nails finished with miniature ribbon details and a glossy salon-style top coat.',
            'category' => 'coquette',
            'price' => 179000,
            'stock' => 9,
            'sizes' => ['XS', 'S', 'M', 'L'],
            'lengths' => ['Short', 'Medium'],
        ],
        [
            'name' => 'Pixel Star',
            'slug' => 'demo-presentation-pixel-star',
            'description' => 'A playful Y2K mix of chrome stars, translucent accents, and cool lavender tones.',
            'category' => 'y2k',
            'price' => 199000,
            'stock' => 8,
            'sizes' => ['S', 'M', 'L'],
            'lengths' => ['Medium', 'Long'],
        ],
        [
            'name' => 'Daisy Daydream',
            'slug' => 'demo-presentation-daisy-daydream',
            'description' => 'Creamy nude press-ons decorated with tiny white daisies and sunny yellow centers.',
            'category' => 'floral',
            'price' => 169000,
            'stock' => 14,
            'sizes' => ['XS', 'S', 'M', 'L'],
            'lengths' => ['Short', 'Medium', 'Long'],
        ],
        [
            'name' => 'Velvet Thorn',
            'slug' => 'demo-presentation-velvet-thorn',
            'description' => 'Deep burgundy and charcoal tones with subtle botanical linework for a moody statement set.',
            'category' => 'grunge',
            'price' => 195000,
            'stock' => 6,
            'sizes' => ['S', 'M', 'L'],
            'lengths' => ['Medium', 'Long'],
        ],
        [
            'name' => 'Everyday French',
            'slug' => 'demo-presentation-everyday-french',
            'description' => 'A modern sheer-pink French manicure set designed for a clean, timeless finish.',
            'category' => 'classy',
            'price' => 159000,
            'stock' => 16,
            'sizes' => ['XS', 'S', 'M', 'L'],
            'lengths' => ['Short', 'Medium'],
        ],
        [
            'name' => 'Cherry Charm',
            'slug' => 'demo-presentation-cherry-charm',
            'description' => 'A romantic pink set with tiny cherry motifs and a smooth high-shine finish.',
            'category' => 'coquette',
            'price' => 175000,
            'stock' => 10,
            'sizes' => ['XS', 'S', 'M'],
            'lengths' => ['Short', 'Medium', 'Long'],
        ],
        [
            'name' => 'Chrome Orbit',
            'slug' => 'demo-presentation-chrome-orbit',
            'description' => 'Reflective silver accents and celestial details bring a futuristic Y2K feel to this reusable set.',
            'category' => 'y2k',
            'price' => 209000,
            'stock' => 7,
            'sizes' => ['S', 'M', 'L'],
            'lengths' => ['Medium', 'Long'],
        ],
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('Presentation catalog seeding is not allowed in production.');
        }

        DB::transaction(function (): void {
            $categories = [];

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

                if ($product->wasRecentlyCreated) {
                    $imagePath = 'demo-products/'.$productData['slug'].'.svg';
                    $artwork = $this->makeArtwork($productData['name'], self::PALETTES[$productData['category']]);

                    if (! Storage::disk('public')->put($imagePath, $artwork)) {
                        throw new RuntimeException("Unable to write presentation artwork: {$imagePath}");
                    }

                    $product->images()->create([
                        'image_path' => $imagePath,
                        'is_primary' => true,
                        'sequence' => 1,
                    ]);
                }
            }

            Coupon::query()->firstOrCreate(
                ['code' => 'PRESENTASI10'],
                ['discount_percentage' => 10, 'is_active' => true],
            );
        });
    }

    /** @param array{background: string, primary: string, secondary: string, accent: string, foreground: string} $palette */
    private function makeArtwork(string $name, array $palette): string
    {
        $escapedName = htmlspecialchars($name, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $shapes = $this->nailSetArtwork($palette);

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 900" role="img" aria-label="Illustrated demo preview: {$escapedName}">
          <defs>
            <linearGradient id="paper" x1="0" y1="0" x2="1" y2="1"><stop stop-color="{$palette['background']}"/><stop offset="1" stop-color="{$palette['secondary']}"/></linearGradient>
            <linearGradient id="nail" x1="0" y1="0" x2="1" y2="1"><stop stop-color="{$palette['secondary']}"/><stop offset="1" stop-color="{$palette['primary']}"/></linearGradient>
            <filter id="shadow" x="-30%" y="-30%" width="160%" height="170%"><feDropShadow dx="0" dy="18" stdDeviation="18" flood-color="{$palette['foreground']}" flood-opacity=".16"/></filter>
          </defs>
          <rect width="720" height="900" fill="url(#paper)"/>
          <circle cx="596" cy="167" r="120" fill="{$palette['secondary']}" opacity=".72"/>
          <circle cx="114" cy="618" r="146" fill="{$palette['secondary']}" opacity=".55"/>
          <path d="M0 710C140 646 250 774 392 708s224-40 328-2v194H0Z" fill="{$palette['background']}" opacity=".76"/>
          <g filter="url(#shadow)">
            {$shapes}
          </g>
          <text x="56" y="88" fill="{$palette['accent']}" font-family="Arial,sans-serif" font-size="17" font-weight="700" letter-spacing="4">LAVERIE · DEMO PREVIEW</text>
          <text x="56" y="788" fill="{$palette['foreground']}" font-family="Georgia,serif" font-size="43">{$escapedName}</text>
          <text x="58" y="832" fill="{$palette['accent']}" font-family="Arial,sans-serif" font-size="15" font-weight="700" letter-spacing="3">PRESENTATION SAMPLE</text>
        </svg>
        SVG;
    }

    /** @param array{background: string, primary: string, secondary: string, accent: string, foreground: string} $palette */
    private function nailSetArtwork(array $palette): string
    {
        $nails = '';

        for ($index = 0; $index < 5; $index++) {
            $x = 115 + ($index * 100);
            $y = 340 + (abs(2 - $index) * 12);
            $rotation = ($index - 2) * 7;
            $nails .= <<<SVG
            <g transform="rotate({$rotation} {$x} {$y})">
              <rect x="{$x}" y="{$y}" width="70" height="190" rx="35" fill="url(#nail)" stroke="{$palette['accent']}" stroke-opacity=".32" stroke-width="2"/>
              <path d="M{$x} {$y}a35 35 0 0 1 70 0v20h-70Z" fill="{$palette['primary']}" opacity=".7"/>
              <path d="M{$x} {$y}v18h70v-18c0-18-16-32-35-32s-35 14-35 32Z" fill="{$palette['secondary']}" opacity=".72"/>
            </g>
            SVG;
        }

        return $nails;
    }
}
