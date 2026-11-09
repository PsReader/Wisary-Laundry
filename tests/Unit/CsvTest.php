<?php

namespace Tests\Unit;

use App\Support\Csv;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{
    public function test_formula_like_cells_are_prefixed(): void
    {
        $callback = Csv::streamDownload('x.csv', ['Nama'], [
            ['=SUM(1;2)'],
            ['+1'],
            ['-1'],
            ['@cmd'],
            ['Budi'],
        ]);

        ob_start();
        $callback();
        $output = (string) ob_get_clean();

        $this->assertStringContainsString("'=SUM(1;2)", $output);
        $this->assertStringContainsString("'+1", $output);
        $this->assertStringContainsString("'-1", $output);
        $this->assertStringContainsString("'@cmd", $output);
        $this->assertStringContainsString('Budi', $output);
        $this->assertStringNotContainsString("'Budi", $output);
    }
}
