<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
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
        // prueba duracion token
        /*
        Passport::routes();
        Passport::tokensExpireIn(now()->addYears(5));
        Passport::refreshTokensExpireIn(now()->addYears(5));
        Passport::personalAccessTokensExpireIn(now()->addYears(5));
        */

    }
}
