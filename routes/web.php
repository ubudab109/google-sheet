<?php

use App\Http\Controllers\GoogleSheetsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [GoogleSheetsController::class, 'connectGoogleAccount'])->name('google.connect');
Route::get('/google-callback', [GoogleSheetsController::class, 'handleGoogleCallback'])->name('google.callback');
Route::get('/google-form', [GoogleSheetsController::class, 'showForm'])->name('google.form');
Route::post('/add-to-google-sheet', [GoogleSheetsController::class, 'addToGoogleSheet']);
