<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasTeslaAPILinked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->user() && $request->user()->currentTeam->teslaAccount)
            return $next($request);
        else
        {
            session()->put('url.intended', $request->url());
            return redirect()->route('tesla-accounts.index');
        }
    }
}
