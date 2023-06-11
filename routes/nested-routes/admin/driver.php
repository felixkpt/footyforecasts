<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Admin routes for your application. These
| routes are loaded by the NesetedRouteServiceProvider within a group which
| is assigned the "web" middleware group. Enjoy building your Admin!
|
*/

$prefix = '';
$middleWares = ['nested_routes_auth'];

Route::middleware(array_filter(array_merge($middleWares, [$prefix])))
    ->prefix($prefix)
    ->group(function () {

        $nested_routes_folder = 'nested-routes/admin';

        $routes_path = base_path('routes/' . $nested_routes_folder);
        if (file_exists($routes_path)) {
            $route_files = collect(File::allFiles(base_path('routes/' . $nested_routes_folder)))->filter(fn ($file) => !Str::is($file->getFileName(), 'driver.php'));

            foreach ($route_files as $file) {

                $path = $file->getPath();
                $file_name = $file->getFileName();
                $prefix = str_replace($file_name, '', $path);
                $prefix = str_replace($routes_path, '', $prefix);
                $file_path = $file->getPathName();
                $this->route_path = $file_path;
                $arr = explode('/', $prefix);
                $len = count($arr);
                $main_file = $arr[$len - 1];
                $arr = array_map('ucwords', $arr);
                $arr = array_filter($arr);
                $ext_route = str_replace('user.route.php', '', $file_name);
                $ext_route = str_replace('index.route.php', '', $file_name);
                if ($main_file . '.route.php' === $ext_route)
                    $ext_route = str_replace($main_file . '.', '.', $ext_route);
                $ext_route = str_replace('.route.php', '', $ext_route);
                //            $ext_route = str_replace('web', '', $ext_route);
                if ($ext_route)
                    $ext_route = '/' . $ext_route;
                $prefix = strtolower($prefix . $ext_route);

                Route::group(['prefix' => $prefix], function () {
                    require $this->route_path;
                });
            }
        }
    });
