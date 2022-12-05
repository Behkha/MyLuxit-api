<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class UserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (Auth::guest()) {
            return response()->json([
                'message' => 'User not authorized'
            ], 401);
        }

        $user = Auth::user();
        if ($user->status_id !== User::STATUSES['active']) {
            return response()->json([
                'message' => 'user is not active'
            ], 403);
        }


        $response = $next($request);
        return $response;
    }
}
