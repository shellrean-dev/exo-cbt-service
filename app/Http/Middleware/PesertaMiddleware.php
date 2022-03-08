<?php

namespace App\Http\Middleware;

use App\Models\CacheConstant;
use Closure;
use App\Peserta;
use Illuminate\Http\Request;
use ShellreanDev\Cache\CacheHandler;

class PesertaMiddleware
{
    protected $cache;

    public function __construct(CacheHandler $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $tokens = explode('|', $request->bearerToken());
        if(count($tokens) != 2) {
            return response()->json(['message' => 'Anda tidak lagi memiliki akses. silakan login kembali'], 401);
        }

        $query = Peserta::with('group')
            ->where(['id' => $tokens[0]]);

        if (config('exo.enable_cache')) {
            $is_cached = $this->cache->isCached(CacheConstant::KEY_AUTHETICATION, md5($request->bearerToken()));
            if ($is_cached) {
                $user = $this->cache->getItem(CacheConstant::KEY_AUTHETICATION, md5($request->bearerToken()));
            } else {
                $user = $query->first();
                if ($user) {
                    $this->cache->cache(CacheConstant::KEY_AUTHETICATION, md5($request->bearerToken()), $user);
                }
            }
        } else {
            $user = $query->first();
        }

        if($user && $user->api_token == $request->bearerToken()) {
            $request->attributes->add(['peserta-auth' => $user]);
            return $next($request);
        }

        return response()->json(['message' => 'Anda tidak lagi memiliki akses. silakan login kembali'], 401);
    }
}
