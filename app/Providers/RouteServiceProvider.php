<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Cookie;

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
        $locale = Request::segment(1);
        $this->app->setLocale($locale);

        $this->mapApiRoutes();

        $this->mapWebRoutes($locale);

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes($locale)
    {
        if (array_key_exists($locale, $this->app->config->get('app.locales'))) {
            Route::group([
                'middleware' => 'web',
                'prefix' => $locale,
                'namespace' => $this->namespace,
            ], function ($router) {
                require base_path('routes/web.php');
            });
        } else {
            if (Cookie::get('lang') && array_key_exists(Cookie::get('lang'), $this->app->config->get('app.locales'))) {
                $this->app->setLocale(Cookie::get('lang'));
            }
            Route::group([
                'middleware' => 'web',
                'namespace' => $this->namespace,
            ], function ($router) {
                require base_path('routes/web.php');
            });
        }
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
}
