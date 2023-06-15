<?php

use App\Http\Controllers\Admin\Competitions\CompetitionsController;
use Illuminate\Support\Facades\Route;

Route::resource('/', CompetitionsController::class, ['parameters' => ['' => 'id']]);
