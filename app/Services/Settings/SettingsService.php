<?php

namespace App\Services\Settings;

class SettingsService
{
    protected SettingsResolver $resolver;

    public function __construct(SettingsResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function get($key, $default = null)
    {
        return $this->resolver->get($key, $default);
    }

    public function set($key, $value)
    {
    }
}
