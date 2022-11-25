<?php

namespace App\Http\Middleware;

use Closure;

class DefaultSecurityHeaders
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

        $response
            ->header('X-Content-Type', 'nosniff', true)
            // https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Strict_Transport_Security_Cheat_Sheet.html
            ->header('Strict-Transport-Security', 'max-age=86400; includeSubDomains')
            // https://cheatsheetseries.owasp.org/cheatsheets/Content_Security_Policy_Cheat_Sheet.html
            ->header('Content-Security-Policy', "frame-ancestors 'self'; form-action 'self';")
        ;

        return $response;
    }
}
