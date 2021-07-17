<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class GlobalBeforeMiddleware
{
    public function handle($request, Closure $next)
    {
        define('START_TIME', microtime(true));
        $path   = $request->path();
        $method = $request->method();
        $params = $request->all();
        if ($method != 'GET') {
            Log::info("request:{$path}", $params);
        }
        return $next($request);
    }
}
