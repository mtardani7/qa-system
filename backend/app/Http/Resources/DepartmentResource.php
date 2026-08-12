<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource; use Illuminate\Http\Request;
class DepartmentResource extends JsonResource { public function toArray(Request $request):array{return ['id'=>$this->id,'company_id'=>$this->company_id,'plant_id'=>$this->plant_id,'code'=>$this->code,'name'=>$this->name,'is_active'=>$this->is_active,'created_at'=>$this->created_at?->toISOString()];} }
