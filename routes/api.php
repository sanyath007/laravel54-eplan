<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'api'], function () {
    /** โครงการ */
    Route::get('projects', 'ProjectController@getAll');
    Route::get('projects/{id}', 'ProjectController@getById');

    /** ควบคุมกำกับติดตาม */
    Route::get('monthly', 'MonthlyController@getAll');
    Route::get('monthly/{id}', 'MonthlyController@getById');

    /** การส่งเบิกเงิน */
    Route::put('withdrawals/{id}', 'WithdrawalController@withdraw');
});
