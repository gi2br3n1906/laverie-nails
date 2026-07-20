<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NailCatalog;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class NailCatalogService
{
    private const DISK = 'public';

    public function __construct(private readonly ConnectionInterface $database) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<UploadedFile>  $images
     */
    public function create(array $attributes, array $images): NailCatalog
    {
        $paths = $this->storeImages($images);

        try {
            return $this->database->transaction(fn (): NailCatalog => NailCatalog::query()->create([
                ...$attributes,
                'images' => $paths,
            ]));
        } catch (Throwable $exception) {
            Storage::disk(self::DISK)->delete($paths);
            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<UploadedFile>  $images
     */
    public function update(NailCatalog $catalog, array $attributes, array $images): NailCatalog
    {
        $oldPaths = $catalog->images;
        $newPaths = $images === [] ? null : $this->storeImages($images);

        try {
            $updated = $this->database->transaction(function () use ($catalog, $attributes, $newPaths): NailCatalog {
                $catalog->update([
                    ...$attributes,
                    ...($newPaths === null ? [] : ['images' => $newPaths]),
                ]);

                return $catalog->refresh();
            });
        } catch (Throwable $exception) {
            if ($newPaths !== null) {
                Storage::disk(self::DISK)->delete($newPaths);
            }
            throw $exception;
        }

        if ($newPaths !== null) {
            Storage::disk(self::DISK)->delete($oldPaths);
        }

        return $updated;
    }

    public function delete(NailCatalog $catalog): void
    {
        $paths = $catalog->images;
        $this->database->transaction(fn () => $catalog->delete());
        Storage::disk(self::DISK)->delete($paths);
    }

    /**
     * @param  list<UploadedFile>  $images
     * @return list<string>
     */
    private function storeImages(array $images): array
    {
        return array_values(array_map(
            fn (UploadedFile $image): string => $image->store('catalogs', self::DISK),
            $images,
        ));
    }
}
