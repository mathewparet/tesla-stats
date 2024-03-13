<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Team;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SwitchTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->has('switchToTeam') && $request->user() && $request->user()->id)
        {
            $request->user()->switchTeam(Team::find($request->get('switchToTeam')));
        }

        return $next($request);
    }
}
