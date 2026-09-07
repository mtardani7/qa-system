<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductImportTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['NO.', 'MM', 'DESCRIPTION', '/BOX'];
    }

    public function array(): array
    {
        return [];
    }
}