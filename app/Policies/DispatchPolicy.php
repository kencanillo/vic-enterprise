<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Dispatch;
use App\Models\User;

class DispatchPolicy
{
    public function viewAny(User $user): bool { return $user->hasRole([Role::SUPER_ADMIN, Role::ADMIN, Role::OPERATIONS_LEAD, Role::DISPATCH_STAFF]); }
    public function view(User $user, Dispatch $dispatch): bool { return $this->viewAny($user); }
    public function create(User $user): bool { return $user->hasRole([Role::SUPER_ADMIN, Role::ADMIN, Role::OPERATIONS_LEAD, Role::DISPATCH_STAFF]); }
    public function update(User $user, Dispatch $dispatch): bool { return $this->create($user); }
    public function delete(User $user, Dispatch $dispatch): bool { return $user->hasRole([Role::SUPER_ADMIN, Role::ADMIN]); }
}