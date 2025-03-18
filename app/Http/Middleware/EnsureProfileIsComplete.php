<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureProfileIsComplete
{
    public function handle(Request $request, Closure $next)
    {
        if (
            auth()->check() &&
            (!auth()->user()->building_id || !auth()->user()->department_id) &&
            !$request->is('app/complete-profile')
        ) {
            return redirect('/app/complete-profile');
        }

        return $next($request);
    }
}
