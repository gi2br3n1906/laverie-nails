<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::query()->where('key', $key)->value('value');

        return $value === null ? $default : $value;
    }

    public static function put(string $key, string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );
    }

    /** @param array<string, string> $values */
    public static function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::put($key, $value);
        }
    }
}
