<?php

namespace App\Http\Middleware;

use App\Repositories\RoleRepository;
use Closure;
use Illuminate\Routing\Router;

class NestedToutesAuth
{
    protected $router;
    public function __construct(Router $router)
    {
      $this->router = $router;
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
        // $role_repo = new RoleRepository($request);
        // $role_repo->check();
        return $next($request);
    }
}
