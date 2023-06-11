<?php

use App\Http\Controllers\Admin\Countries\CountriesController;
use Illuminate\Support\Facades\Route;

Route::resource('/', CountriesController::class);
