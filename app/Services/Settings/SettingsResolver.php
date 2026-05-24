<?php

namespace App\Services\Settings;

use App\Models\Context;
use App\Traits\ResolvesOwner;
use Illuminate\Support\Facades\Cache;

class SettingsResolver
{
    use ResolvesOwner;

    protected $cache = [];

    public function get($key, $default = null)
    {
        if (!empty($this->cache)) {
            return $this->cache[$key] ?? $default;
        }

        $owner = $this->getOwner();
        $ownerId = $owner?->id ?? 'global';

        return Cache::remember(
            "settings.$ownerId.$key",
            3600,
            function () use ($key, $default, $owner) {

                // tenant
                if ($owner && method_exists($owner, 'settings')) {
                    $value = $owner->settings()
                        ->where('key', $key)
                        ->value('value');

                    if (!is_null($value)) {
                        return $value;
                    }
                }

                // global
                return Context::where('key', $key)->value('value') ?? $default;
            }
        );
    }

    // 🚀 preload همه settings
    public function all(): array
    {
        if (!empty($this->cache)) {
            return $this->cache;
        }

        $owner = $this->resolveOwner();

        // tenant settings
        if ($owner && method_exists($owner, 'settings')) {
            $this->cache = $owner->settings()
                ->pluck('value', 'key')
                ->toArray();
        } else {
            $this->cache = Context::pluck('value', 'key')->toArray();
        }

        return $this->cache;
    }
}
