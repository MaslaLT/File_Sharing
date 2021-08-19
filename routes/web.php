<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/', [\App\Http\Controllers\FileController::class, 'store'])->name('file.store');
Route::get('/{fileId}', [\App\Http\Controllers\FileController::class, 'show'])->name('file.show');

Route::post('/upload', [\App\Http\Controllers\UploadController::class, 'store']);
Route::delete('/upload', [\App\Http\Controllers\UploadController::class, 'destroy']);
