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
        $owner = $this->getOwner();
        $ownerId = $owner?->id ?? 'global';

        if (isset($this->cache[$ownerId])) {
            return $this->cache[$ownerId][$key] ?? $default;
        }

        return Cache::remember(
            "settings.$ownerId.$key",
            3600,
            function () use ($key, $default, $owner) {

                if ($owner && method_exists($owner, 'settings')) {
                    $value = $owner->settings()
                        ->where('key', $key)
                        ->value('value');

                    if (!is_null($value)) {
                        return $value;
                    }
                }

                return Context::where('key', $key)->value('value') ?? $default;
            }
        );
    }
    public function all(): array
    {
        $owner = $this->getOwner();
        $ownerId = $owner?->id ?? 'global';

        if (isset($this->cache[$ownerId])) {
            return $this->cache[$ownerId];
        }

        if ($owner && method_exists($owner, 'settings')) {
            $this->cache[$ownerId] = $owner->settings()
                ->pluck('value', 'key')
                ->toArray();
        } else {
            $this->cache[$ownerId] = Context::pluck('value', 'key')->toArray();
        }

        return $this->cache[$ownerId];
    }
}
