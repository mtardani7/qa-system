<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource; use Illuminate\Http\Request;
class HolidayResource extends JsonResource { public function toArray(Request $request):array{return ['id'=>$this->id,'company_id'=>$this->company_id,'plant_id'=>$this->plant_id,'holiday_date'=>$this->holiday_date?->toDateString(),'name'=>$this->name,'is_working_day'=>$this->is_working_day];} }
