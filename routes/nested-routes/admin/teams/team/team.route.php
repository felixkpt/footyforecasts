<?php

use App\Http\Controllers\Admin\Teams\Team\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/{id}', [TeamController::class, 'show']);
Route::post('/{id}', [TeamController::class, 'store']);

Route::get('/{id}/predictions', [TeamController::class, 'predictions']);
Route::post('/{id}/predictions', [TeamController::class, 'doPredictions']);
Route::get('/{id}/fixtures', [TeamController::class, 'fixtures']);
Route::post('/{id}/fixtures', [TeamController::class, 'getFixtures']);
Route::get('/{id}/detailed-fixtures', [TeamController::class, 'detailedFixtures']);
Route::post('/{id}/detailed-fixtures', [TeamController::class, 'getDetailedFixtures']);
Route::get('/{id}/update', [TeamController::class, 'update']);
Route::post('/{id}/update', [TeamController::class, 'getUpdates']);

Route::post('/{id}/change-status', [TeamController::class, 'changeStatus']);
