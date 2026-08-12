<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class DailyReportImportRequest extends FormRequest { public function authorize():bool{return ($this->user()?->can('daily-reports.import')??false)||($this->user()?->can('daily-reports.create')??false);} public function rules():array{return ['plant_id'=>['required','integer','exists:plants,id'],'line_id'=>['nullable','integer','exists:lines,id'],'file'=>['required','file','mimes:xlsx,xls','max:51200']];} }
