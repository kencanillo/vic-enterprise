<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\InventoryTransaction;
use App\Models\User;

class InventoryTransactionPolicy
{
    public function viewAny(User $user): bool { return $user->hasRole([Role::SUPER_ADMIN, Role::ADMIN, Role::OPERATIONS_LEAD, Role::WAREHOUSE_STAFF]); }
    public function create(User $user): bool { return $user->hasRole([Role::SUPER_ADMIN, Role::ADMIN, Role::OPERATIONS_LEAD, Role::WAREHOUSE_STAFF]); }
    public function view(User $user, InventoryTransaction $transaction): bool { return $this->viewAny($user); }
}