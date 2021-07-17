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

Route::get('test', [\App\Http\Controllers\IndexController::class, 'test']);
Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::get('login/captcha', [\App\Http\Controllers\AuthController::class, 'getCaptcha']);


Route::middleware(['auth.admin.api'])->group(function () {
    Route::get('init', [\App\Http\Controllers\Admin\IndexController::class, 'getInit']); // layuimini 初始化数据

    // 统一标签搜索
    Route::prefix('label')->group(function () {
        Route::get('role', [\App\Http\Controllers\Admin\LabelController::class, 'getRoles']); // 获取角色数据
    });

    // 系统管理
    Route::prefix('system')->group(function () {
        // 管理员管理
        Route::prefix('user')->group(function () {
            Route::get('index', [\App\Http\Controllers\Admin\System\UserController::class, 'index']); // 列表
            Route::post('add', [\App\Http\Controllers\Admin\System\UserController::class, 'add']); // 添加
            Route::post('edit', [\App\Http\Controllers\Admin\System\UserController::class, 'edit']); // 基础信息修改
            Route::post('delete', [\App\Http\Controllers\Admin\System\UserController::class, 'delete']); // 删除
            Route::post('setStatus', [\App\Http\Controllers\Admin\System\UserController::class, 'setStatus']); // 设置状态
            Route::post('setRole', [\App\Http\Controllers\Admin\System\UserController::class, 'setRole']); // 修改角色
            Route::post('setPassword', [\App\Http\Controllers\Admin\System\UserController::class, 'setPassword']); // 修改密码
        });

        // 角色管理
        Route::prefix('role')->group(function () {
            Route::get('index', [\App\Http\Controllers\Admin\System\RoleController::class, 'index']); // 列表
            Route::post('add', [\App\Http\Controllers\Admin\System\RoleController::class, 'add']); // 添加
            Route::post('edit', [\App\Http\Controllers\Admin\System\RoleController::class, 'edit']); // 修改
            Route::post('delete', [\App\Http\Controllers\Admin\System\RoleController::class, 'delete']); // 删除
            Route::get('getPermissions', [\App\Http\Controllers\Admin\System\RoleController::class, 'getPermissions']); // 获取角色权限列表
            Route::post('setPermissions', [\App\Http\Controllers\Admin\System\RoleController::class, 'setPermissions']); // 设置角色权限列表
        });

        // 权限管理
        Route::prefix('permission')->group(function () {
            Route::get('index', [\App\Http\Controllers\Admin\System\PermissionController::class, 'index']); // 列表
            Route::post('add', [\App\Http\Controllers\Admin\System\PermissionController::class, 'add']); // 添加
            Route::post('edit', [\App\Http\Controllers\Admin\System\PermissionController::class, 'edit']); // 修改
            Route::post('delete', [\App\Http\Controllers\Admin\System\PermissionController::class, 'delete']); // 删除
        });
    });
});



