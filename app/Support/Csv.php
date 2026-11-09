<?php

namespace App\Support;

use Closure;

class Csv
{
    private const DELIMITER = ';';

    private const BOM = "\xEF\xBB\xBF";

    /**
     * Membuat callable untuk response()->streamDownload(). Output UTF-8
     * dengan BOM agar terbuka benar di Excel. Baris ditulis baris per baris
     * sehingga aman untuk dataset besar.
     *
     * @param  array<int, string>  $headers
     */
    public static function streamDownload(string $filename, array $headers, iterable $rows): Closure
    {
        return static function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, self::BOM);
            fputcsv($handle, $headers, self::DELIMITER, '"', '');

            foreach ($rows as $row) {
                $row = is_array($row) ? $row : iterator_to_array($row);
                fputcsv($handle, array_map(fn ($value) => self::sanitize($value), $row), self::DELIMITER, '"', '');
            }

            fclose($handle);
        };
    }

    /**
     * Cegah formula injection: Excel mengeksekusi sel yang diawali karakter ini.
     */
    private static function sanitize(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'".$value : $value;
    }
}
