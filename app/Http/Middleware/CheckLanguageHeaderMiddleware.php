<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;

class CheckLanguageHeaderMiddleware
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
        // Pre-Middleware Action
        $languageAbbr = $request->header('Language');
        if (empty($languageAbbr)) {
            $languageAbbr = Language::getDefaultLanguageAbbr();
        }

        $languageAbbr = mb_strtolower($languageAbbr);

        $request->merge([
            'language' => Language::getByAbbr($languageAbbr)
        ]);

        $response = $next($request);

        // Post-Middleware Action

        return $response;
    }
}
