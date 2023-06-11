<?php

/**
 * Created by PhpStorm.
 * User: iankibet
 * Date: 2016/06/04
 * Time: 7:47 AM
 */

namespace App\Repositories;

use App\Models\PermissionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Str;

class RoleRepository
{
    protected $path;
    protected $user;
    protected $menus;
    protected $allow = false;
    protected $request;
    protected $is_app = 0;
    protected $common;
    protected $userPermissions;
    protected $allPermissionsFile;

    protected $allowedPermissions;
    protected $role;
    protected $urls = [];
    protected $loopLevel = 0;


    public function __construct(Request $request = null)
    {
        if ($request) {
            $this->request = $request;
            $this->user = currentUser();
            $this->path = Route::getFacadeRoot()->current()->uri();
        }
    }

    public function check()
    {

        return true;

        $current = request()->getPathInfo();

        if (Str::startsWith($current, '/api/client')) {
            return true;
        } else if (currentUser()) {
            $this->authorize($current);
        } else {
            App::abort(401, "Not authorized to access this page/resource/endpoint");
        }
    }

    protected function authorize($current)
    {

        $allowed_urls = [];
        $allowed_urls[] = '/';
        $allowed_urls[] = '';
        $allowed_urls[] = 'auth/user';
        $allowed_urls[] = 'auth/password';

        if (in_array($current, $allowed_urls)) {
            return true;
        }

        $module = PermissionGroup::find($this->user->permission_group_id)
            ->first();

        $routes = $slugs = [];
        if (isset($module->routes)) {
            [$routes, $slugs] = [json_decode($module->routes) ?? [], json_decode($module->slugs) ?? []];
            if ($routes[0] == '*') return true;
        }

        $incoming_route = Str::after(Route::getCurrentRoute()->uri, 'api/');
        $method = request()->method();

        $found_path = '';
        foreach ($routes as $route) {

            $res = preg_split('#@#', $route, 2);
            $curr_route = $res[0];
            $methods = array_filter(explode('@', str_replace('|', '', $res[1] ?? '')));

            if ($incoming_route == $curr_route) {
                $found_path = true;
                if (in_array($method, $methods)) {
                    return true;
                }
            }
        }

        if ($found_path ===  true)
            $this->unauthorize(405);

        return $this->unauthorize();
    }

    public function unauthorize($status = 403, $message = null)
    {
        $common_paths = ['logout', 'login', 'register'];
        $path = $this->path;
        if (!in_array($path, $common_paths)) {
            App::abort($status, ($status === 405 ? "Not authorized to perform current method on" : "Not authorized to access") . " this page/resource/endpoint");
        }
    }
}
