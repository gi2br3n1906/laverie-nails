<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SizeStandard;
use Illuminate\Database\Eloquent\Collection;

class SizeStandardService
{
    /**
     * @return Collection<int, SizeStandard>
     */
    public function all(): Collection
    {
        return SizeStandard::query()
            ->orderBy('jempol')
            ->orderBy('id')
            ->get();
    }

    /** @return Collection<int, SizeStandard> */
    public function active(): Collection
    {
        return SizeStandard::query()
            ->where('is_active', true)
            ->orderBy('jempol')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<string, bool|float|int|string>  $attributes
     */
    public function create(array $attributes): SizeStandard
    {
        return SizeStandard::query()->create($attributes);
    }

    /**
     * @param  array<string, bool|float|int|string>  $attributes
     */
    public function update(SizeStandard $sizeStandard, array $attributes): SizeStandard
    {
        $sizeStandard->update($attributes);

        return $sizeStandard->refresh();
    }

    public function delete(SizeStandard $sizeStandard): void
    {
        $sizeStandard->delete();
    }
}
