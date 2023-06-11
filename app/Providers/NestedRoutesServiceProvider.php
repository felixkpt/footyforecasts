<?php

namespace App\Providers;

use App\Http\Middleware\NestedToutesAuth;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class NestedRoutesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        require(app_path('Repositories/helperrepo.php'));
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('nested_routes_auth', NestedToutesAuth::class);
        $this->loadRoutesFrom(base_path('routes/nested-routes/admin/driver.php'));
        $this->loadRoutesFrom(base_path('routes/nested-routes/client/driver.php'));
    }
}
