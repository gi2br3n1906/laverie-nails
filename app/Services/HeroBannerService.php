<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HeroBanner;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HeroBannerService
{
    private const DISK = 'public';

    public function __construct(private readonly ConnectionInterface $database) {}

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes, UploadedFile $image): HeroBanner
    {
        $path = $this->storeImage($image);

        try {
            return $this->database->transaction(fn (): HeroBanner => HeroBanner::query()->create([
                ...$attributes,
                'image_path' => $path,
            ]));
        } catch (Throwable $exception) {
            Storage::disk(self::DISK)->delete($path);
            throw $exception;
        }
    }

    /** @param array<string, mixed> $attributes */
    public function update(HeroBanner $banner, array $attributes, ?UploadedFile $image): HeroBanner
    {
        $oldPath = $banner->image_path;
        $newPath = $image?->store('banners', self::DISK);

        try {
            $updated = $this->database->transaction(function () use ($banner, $attributes, $newPath): HeroBanner {
                $banner->update([
                    ...$attributes,
                    ...($newPath === null ? [] : ['image_path' => $newPath]),
                ]);

                return $banner->refresh();
            });
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk(self::DISK)->delete($newPath);
            }
            throw $exception;
        }

        if ($newPath !== null) {
            Storage::disk(self::DISK)->delete($oldPath);
        }

        return $updated;
    }

    public function delete(HeroBanner $banner): void
    {
        $path = $banner->image_path;
        $this->database->transaction(fn () => $banner->delete());
        Storage::disk(self::DISK)->delete($path);
    }

    private function storeImage(UploadedFile $image): string
    {
        return $image->store('banners', self::DISK);
    }
}
