<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CSVController;
use App\Http\Controllers\API\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Tasks;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/logIn', [AuthController::class, 'logIn']);

Route::group(['middleware' => 'auth:sanctum' ], function () {
    Route::post('/logOut', [AuthController::class, 'logOut']);
    Route::post('/createUser', [AuthController::class, 'createUser']);
    Route::post('/changeUserRole', [AuthController::class, 'changeUserRole']);

    Route::resource('/tasks', TaskController::class);

    Route::post('/tasks/filter', [TaskController::class, 'filteredTasks']);
    Route::get('/tasks/{id}/revision', [TaskController::class, 'getRevision']);
    Route::post('/tasks/changeStatus', [TaskController::class, 'changeStatus']);
    Route::post('/tasks/changeAssignTo', [TaskController::class, 'changeAssignTo']);


    Route::post('/csv/import', [CSVController::class, 'import']);
    Route::get('/csv/export', [CSVController::class, 'export']);

    Route::post('/allUsers', function(){
        return Tasks::with(['SubTask', 'createdBy', 'status', 'parent'])->where('id', 1)->get();
    });
});
