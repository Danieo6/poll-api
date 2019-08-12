<?php
$router->group(['prefix' => 'api/v1'], function () use ($router)
{
    //User API
    Route::group(['prefix' => 'user'], function ($router) {
        Route::post('create', 'UserController@create');
        Route::post('login', 'UserController@login');
        Route::post('logout', 'UserController@logout');
        Route::post('refresh', 'UserController@refresh');
        Route::get('current', 'UserController@current');
    });

    //Poll API
    Route::group(['prefix' => 'poll'], function ($router) {
        Route::post('create', 'PollController@create');
        Route::post('vote', 'PollController@vote');
        Route::get('get/{id}', 'PollController@get');
    });
});