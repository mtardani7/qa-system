<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource; use Illuminate\Http\Request;
class CompanyResource extends JsonResource { public function toArray(Request $request):array{return ['id'=>$this->id,'code'=>$this->code,'name'=>$this->name,'address'=>$this->address,'logo_path'=>$this->logo_path,'timezone'=>$this->timezone,'is_active'=>$this->is_active,'created_at'=>$this->created_at?->toISOString()];} }
