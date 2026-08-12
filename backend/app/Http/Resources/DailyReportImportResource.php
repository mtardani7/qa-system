<?php
namespace App\Http\Resources;
use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class DailyReportImportResource extends JsonResource { public function toArray(Request $request):array{return ['id'=>$this->id,'file_name'=>$this->file_name,'status'=>$this->status,'processed_rows'=>(int)$this->processed_rows,'imported_reports'=>(int)$this->imported_reports,'failed_rows'=>(int)$this->failed_rows,'errors'=>$this->errors,'error_message'=>$this->error_message,'created_at'=>$this->created_at?->toISOString()];} }
