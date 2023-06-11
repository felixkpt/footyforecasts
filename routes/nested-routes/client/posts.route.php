<?php

use App\Http\Controllers\Client\Posts\PostsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostsController::class, 'index']);
