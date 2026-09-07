<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class MachineImportTemplateExport implements FromArray
{
    public function array(): array
    {
        return [
            ['PLANT', 'CODE', 'NAME', 'MACHINE NUMBER', 'DESCRIPTION', 'IS ACTIVE'],
        ];
    }
}
