<?php

declare(strict_types=1);

namespace App\Domain\Config\Models;

use App\Shared\Models\BaseModel;
use Illuminate\Support\Facades\Cache;

final class SystemConfig extends BaseModel
{
    protected $fillable = [
        'group',
        'key',
        'name',
        'description',
        'type',
        'value',
        'default_value',
        'validation_rules',
        'is_sensitive',
    ];

    public static function getValue(string $group, string $key, mixed $default = null): mixed
    {
        return Cache::remember(
            "system_config:{$group}:{$key}",
            now()->addMinutes(30),
            function () use ($group, $key, $default) {
                $config = self::query()
                    ->where('group', $group)
                    ->where('key', $key)
                    ->first();

                if (! $config) {
                    return $default;
                }

                return self::castValue($config->value ?? $config->default_value, $config->type);
            }
        );
    }

    public static function setValue(string $group, string $key, mixed $value): void
    {
        self::query()
            ->where('group', $group)
            ->where('key', $key)
            ->update(['value' => (string) $value]);

        self::clearCache($group, $key);
    }

    public static function clearCache(string $group, string $key): void
    {
        Cache::forget("system_config:{$group}:{$key}");
    }

    public static function clearGroupCache(string $group): void
    {
        $keys = self::query()->where('group', $group)->pluck('key');
        foreach ($keys as $key) {
            self::clearCache($group, $key);
        }
    }

    public function getTypedValueAttribute(): mixed
    {
        return self::castValue($this->value ?? $this->default_value, $this->type);
    }

    public function getDisplayValueAttribute(): string
    {
        if ($this->is_sensitive && $this->value) {
            return str_repeat('*', 8);
        }

        return (string) ($this->value ?? $this->default_value ?? '');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'validation_rules' => 'array',
            'is_sensitive' => 'boolean',
        ];
    }

    private static function castValue(mixed $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'integer', 'int' => (int) $value,
            'float', 'decimal' => (float) $value,
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => is_array($value) ? $value : json_decode((string) $value, true),
            default => (string) $value,
        };
    }
}
