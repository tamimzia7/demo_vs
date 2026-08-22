<?php

namespace App\Providers;

use App\Models\Offering;
use App\Models\Relationship;
use App\Models\Visitor;
use App\Policies\AdminPolicy;
use App\Policies\OfferingPolicy;
use App\Policies\RelationshipPolicy;
use App\Policies\VisitorPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin', [AdminPolicy::class, 'viewAny']);
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Offering::class, OfferingPolicy::class);
        Gate::policy(Visitor::class, VisitorPolicy::class);
        Gate::policy(Relationship::class, RelationshipPolicy::class);
    }
}
