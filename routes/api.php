<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\{
    AuthController,
    CategoryController,
    TutorController,
    // GetCitiesController

    BranchController
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function() {
    Route::get('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/logout-all-devices', [AuthController::class, 'logoutAll']);

    Route::get('categories', [CategoryController::class, 'index']);

    Route::put('tutors', [TutorController::class, 'upsert']);
    Route::get('public/tutors', [TutorController::class, 'indexPublic']);
    Route::get('tutors', [TutorController::class, 'index']);
    Route::get('tutors/{id}', [TutorController::class, 'show']);

    Route::get('branches', [BranchController::class, 'index']);
    Route::post('branches', [BranchController::class, 'store']);
    Route::get('branches/{id}', [BranchController::class, 'show']);
    Route::patch('branches/{id}', [BranchController::class, 'update']);
    Route::delete('branches/{id}', [BranchController::class, 'destroy']);
    /**
     * Dangerous route, only for development purpose
     * Should be removed in production
     */
    // Route::get('seed-provinces-cities', [GetCitiesController::class, 'provincesAndCities']);
});


Route::post("auth/login", [AuthController::class, 'attempt']);
