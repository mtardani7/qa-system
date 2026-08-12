<?php

namespace App\Policies;

use App\Models\Plant;
use App\Models\User;

class PlantPolicy
{
    public function viewAny(User $user): bool { return $user->can('plants.view'); }
    public function view(User $user, Plant $plant): bool { return $user->can('plants.view'); }
    public function create(User $user): bool { return $user->can('plants.create'); }
    public function update(User $user, Plant $plant): bool { return $user->can('plants.update'); }
    public function delete(User $user, Plant $plant): bool { return $user->can('plants.delete'); }
}
