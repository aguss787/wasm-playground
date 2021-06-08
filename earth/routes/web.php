<?php

use App\Http\Controllers\AddOneController;
use App\Http\Controllers\PathSumCostController;
use App\Http\Controllers\ShortestPathController;
use App\Http\Controllers\SumController;
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
Route::get('/add_one', [AddOneController::class, 'run']);
Route::get('/sum', [SumController::class, 'run']);
Route::get('/path_sum', [PathSumCostController::class, 'run']);
Route::get('/shortest_path', [ShortestPathController::class, 'run']);
