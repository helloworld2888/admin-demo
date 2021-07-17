<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// 异常路由返回
Route::fallback(function () {
    echo 'error';
});

Route::view('404', 'admin.404');
Route::view('login', 'admin.login');
Route::view('index', 'admin.index');

Route::prefix('view')->group(function () {
    Route::view('welcome', 'admin.welcome')->name('admin.view.welcome');

    // 系统管理
    Route::prefix('system')->group(function () {
        // 管理员管理
        Route::prefix('user')->group(function () {
            Route::view('index', 'admin.system.user.index');
            Route::view('add', 'admin.system.user.add');
            Route::view('edit', 'admin.system.user.edit');
            Route::view('setRole', 'admin.system.user.setRole');
            Route::view('setPassword', 'admin.system.user.setPassword');
        });
        // 角色管理
        Route::prefix('role')->group(function () {
            Route::view('index', 'admin.system.role.index');
            Route::view('add', 'admin.system.role.add');
            Route::view('edit', 'admin.system.role.edit');
            Route::view('assign-permission', 'admin.system.role.assign-permission');
        });
        // 菜单管理
        Route::prefix('menu')->group(function () {
            Route::view('index', 'admin.system.menu.index');
            Route::view('add', 'admin.system.menu.add');
            Route::view('edit', 'admin.system.menu.edit');
        });
        // 权限管理
        Route::prefix('permission')->group(function () {
            Route::view('index', 'admin.system.permission.index');
            Route::view('add', 'admin.system.permission.add');
            Route::view('edit', 'admin.system.permission.edit');
        });
    });
});

