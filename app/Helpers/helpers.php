<?php

use App\Services\AppSettingService;

if (!function_exists('get_app_setting')) {
    /**
     * Get application setting value by key
     */
    function get_app_setting(string $key, mixed $default = null): mixed
    {
        return app(AppSettingService::class)->get($key, $default);
    }
}

if (!function_exists('get_app_settings_by_group')) {
    /**
     * Get application settings by group
     */
    function get_app_settings_by_group(string $group): array
    {
        return app(AppSettingService::class)->getByGroup($group);
    }
}

if (!function_exists('base_url')) {
    /**
     * CI3 compatibility helper for base_url
     */
    function base_url(?string $path = ''): string
    {
        return url($path ?? '');
    }
}

if (!function_exists('isAvatar')) {
    /**
     * Get avatar URL
     */
    function isAvatar(?string $val): string
    {
        if (!empty($val)) {
            return asset('filemanager/avatar/' . $val);
        }
        return asset('filemanager/avatar/default.png');
    }
}

if (!function_exists('format_indo')) {
    /**
     * Format date to Indonesian format
     */
    function format_indo($date): string
    {
        if (empty($date)) {
            return '';
        }
        $formatToDate = date('Y-m-d', strtotime($date));
        $Bulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

        $tahun = substr($formatToDate, 0, 4);
        $bulan = substr($formatToDate, 5, 2);
        $tgl = substr($formatToDate, 8, 2);

        return $tgl . " " . $Bulan[(int)$bulan - 1] . " " . $tahun;
    }
}

if (!function_exists('format_periode_bulan')) {
    /**
     * Format month and year to Indonesian format
     */
    function format_periode_bulan($date): string
    {
        if (empty($date)) {
            return '';
        }
        $Bulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        $tahun = substr($date, 0, 4);
        $bulan = substr($date, 5, 2);
        return $Bulan[(int)$bulan - 1] . " " . $tahun;
    }
}

if (!function_exists('format_rupiah')) {
    /**
     * Format number to Rupiah currency
     */
    function format_rupiah($angka, $withPrefix = true): string
    {
        $prefix = $withPrefix ? 'Rp ' : '';
        return $prefix . number_format($angka, 0, ',', '.');
    }
}

if (!function_exists('str_limit')) {
    /**
     * String limit helper
     */
    function str_limit($value, $limit = 100, $end = '...'): string
    {
        return \Illuminate\Support\Str::limit($value ?? '', $limit, $end);
    }
}

if (!function_exists('mnow')) {
    /**
     * Current datetime string helper
     */
    function mnow(): string
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('ribuan')) {
    /**
     * Format number with thousand separator
     */
    function ribuan($angka): string
    {
        $num = floatval($angka);
        if ($num == 0) {
            return '0';
        }
        return number_format($num, 0, ',', '.');
    }
}

if (!function_exists('rupiah')) {
    /**
     * Format number as Rupiah currency string
     */
    function rupiah($angka): string
    {
        $num = floatval($angka);
        if ($num == 0) {
            return 'Rp 0';
        }
        return 'Rp ' . number_format($num, 0, ',', '.');
    }
}

if (!function_exists('terbilang')) {
    /**
     * Convert number to Indonesian word representation
     */
    function terbilang($x): string
    {
        $x = intval($x);
        $angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
        if ($x < 12) return " " . $angka[$x];
        elseif ($x < 20) return terbilang($x - 10) . " belas";
        elseif ($x < 100) return terbilang(intval($x / 10)) . " puluh" . terbilang($x % 10);
        elseif ($x < 200) return " seratus" . terbilang($x - 100);
        elseif ($x < 1000) return terbilang(intval($x / 100)) . " ratus" . terbilang($x % 100);
        elseif ($x < 2000) return " seribu" . terbilang($x - 1000);
        elseif ($x < 1000000) return terbilang(intval($x / 1000)) . " ribu" . terbilang($x % 1000);
        elseif ($x < 1000000000) return terbilang(intval($x / 1000000)) . " juta" . terbilang($x % 1000000);
        elseif ($x < 1000000000000) return terbilang(intval($x / 1000000000)) . " milyar" . terbilang($x % 1000000000);
        return "";
    }
}
