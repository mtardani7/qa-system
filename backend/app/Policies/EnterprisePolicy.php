<?php
namespace App\Policies;
use App\Models\User;
class EnterprisePolicy { public function viewAny(User $user): bool{return $user->can('enterprise.view');} public function view(User $user, mixed $model): bool{return $user->can('enterprise.view');} public function create(User $user): bool{return $user->can('enterprise.manage');} public function update(User $user, mixed $model): bool{return $user->can('enterprise.manage');} public function delete(User $user, mixed $model): bool{return $user->can('enterprise.manage');} }
