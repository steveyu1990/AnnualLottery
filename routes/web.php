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

Route::get('/', 'LotteryController@index');
Route::get('/quite', 'LotteryController@quite');
Route::post('/check_mobile', 'LotteryController@checkMobile');
Route::post('/register', 'LotteryController@register');
Route::get('/lottery', 'LotteryController@lottery');
Route::get('/lucky', 'LotteryController@lucky');
