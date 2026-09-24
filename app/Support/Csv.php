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
            fputcsv($handle, $headers, self::DELIMITER);

            foreach ($rows as $row) {
                fputcsv($handle, is_array($row) ? $row : iterator_to_array($row), self::DELIMITER);
            }

            fclose($handle);
        };
    }
}
