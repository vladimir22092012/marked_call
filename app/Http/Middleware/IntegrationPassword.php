<?php

namespace App\Http\Middleware;

use App\Models\CBLog;
use Closure;
use Illuminate\Http\Request;
use Laravel\Horizon\Exceptions\ForbiddenException;
use Symfony\Component\HttpFoundation\Response;

class IntegrationPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('ServiceToken') &&
            $request->header('ServiceToken') == env('MCC_SERVICES_PASS')) {
            return $next($request);
        }
        throw new ForbiddenException(403);
    }
}
