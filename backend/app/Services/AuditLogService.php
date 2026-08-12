<?php
namespace App\Services;
use App\Models\AuditLog; use Illuminate\Database\Eloquent\Model; use Illuminate\Support\Facades\Auth; use Illuminate\Support\Facades\Request;
class AuditLogService { public function record(string $action, ?Model $model=null, ?array $old=null, ?array $new=null):AuditLog{return AuditLog::create(['user_id'=>Auth::id(),'action'=>$action,'ip_address'=>Request::ip(),'auditable_type'=>$model?->getMorphClass(),'auditable_id'=>$model?->getKey(),'old_values'=>$old,'new_values'=>$new]);} }
