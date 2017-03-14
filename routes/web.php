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
    return view('welcome');
});

Route::get('/oauth.php', 'LoginController@oauth');

//Route::group(['prefix' => 'admin', 'namespace' => 'Admin','middleware'=>['web']], function(){
//
//    Route::get('login', 'SchoolsController@login');
//});
//Route::group(['prefix' => 'admin', 'namespace' => 'Admin','middleware'=>['Admin.Login','web']], function(){
//    Route::get('index', 'SchoolsController@index');
//    Route::get('detail/{id}', 'SchoolsController@detail');
//    Route::get('logoff', 'SchoolsController@logoff');
//
//});
//
//Route::get('/oauth', function (Request $request) {
//    $provider = new \League\OAuth2\Client\Provider\GenericProvider([
//        'clientId'                => '20db89ee-263a-40d6-9256-103029570676',
//        'clientSecret'            => 'kZ0kRt2Zz3zFOMxZTqqWDAj',
//        'redirectUri'             => 'http://blog.hd/oauth',
//        'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/authorize',
//        'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/token',
//        'urlResourceOwnerDetails' => '',
//        'scopes'                  => 'openid mail.send'
//    ]);
//echo $request->has('code');
//    if (!$request->has('code')) {
//        return redirect($provider->getAuthorizationUrl());
//    }
//    else {
//        $accessToken = $provider->getAccessToken('authorization_code', [
//            'code'     => $request->input('code')
//        ]);
//        echo   $accessToken;
//        echo  $accessToken->getToken();
//        exit($accessToken->getToken());
//    }
//});
Auth::routes();

Route::get('/home', 'HomeController@index');

Auth::routes();

Route::get('/home', 'HomeController@index');
