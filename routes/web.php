<?php

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
use Illuminate\Http\Request;


Route::get('/', function () {
    //if there's cookies, redirect to o365 login.
    return redirect('/login');
});

//O365 user login
Route::get('/oauth.php', 'O365AuthController@oauth');

//login, register related.
Auth::routes();


//a user must login to visit below pages.
Route::group(['middleware' => ['web','auth.basic']], function () {

    //add school related routers.
    Route::get('/home', 'HomeController@index');
});