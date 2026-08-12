<?php
namespace App\Policies;
use App\Models\Defect;
use App\Models\User;
class DefectPolicy { public function viewAny(User $user): bool { return $user->can('defects.view'); } public function view(User $user, Defect $defect): bool { return $user->can('defects.view'); } public function create(User $user): bool { return $user->can('defects.create'); } public function update(User $user, Defect $defect): bool { return $user->can('defects.update'); } public function delete(User $user, Defect $defect): bool { return $user->can('defects.delete'); } }
