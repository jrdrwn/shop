<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Toko;
use App\Models\Transaction;
use App\Policies\ProductPolicy;
use App\Policies\TokoPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Toko::class => TokoPolicy::class,
        Product::class => ProductPolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
