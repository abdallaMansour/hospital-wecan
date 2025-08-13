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











        // if (request()->path() == 'login' && request()->method() == 'GET') {
            $hospital_id = request()->get('hospital');

            if (!$hospital_id) {
                $hospital_id = Cookie::get('current_hospital_id');
                if (!$hospital_id) {
                    Auth::logout();
                    abort(403);
                }
            }

            $hospital = Hospital::find($hospital_id);

            if (!$hospital) {
                Auth::logout();
                return redirect()->to(env('APP_URL') . '/login?hospital=' . $hospital_id);
                // abort(404);
            }

            if ($hospital->account_status != 'active') {
                Auth::logout();
                return redirect()->to(env('APP_URL') . '/login?hospital=' . $hospital_id);
                // abort(403);
            }

            // make it with cookie
            Cookie::queue('current_hospital_id', $hospital_id, 60 * 24 * 30);
        // }














        // $hospital = $request->route('hospital');
        // if (!$hospital) {
        //     Auth::logout();
        //     return response()->view('errors.404');
        // }
        // $hospital = Hospital::where('id', $hospital)->where('account_status', 'active')->first();
        // if (!$hospital) {
        //     Auth::logout();
        //     return response()->view('errors.hospital-not-found');
        // }

        return $next($request);
    }
}
