<?php

namespace App\Http\Middleware;

use Closure;

class NoCacheHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        no_cache_headers($response);
        return $response;
    }
}
