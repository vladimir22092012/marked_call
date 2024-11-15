<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Permissions
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->role->checkPermissions($request)) {
            redirect($request->user()->role->homeUrl);
        }

        return $next($request);
    }
}
