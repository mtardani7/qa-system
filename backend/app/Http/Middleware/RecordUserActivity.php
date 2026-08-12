<?php
namespace App\Http\Middleware;
use App\Models\UserActivity; use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response;
class RecordUserActivity { public function handle(Request $request,Closure $next):Response {$response=$next($request);if($request->user()){try{UserActivity::create(['user_id'=>$request->user()->id,'event'=>$request->method().' '.$request->route()?->getName(),'route'=>$request->path(),'method'=>$request->method(),'ip_address'=>$request->ip(),'user_agent'=>substr((string)$request->userAgent(),0,1000),'metadata'=>['status'=>$response->getStatusCode()]]);}catch(\Throwable $exception){report($exception);}}return $response;} }
