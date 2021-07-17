<?php


namespace App\Http\Middleware;

use App\Libraries\Alarm;
use Closure;
use Illuminate\Support\Facades\Log;

class GlobalAfterMiddleware
{
    public function handle($request, Closure $next)
    {
        $path     = $request->path();
        $method   = $request->method();
        $response = $next($request);
        $cost     = number_format(microtime(true) - START_TIME, 4);
        if ($method != 'GET') {
            $info = "request:{$path} cost:{$cost} response:".json_encode($response);
            Log::info($info);
        }

        return $response;
    }
}
