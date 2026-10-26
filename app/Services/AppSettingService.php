<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;

class AppSettingService
{
    protected const CACHE_PREFIX = 'app_setting_';
    protected const CACHE_GROUP_PREFIX = 'app_settings_group_';
    protected const CACHE_TTL = 3600; // 1 hour

    /**
     * Get a setting value by key
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(self::CACHE_PREFIX . $key, self::CACHE_TTL, function () use ($key, $default) {
            $setting = AppSetting::where('key', $key)->first();
            if ($setting) {
                return $setting->value;
            }

            // Also check key with underscore or dash variant
            $altKey = str_contains($key, '_') ? str_replace('_', '-', $key) : str_replace('-', '_', $key);
            $settingAlt = AppSetting::where('key', $altKey)->first();
            if ($settingAlt) {
                return $settingAlt->value;
            }

            return $default;
        });
    }

    /**
     * Get all theme and layout configuration from app_settings
     */
    public function getThemeConfig(): array
    {
        return Cache::remember('theme_config_all', self::CACHE_TTL, function () {
            $settings = AppSetting::whereIn('group', ['theme', 'seo'])
                ->pluck('value', 'key')
                ->toArray();

            $config = [];
            foreach ($settings as $key => $val) {
                $config[$key] = $val;
                $dashKey = str_replace('_', '-', $key);
                $config[$dashKey] = $val;
            }

            return $config;
        });
    }

    /**
     * Get all settings in a specific group as an associative array key => value
     */
    public function getByGroup(string $group): array
    {
        return Cache::remember(self::CACHE_GROUP_PREFIX . $group, self::CACHE_TTL, function () use ($group) {
            return AppSetting::where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Set a setting value
     */
    public function set(string $key, mixed $value): void
    {
        AppSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget(self::CACHE_PREFIX . $key);

        $setting = AppSetting::where('key', $key)->first();
        if ($setting && $setting->group) {
            Cache::forget(self::CACHE_GROUP_PREFIX . $setting->group);
        }
    }

    /**
     * Get all settings with metadata
     */
    public function getAllWithMetadata(): array
    {
        $settings = \Illuminate\Support\Facades\DB::table('app_settings')
            ->where('is_visible', 1)
            ->orderBy('group', 'asc')
            ->orderBy('sort_order', 'asc')
            ->get();

        foreach ($settings as $setting) {
            if (!empty($setting->options)) {
                $setting->options = json_decode($setting->options);
            }
            if (!empty($setting->validation_rules)) {
                $setting->validation_rules = json_decode($setting->validation_rules);
            }
        }

        return $settings->toArray();
    }

    /**
     * Clear all cached settings
     */
    public function clearCache(): void
    {
        Cache::forget('theme_config_all');
        $keys = AppSetting::pluck('key', 'group');
        foreach ($keys as $group => $key) {
            Cache::forget(self::CACHE_PREFIX . $key);
            if ($group) {
                Cache::forget(self::CACHE_GROUP_PREFIX . $group);
            }
        }
    }
}
