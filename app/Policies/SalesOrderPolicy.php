<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\SalesOrder;
use App\Models\User;

class SalesOrderPolicy
{
    public function viewAny(User $user): bool { return $user->hasRole([Role::SUPER_ADMIN, Role::ADMIN, Role::OPERATIONS_LEAD, Role::DISPATCH_STAFF]); }
    public function view(User $user, SalesOrder $order): bool { return $this->viewAny($user); }
    public function create(User $user): bool { return $user->hasRole([Role::SUPER_ADMIN, Role::ADMIN, Role::OPERATIONS_LEAD]); }
    public function update(User $user, SalesOrder $order): bool { return $user->hasRole([Role::SUPER_ADMIN, Role::ADMIN, Role::OPERATIONS_LEAD]); }
    public function delete(User $user, SalesOrder $order): bool { return $user->hasRole([Role::SUPER_ADMIN, Role::ADMIN]); }
}