<?php

use App\Http\Controllers\OpenrouterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [LoginController::class, 'register'])->name('register');
Route::post('/login', [LoginController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/analyze', [OpenrouterController::class, 'analisisGizi']);
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/progress', [ProgressController::class, 'index']);
    Route::get('/rekomendasi', [OpenrouterController::class, 'rekomendasiGizi']);
    Route::get('/progress/detail/{id}', [ProgressController::class, 'show']);
    Route::post('/profile/{id}', [ProfileController::class, 'update']);
});