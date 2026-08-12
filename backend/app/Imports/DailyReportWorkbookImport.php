<?php
namespace App\Imports;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
class DailyReportWorkbookImport implements WithMultipleSheets { public function __construct(private readonly DailyReportExcelImport $daily){} public function sheets():array{return [0=>$this->daily,1=>new DailyReportProductMasterImport()];} }
