<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapApiv2Routes();

        $this->mapApiv3Routes();

        $this->mapGatewayRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "api2" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiv2Routes()
    {
        Route::prefix('api/v2')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api_v2.php'));
    }

    /**
     * Define the "apiv3" routes for the application.
     * 
     * These routes are typically stateless.
     * 
     * @return void
     */
    protected function mapApiv3Routes()
    {
        Route::prefix('api/v3')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api_v3.php'));
    }

    /**
     * Define the "gateway" routes for the application.
     * 
     * These routes are typically stateless.
     * 
     * @return void
     */
    protected function mapGatewayRoutes()
    {
        Route::prefix('api/gateway')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/gateway.php'));
    }
}
