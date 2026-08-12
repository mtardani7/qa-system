<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        if (! Auth::attempt($request->only('email', 'password'))) return ApiResponse::error('Invalid credentials.', ['email' => ['The provided credentials are incorrect.']], 422);
        $user = $request->user();
        return ApiResponse::success($this->authPayload($user, $user->createToken($request->input('device_name', 'qms-client'))->plainTextToken), 'Authenticated successfully.');
    }

    public function me() { $user = request()->user(); return ApiResponse::success($this->authPayload($user), 'Authenticated user retrieved.'); }

    public function logout() { request()->user()->currentAccessToken()?->delete(); return ApiResponse::success(null, 'Logged out successfully.'); }

    private function authPayload($user, ?string $token = null): array { return ['token' => $token, 'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'employee_number' => $user->employee_number, 'is_qa_checker' => (bool) $user->is_qa_checker, 'plant_ids' => $user->plants()->pluck('plants.id')->values()->all()], 'roles' => $user->getRoleNames()->values()->all(), 'permissions' => $user->getAllPermissions()->pluck('name')->values()->all()]; }
}
