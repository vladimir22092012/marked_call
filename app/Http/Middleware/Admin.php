<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user('token') && $request->user()->name != 'admin') {
            return redirect('/');
        }

        return $next($request);
    }
}
