<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource; use Illuminate\Http\Request;
class AuditLogResource extends JsonResource { public function toArray(Request $request):array{return ['id'=>$this->id,'user_id'=>$this->user_id,'action'=>$this->action,'auditable_type'=>$this->auditable_type,'auditable_id'=>$this->auditable_id,'ip_address'=>$this->ip_address,'old_values'=>$this->old_values,'new_values'=>$this->new_values,'created_at'=>$this->created_at?->toISOString()];} }
