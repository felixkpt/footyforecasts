<?php

use App\Http\Controllers\Admin\Teams\Team\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/{id}', [TeamController::class, 'show']);
Route::post('/{id}/actions', [TeamController::class, 'checkMatchesAction']);
Route::get('/{id}/actions', [TeamController::class, 'checkMatches']);