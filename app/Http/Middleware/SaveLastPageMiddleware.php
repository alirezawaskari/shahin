<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SaveLastPageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $except_url=array( url('login') , url('/') , url('/logout') );
        if ( ! in_array(url()->current(), $except_url) ){
            session()->put('last_page', url()->current());
        }

        return $next($request);
    }
}
