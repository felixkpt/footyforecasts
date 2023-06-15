<?php

use App\Http\Controllers\Admin\Posts\PostsController;
use Illuminate\Support\Facades\Route;

Route::resource('/', PostsController::class, ['parameters' => ['' => 'id']]);
