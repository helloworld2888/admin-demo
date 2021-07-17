<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // sql日志
        DB::listen(function ($query) {
            foreach ($query->bindings as $replace){
                $value = is_numeric($replace) ? $replace : "'".$replace."'";
                $query->sql = preg_replace('/\?/', $value, $query->sql, 1);
            }
            Log::info($query->sql);
        });
        //

        // 视图公共变量
        view()->composer(['admin.404', 'admin.login', 'admin.index'], function ($view) {
            $domain = URL::asset('/');
            $layuiminiUrl = URL::asset('/').'resources/layuimini-2-onepage';
            view()->share('domain', $domain);
            view()->share('layuiminiUrl', $layuiminiUrl);
        });
    }
}
