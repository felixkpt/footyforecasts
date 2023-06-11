<?php

use App\Http\Controllers\Admin\Teams\TeamsController;
use Illuminate\Support\Facades\Route;

Route::resource('/', TeamsController::class, ['parameters' => ['' => 'id']]);
