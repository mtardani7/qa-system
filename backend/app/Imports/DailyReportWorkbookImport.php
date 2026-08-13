<?php
namespace App\Imports;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
class DailyReportWorkbookImport implements WithMultipleSheets, SkipsUnknownSheets { public function __construct(private readonly DailyReportExcelImport $daily){} public function sheets():array{return [0=>$this->daily,1=>new DailyReportProductMasterImport()];} public function onUnknownSheet($sheetName):void{} }
