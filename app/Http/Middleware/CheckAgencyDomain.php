<?php

namespace App\Http\Middleware;

use App\Models\AgencySite;
use Closure;

class CheckAgencyDomain
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
        # check subdomain
        $ageny_sites = AgencySite::where('subdomain', $request->subdomain)->first();
        if (empty($ageny_sites)) {
            return redirect()->route('index');
        }

        return $next($request);
    }
}
