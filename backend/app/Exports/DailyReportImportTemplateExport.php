<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class DailyReportImportTemplateExport implements FromArray
{
    public function array(): array
    {
        return [
            ['', '', '', 'DIISI MANUAL MENGIKUTI FORMAT', '', ' ', '', 'PILIH OTOMATIS'],
            ['NOTE', ':', '', 'DISI DENGAN CARA DIPILIH', '', '', 'BULAN', ':', ''],
            ['', '', '', 'OTOMATIS TERISI', '', '', 'TAHUN', ':', ''],
            ['', '', '', '', '', '', '', ''],
            [],
            [
                'TGL PRODUKSI', 'SHIFT', 'MESIN', 'TYPE PRODUK', 'NO. MM', 'NAMA ITEM',
                'NO. PO', 'QTY /BOX', 'OUTPUT PCS', 'HASIL (BOX)', 'RANGE TEMUAN (BOX)',
                '', '', 'JUMLAH TEMUAN (PCS)', 'TEMUAN SAAT PENGECEKAN', 'KETERANGAN DEFECT',
                'KATEGORI DEFECT', 'RESULT / STATUS', 'CHECKED BY QA 1', 'CHECKED BY QA 2',
            ],
        ];
    }
}