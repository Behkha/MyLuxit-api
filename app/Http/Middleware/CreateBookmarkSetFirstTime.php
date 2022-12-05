<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class CreateBookmarkSetFirstTime
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
        //TODO move this to a job and run on start.sh
        if (Auth::check()) {
            if (!Redis::exists(config('cache.prefix').'User:' . Auth::id() . ':PlaceBookmarks')) {
                $placeIds = Auth::user()->bookmarkedPlaces()->pluck('bookmarkable_id');
                foreach ($placeIds as $placeId)
                    Redis::sadd(config('cache.prefix').'User:' . Auth::id() . ':PlaceBookmarks', $placeId);
            }
            if (!Redis::exists(config('cache.prefix').'User:' . Auth::id() . ':EventBookmarks')) {
                $eventIds = Auth::user()->bookmarkedEvents()->pluck('bookmarkable_id');
                foreach ($eventIds as $eventId)
                    Redis::sadd(config('cache.prefix').'User:' . Auth::id() . ':EventBookmarks', $eventId);
            }
        }

        $response = $next($request);

        // Post-Middleware Action

        return $response;
    }
}
