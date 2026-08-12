<?php
namespace App\Http\Controllers\Api;
use App\Http\Resources\AuditLogResource; use App\Models\AuditLog; use Illuminate\Http\Request;
class AuditLogController extends BaseApiController { public function index(Request $request){abort_unless($request->user()->can('audit.view'),403);$rows=AuditLog::query()->with('user:id,name')->when($request->input('action'),fn($q,$v)=>$q->where('action',$v))->when($request->input('user_id'),fn($q,$v)=>$q->where('user_id',$v))->latest()->paginate(min((int)$request->input('per_page',25),100));return $this->respondWithResource(AuditLogResource::collection($rows),'Audit logs retrieved successfully.');} }
