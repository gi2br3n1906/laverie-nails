<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    private const DISK = 'public';

    public function __construct(private readonly ConnectionInterface $database) {}

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Category
    {
        return $this->database->transaction(fn (): Category => Category::query()->create($attributes));
    }

    /** @param array<string, mixed> $attributes */
    public function update(Category $category, array $attributes): Category
    {
        return $this->database->transaction(function () use ($category, $attributes): Category {
            $category->update($attributes);

            return $category->refresh();
        });
    }

    public function delete(Category $category): void
    {
        $paths = $category->products()->with('images')->get()
            ->flatMap(fn ($product) => $product->images->pluck('image_path'))
            ->all();

        $this->database->transaction(fn () => $category->delete());
        Storage::disk(self::DISK)->delete($paths);
    }
}
