<?php

namespace App\Models\Concerns;

trait HasTenantSettings
{
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, mixed $value): static
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;

        return $this;
    }

    public function setSettings(array $settings): static
    {
        $this->settings = array_merge($this->settings ?? [], $settings);

        return $this;
    }
}
