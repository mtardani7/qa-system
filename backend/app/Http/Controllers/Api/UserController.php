<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UserRequest;
use App\Http\Requests\UserResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseApiController
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $users = User::query()
            ->with('plants')
            ->when($validated['search'] ?? null, function ($query, string $search): void {
                $operator = $query->getModel()->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
                $query->where(function ($inner) use ($search, $operator): void {
                    $inner->where('name', $operator, "%{$search}%")
                        ->orWhere('employee_number', $operator, "%{$search}%")
                        ->orWhere('email', $operator, "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($validated['per_page'] ?? 20);

        return $this->respondWithResource(UserResource::collection($users), 'Users retrieved successfully.');
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'employee_number' => $data['employee_number'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => $data['is_active'] ?? true,
            ]);
            $user->syncRoles([$data['role']]);
            $user->plants()->sync($data['role'] === 'Super Admin' ? [] : ($data['plant_ids'] ?? []));

            return $user;
        });

        return $this->respondSuccess(new UserResource($user->load('plants')), 'User created successfully.', 201);
    }

    public function update(UserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        $data = $request->validated();

        DB::transaction(function () use ($data, $user): void {
            $user->fill([
                'name' => $data['name'],
                'employee_number' => $data['employee_number'] ?? null,
                'email' => $data['email'],
                'is_active' => $data['is_active'] ?? true,
            ]);
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();
            $user->syncRoles([$data['role']]);
            $user->plants()->sync($data['role'] === 'Super Admin' ? [] : ($data['plant_ids'] ?? []));
        });

        return $this->respondSuccess(new UserResource($user->load('plants')), 'User updated successfully.');
    }

    public function resetPassword(UserResetPasswordRequest $request, User $user)
    {
        $this->authorize('update', $user);
        $user->update(['password' => Hash::make($request->validated()['password'])]);

        return $this->respondSuccess(null, 'Password reset successfully.');
    }
}
