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
Route::post('/', [\App\Http\Controllers\UploadController::class, 'uploadTmp']);
Route::delete('/', [\App\Http\Controllers\UploadController::class, 'destroyTmp']);

Route::post('/upload', [\App\Http\Controllers\UploadController::class, 'store'])->name('upload.store');

Route::get('/upload/{link}', [\App\Http\Controllers\UploadController::class, 'find'])->name('upload.find');
Route::get('/upload/{link}/password', function($link) {
            return view('password', ['link' => $link]);
            })->name('upload.password');

Route::post('/upload/{link}', [\App\Http\Controllers\UploadController::class, 'auth'])->name('upload.auth');
Route::get('upload/{link}/show', [\App\Http\Controllers\UploadController::class, 'show'])->name('upload.show');
Route::get('upload/{link}/download', [\App\Http\Controllers\UploadController::class, 'download'])->name('upload.download');
