<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PostController;

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

Route::get('/getBlogsList', [BlogController::class, 'getList']);
Route::get('/getPostsList', [PostController::class, 'getList']);
Route::get('/getTimeList', [PostController::class, 'getTimeList']);


Route::get('/', function () {
    return view('welcome');
});
