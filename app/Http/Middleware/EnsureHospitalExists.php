<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class EnsureHospitalExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $hospital_key = request()->get('hospital') ?? Cookie::get('current_hospital_id');

        if (!$hospital_key)
            return $next($request);

        if (Auth::check() && Auth::user()->account_type === 'hospital') {

            $hospital = Hospital::where('key', $hospital_key)->first();

            if (!$hospital) {
                Auth::logout();
                abort(404);
            }

            if ($hospital->account_status != 'active') {
                Auth::logout();
                abort(403);
            }
        }

        
        Cookie::queue('current_hospital_id', $hospital_key, 60 * 24 * 30);

        return $next($request);
    }
}
