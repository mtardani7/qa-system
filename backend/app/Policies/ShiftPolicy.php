<?php
namespace App\Policies;
use App\Models\Shift;
use App\Models\User;
class ShiftPolicy { public function viewAny(User $user): bool { return $user->can('shifts.view'); } public function view(User $user, Shift $shift): bool { return $user->can('shifts.view'); } public function create(User $user): bool { return $user->can('shifts.create'); } public function update(User $user, Shift $shift): bool { return $user->can('shifts.update'); } public function delete(User $user, Shift $shift): bool { return $user->can('shifts.delete'); } }
