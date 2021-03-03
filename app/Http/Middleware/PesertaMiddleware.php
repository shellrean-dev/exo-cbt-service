<?php

namespace App\Http\Middleware;

use Closure;
use App\Peserta;

class PesertaMiddleware
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
        $user = Peserta::where(['api_token' => $request->bearerToken()])->first();
        if($user) {
            
            $request->attributes->add(['peserta-auth' => $user]);

            return $next($request);
        }

        return response()->json(['message' => 'Anda tidak lagi memiliki akses. silakan login kembali'], 401);
    }
}
