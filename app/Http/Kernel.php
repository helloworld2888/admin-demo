<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\GlobalBeforeMiddleware::class,
        \App\Http\Middleware\TrustHosts::class, // 域名白名单
        \App\Http\Middleware\TrustProxies::class, // 过滤可信代理
        \Fruitcake\Cors\HandleCors::class, // 跨源资源
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class, // 维护访问url
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class, // 验证 POST 数据大小
        \App\Http\Middleware\TrimStrings::class, // 对请求内容进行 前后空白字符清理
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class, // 空字符转成 null
        \App\Http\Middleware\GlobalAfterMiddleware::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class, // 对 Cookie 进行加解密处理与验证
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class, //  Cookie 的队列服务中，取出 Cookie 加入到响应中。
            \Illuminate\Session\Middleware\StartSession::class, // 开启 Session
            \Illuminate\Session\Middleware\AuthenticateSession::class, // 让其他设备上的 Session 失效
            // \Illuminate\View\Middleware\ShareErrorsFromSession::class, // Session 中分享错误信息至 View 视图中
            // \App\Http\Middleware\VerifyCsrfToken::class, // CSRF 令牌
            \Illuminate\Routing\Middleware\SubstituteBindings::class, // 显式和隐式地根据请求参数绑定对应数据模型
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'             => \App\Http\Middleware\Authenticate::class,
        'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers'    => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'              => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed'           => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'         => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'auth.admin.api'   => \App\Http\Middleware\AuthAdminApi::class,
    ];
}
