<?php

namespace App\Http\Middleware;

use Closure;
use DB;

class DatabaseLoggerMiddleware
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
        if(config('database.global_logging')) {
            DB::connection()->enableQueryLog();
        }

        return $next($request);
    }
}
