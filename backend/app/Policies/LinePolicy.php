<?php
namespace App\Policies;
use App\Models\Line;
use App\Models\User;
class LinePolicy { public function viewAny(User $user): bool { return $user->can('lines.view'); } public function view(User $user, Line $line): bool { return $user->can('lines.view'); } public function create(User $user): bool { return $user->can('lines.create'); } public function update(User $user, Line $line): bool { return $user->can('lines.update'); } public function delete(User $user, Line $line): bool { return $user->can('lines.delete'); } }
