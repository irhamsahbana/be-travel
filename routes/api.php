<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\{
    // GetCitiesController
    AuthController,
    AgentController,
    BranchController,
    CategoryController,
    CompanyController,
    CongregationController,
    FileController,
    InvoiceController,
    TutorController,
    PaymentController,
    TestController,
};

use App\Libs\Response;
use Illuminate\Support\Facades\DB;

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

Route::post('register-companies', [CompanyController::class, 'register']);
Route::post('register-agents', [AgentController::class, 'register']);
Route::post('register-congregations', [CongregationController::class, 'register']);

Route::get('status-congregations/{identifier}', [CongregationController::class, 'check']);
Route::get('public-categories', [CategoryController::class, 'index']);

Route::get('public-companies', [CompanyController::class, 'publicIndex']);
Route::get('public-branches', [BranchController::class, 'publicIndex']);
Route::post("auth/login", [AuthController::class, 'attempt']);

Route::get('test', function () {
    return (new Response)->json(['test' => 'test'], 'success');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);

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

    Route::post('files', [FileController::class, 'storeFile']);

    Route::get('congregations', [CongregationController::class, 'index']);
    Route::get('congregations/{id}', [CongregationController::class, 'show']);
    Route::patch('congregations/{id}', [CongregationController::class, 'update']);

    Route::get('agents/{id}/attachments', [AgentController::class, 'downloadAttachments']);
    Route::get('agents/{id}', [AgentController::class, 'show']);
    Route::get('agents', [AgentController::class, 'index']);

    Route::get('invoices', [InvoiceController::class, 'index']);
    Route::get('invoices/{id}', [InvoiceController::class, 'show']);
    Route::delete('invoices/{id}', [InvoiceController::class, 'destroy']);

    Route::post('payments', [PaymentController::class, 'store']);
});

Route::get('delete-non-dummy-data', [TestController::class, 'deleteNonDummy']);

Route::fallback(function () {
    return (new Response)->json([], 'Endpoint not found.', 404);
});
