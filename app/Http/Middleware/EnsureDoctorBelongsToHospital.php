<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class EnsureDoctorBelongsToHospital
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {












        $hospital_id = Cookie::get('current_hospital_id');
















        // $hospital = Hospital::findOrFail($request->route('hospital'));

        if (Auth::check()) {
            if (Auth::user()->hospital_id != $hospital_id) {
                Auth::logout();
                abort(403);
            }

            // $doctorHospitalId = Auth::user()->hospital_id;

            // if ($doctorHospitalId != $hospital->id && $hospital->account_status != 'active') {
            //     Auth::logout();
            //     return response()->view('errors.403');
            // }
        }

        return $next($request);
    }
}
