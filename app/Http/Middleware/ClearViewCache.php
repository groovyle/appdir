<?php
// https://stackoverflow.com/a/36727251

namespace App\Http\Middleware;

use Artisan;
use Closure;

class ClearViewCache
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
        if (env('APP_ENV') === 'local') {
            ini_set('opcache.revalidate_freq', '0');
            Artisan::call('view:clear');
        }

        return $next($request);
    }
}