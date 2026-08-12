<?php

namespace App\Policies;

use App\Models\QaChecker;
use App\Models\User;

class QaCheckerPolicy
{
    public function viewAny(User $user): bool { return $user->can('qa-checkers.view'); }
    public function view(User $user, QaChecker $qaChecker): bool { return $user->can('qa-checkers.view'); }
    public function create(User $user): bool { return $user->can('qa-checkers.create'); }
    public function update(User $user, QaChecker $qaChecker): bool { return $user->can('qa-checkers.update'); }
    public function delete(User $user, QaChecker $qaChecker): bool { return $user->can('qa-checkers.delete'); }
}
