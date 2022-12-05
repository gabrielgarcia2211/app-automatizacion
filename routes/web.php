<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PermisosToRolsController;
use App\Http\Controllers\PermisosToRolsControllerV2;
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


Route::get('/test',[PermisosToRolsController::class, 'extract_permissions']);
Route::get('/test2',[PermisosToRolsControllerV2::class, 'extract_permissions']);

