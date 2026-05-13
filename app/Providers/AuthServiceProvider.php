<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Dispatch;
use App\Models\InventoryTransaction;
use App\Models\SalesOrder;
use App\Policies\DispatchPolicy;
use App\Policies\InventoryTransactionPolicy;
use App\Policies\SalesOrderPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        SalesOrder::class => SalesOrderPolicy::class,
        Dispatch::class => DispatchPolicy::class,
        InventoryTransaction::class => InventoryTransactionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
