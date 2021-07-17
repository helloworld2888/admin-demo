<?php

namespace App\Providers;

use App\Libraries\Role as RoleLib;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
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

        // 超级管理员 角色响应true所有权限
        Gate::before(function ($user, $ability) {
            return $user->hasRole(RoleLib::SUPER_ADMIN) ? true : null;
        });
    }
}
