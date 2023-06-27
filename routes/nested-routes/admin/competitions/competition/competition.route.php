<?php

use App\Http\Controllers\Admin\Competitions\Competition\CompetitionController;
use Illuminate\Support\Facades\Route;

Route::get('/{id}', [CompetitionController::class, 'show']);
Route::post('/{id}', [CompetitionController::class, 'store']);

Route::get('/{id}/predictions', [CompetitionController::class, 'predictions']);
Route::post('/{id}/predictions', [CompetitionController::class, 'doPredictions']);
Route::get('/{id}/fixtures', [CompetitionController::class, 'fixtures']);
Route::post('/{id}/fixtures', [CompetitionController::class, 'getFixtures']);
Route::get('/{id}/detailed-fixtures', [CompetitionController::class, 'detailedFixtures']);
Route::post('/{id}/detailed-fixtures', [CompetitionController::class, 'getDetailedFixtures']);
Route::get('/{id}/update', [CompetitionController::class, 'update']);
Route::post('/{id}/update', [CompetitionController::class, 'getUpdates']);

Route::post('/{id}/change-status', [CompetitionController::class, 'changeStatus']);
