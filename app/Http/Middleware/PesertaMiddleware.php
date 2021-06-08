<?php

namespace App\Http\Middleware;

use Closure;
use App\Peserta;
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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $key = sprintf('peserta:data:token:%s', $request->bearerToken());
        if ($this->cache->isCached($key)) {
            $user = $this->cache->getItem($key);
        } else {
            $user = Peserta::with('group')
                ->where(['api_token' => $request->bearerToken()])
                ->first();

            $this->cache->cache($key, $user);
        }

        if($user) {
            $request->attributes->add(['peserta-auth' => $user]);
            return $next($request);
        }

        return response()->json(['message' => 'Anda tidak lagi memiliki akses. silakan login kembali'], 401);
    }
}
