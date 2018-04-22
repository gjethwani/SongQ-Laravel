<?php

namespace App\Http\Middleware;

use Closure;

class CheckIfGuestAuthenticated
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
        $guestAuthenticated = $request->session()->get('playlistAuthenticated');
        if ($guestAuthenticated) {
          return $next($request);
        } else {
          return redirect('/guest');
        }

    }
}
