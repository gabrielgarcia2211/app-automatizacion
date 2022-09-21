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

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

# Rutas para el módulo de consultas
Route::group(['prefix' => 'administration', 'middleware' => 'auth'], function () {
    Route::get('/sites', [App\Http\Controllers\SitesController::class, 'get_sites'])->name('get-sites');
});
