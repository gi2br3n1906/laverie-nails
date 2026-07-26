<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductService
{
    private const DISK = 'public';

    public function __construct(private readonly ConnectionInterface $database) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<UploadedFile>  $images
     */
    public function create(array $attributes, array $images, ?int $primaryIndex): Product
    {
        $paths = $this->storeImages($images);

        try {
            return $this->database->transaction(function () use ($attributes, $paths, $primaryIndex): Product {
                $product = Product::query()->create($attributes);
                $selectedIndex = $paths === [] ? null : ($primaryIndex ?? 0);

                foreach ($paths as $index => $path) {
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => $index === $selectedIndex,
                        'sequence' => $index,
                    ]);
                }

                return $product->load(['category', 'images']);
            });
        } catch (Throwable $exception) {
            Storage::disk(self::DISK)->delete($paths);
            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<UploadedFile>  $images
     */
    public function update(
        Product $product,
        array $attributes,
        array $images,
        ?int $primaryImageId,
        ?int $newPrimaryIndex,
    ): Product {
        $paths = $this->storeImages($images);

        try {
            return $this->database->transaction(function () use (
                $product,
                $attributes,
                $paths,
                $primaryImageId,
                $newPrimaryIndex,
            ): Product {
                $product->update($attributes);
                $nextSequence = ((int) $product->images()->max('sequence')) + 1;
                $newImages = collect();

                foreach ($paths as $index => $path) {
                    $newImages->push($product->images()->create([
                        'image_path' => $path,
                        'is_primary' => false,
                        'sequence' => $nextSequence + $index,
                    ]));
                }

                $targetId = $newPrimaryIndex !== null
                    ? $newImages->get($newPrimaryIndex)?->id
                    : $primaryImageId;

                if ($targetId !== null) {
                    $this->setPrimaryImage($product, (int) $targetId);
                } elseif (! $product->images()->where('is_primary', true)->exists()) {
                    $fallbackId = $product->images()->orderBy('sequence')->orderBy('id')->value('id');
                    if ($fallbackId !== null) {
                        $this->setPrimaryImage($product, (int) $fallbackId);
                    }
                }

                return $product->refresh()->load(['category', 'images']);
            });
        } catch (Throwable $exception) {
            Storage::disk(self::DISK)->delete($paths);
            throw $exception;
        }
    }

    public function deleteImage(Product $product, ProductImage $image): void
    {
        abort_unless($image->product_id === $product->id, 404);

        $path = $image->image_path;
        $wasPrimary = $image->is_primary;

        $this->database->transaction(function () use ($product, $image, $wasPrimary): void {
            $image->delete();

            if ($wasPrimary) {
                $fallbackId = $product->images()->orderBy('sequence')->orderBy('id')->value('id');
                if ($fallbackId !== null) {
                    $this->setPrimaryImage($product, (int) $fallbackId);
                }
            }
        });

        Storage::disk(self::DISK)->delete($path);
    }

    public function delete(Product $product): void
    {
        $paths = $product->images()->pluck('image_path')->all();
        $this->database->transaction(fn () => $product->delete());
        Storage::disk(self::DISK)->delete($paths);
    }

    private function setPrimaryImage(Product $product, int $imageId): void
    {
        $product->images()->update(['is_primary' => false]);
        $product->images()->whereKey($imageId)->update(['is_primary' => true]);
    }

    /**
     * @param  list<UploadedFile>  $images
     * @return list<string>
     */
    private function storeImages(array $images): array
    {
        return array_values(array_map(
            fn (UploadedFile $image): string => $image->store('products', self::DISK),
            $images,
        ));
    }
}
