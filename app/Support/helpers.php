<?php

use Illuminate\Support\Carbon;

if (! function_exists('format_rupiah')) {
    /**
     * Format a numeric value as Indonesian Rupiah.
     * Example: format_rupiah(40000) => "Rp40.000"
     */
    function format_rupiah(int|float $amount): string
    {
        return 'Rp'.number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('format_date')) {
    /**
     * Format tanggal sesuai spesifikasi, contoh: "7 September 2026".
     */
    function format_date(?\Carbon\Carbon $date): string
    {
        if ($date === null) {
            return '';
        }

        return $date->locale('id')->translatedFormat('j F Y');
    }
}

if (! function_exists('format_time_wib')) {
    /**
     * Format waktu WIB sesuai spesifikasi, contoh: "15.00 WIB".
     */
    function format_time_wib(string $hhmm): string
    {
        return str_replace(':', '.', $hhmm).' WIB';
    }
}

if (! function_exists('format_datetime_wib')) {
    /**
     * Format tanggal dan waktu WIB, contoh: "9 September 2026 · 14.05 WIB".
     */
    function format_datetime_wib(?Carbon $datetime): string
    {
        if ($datetime === null) {
            return '';
        }

        return $datetime->locale('id')->translatedFormat('j F Y').' · '.$datetime->format('H.i').' WIB';
    }
}
