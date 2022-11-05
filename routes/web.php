<?php

use App\Http\Controllers\Controller;
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

Route::feeds();

Route::controller(Controller::class)->group(function () {
    Route::get('subscriptions', 'getSubscriptions');
    Route::post('subscriptions/password', 'getSubscriptions');
    Route::post('subscriptions', 'postSubscriptions');
});
