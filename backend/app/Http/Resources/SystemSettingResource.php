<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource; use Illuminate\Http\Request;
class SystemSettingResource extends JsonResource { public function toArray(Request $request):array{return ['id'=>$this->id,'company_id'=>$this->company_id,'key'=>$this->key,'value'=>$this->typedValue(),'type'=>$this->type];} }
