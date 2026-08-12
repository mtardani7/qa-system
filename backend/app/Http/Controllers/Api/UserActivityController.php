<?php
namespace App\Http\Controllers\Api;
use App\Models\UserActivity; use Illuminate\Http\Request;
class UserActivityController extends BaseApiController { public function index(Request $request){abort_unless($request->user()->can('audit.view'),403);$rows=UserActivity::query()->with('user:id,name')->when($request->input('event'),fn($q,$v)=>$q->where('event',$v))->latest('created_at')->paginate(min((int)$request->input('per_page',25),100));return $this->respondSuccess($rows,'User activities retrieved successfully.');} }
