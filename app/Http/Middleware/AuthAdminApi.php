<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class AuthAdminApi
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $guard
     * @return array|mixed
     */
    public function handle($request, Closure $next, $guard = 'web')
    {
        if (!Auth::guard($guard)->check()) {
            return response()->json(['code' => 0, 'msg' => '请重新登录']);
        }
        /**
         * @var User $user
         */
        $user = Auth::guard($guard)->user();
        if (!$user || $user->status != User::USABLE) {
            return response()->json(['code' => 0, 'msg' => '请重新登录']);
        }
        return $next($request);
    }

}
