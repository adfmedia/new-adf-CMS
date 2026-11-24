<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'adm', 'namespace' => 'Adm\Http\Controllers', 'middleware' => ['web']], function () {
    Route::get('/login', 'AuthController@create')->middleware('guest')->name('adm.login');
    Route::post('/login', 'AuthController@store')->middleware('guest');
    Route::get('/login/bespokeid', 'BespokeIdAuthController@redirect')->middleware('guest')->name('adm.login.bespokeid');
    Route::get('/login/bespokeid/callback', 'BespokeIdAuthController@callback')->middleware('guest')->name('adm.login.bespokeid.callback');
    Route::any('/forgot-password', 'AuthController@forgot_password')->middleware('guest')->name('adm.forgot-password');
    Route::any('/reset-password/{token}', 'AuthController@reset_password')->middleware('guest')->name('adm.reset-password');
    Route::any('/mfa-enrol', 'AuthController@mfa_enrol')->name('adm.mfa-enrol');
    Route::any('/mfa-prompt', 'AuthController@mfa_prompt')->name('adm.mfa-prompt');
    Route::any('/logout', 'AuthController@logout')->name('adm.logout');
});
Route::group(['prefix' => 'adm', 'namespace' => 'Adm\Http\Controllers', 'middleware' => ['auth:adm']], function () {
    /* File Manager */
    Route::any('/file-manager/ajax/{requested_function}', 'FileManagerController@fn_loader')->name('adm.file-manager.ajax');
    Route::get('/file-manager', 'FileManagerController@index')->name('adm.file-manager');

    Route::any('/ajax/tools/{requested_function}', 'AjaxToolsController@fn_loader')->name('adm.ajax-tool');

    /* Preview */
    Route::group(['middleware' => ['front']], function () {
        Route::any('/{section?}/preview/{id?}', 'RouteController@preview')->name('adm.route.preview');
    });

    Route::any('/{section?}/{method?}/{id?}', 'RouteController@index')->name('adm.route');
});

if (env('FE_LOGIN_ENABLED')) {
    require __DIR__ . '/auth.php';
}

Route::group(['middleware' => ['front'], 'namespace' => 'App\Http\Controllers'], function () {
    Route::any('/beanstalk/pre', 'DeploymentController@preHook');
    Route::any('/beanstalk/post', 'DeploymentController@postHook');

    Route::group(['middleware' => ['web']], function () {
        // Images
        Route::group(['prefix' => 'images'], function () {
            Route::get('/fit-in/{dimensions}/{crop}/{path}', 'FileController@image_fit_in_crop')->where('path', '(.*)')->where('dimensions', '(\d*x\d*)')->where('crop', '(\d+x\d+(-\d*x\d*)?)')->name('image.fit-in.crop');
            Route::get('/fit-in/{dimensions}/{path}', 'FileController@image_fit_in')->where('path', '(.*)')->where('dimensions', '(\d*x\d*)')->name('image.fit-in');

            Route::get('/fit/{dimensions}/{crop}/{path}', 'FileController@image_fit_crop')->where('path', '(.*)')->where('dimensions', '(\d*x\d*)')->where('crop', '(\d+x\d+(-\d*x\d*)?)')->name('image.fit.crop');
            Route::get('/fit/{dimensions}/{path}', 'FileController@image_fit')->where('path', '(.*)')->where('dimensions', '(\d*x\d*)')->name('image.fit');

            Route::get('/fill/{dimensions}/{crop}/{path}', 'FileController@image_fill_crop')->where('path', '(.*)')->where('dimensions', '(\d*x\d*)')->where('crop', '(\d+x\d+(-\d*x\d*)?)')->name('image.fill.crop');
            Route::get('/fill/{dimensions}/{path}', 'FileController@image_fill')->where('path', '(.*)')->where('dimensions', '(\d*x\d*)')->name('image.fill');

            Route::get('/{path}', 'FileController@image_raw')->where('path', '(.*)')->name('image');
        });
        Route::group(['prefix' => 'files'], function () {
            Route::get('/{path}', 'FileController@file')->where('path', '(.*)')->name('file');
        });

        Route::get('/robots.txt', 'RobotsController@index');
        Route::get('/sitemap-news.xml', 'SitemapController@news')->name('sitemap.news');
        Route::get('/sitemap.xml', 'SitemapController@xml')->name('sitemap.xml');

        Route::get('/favicons/site.webmanifest', 'FileController@favicon_manifest')->name('favicon.manifest');

        Route::any('/ajax/{controller}/{method}', 'RouteController@ajax')->name('theme.ajax');
        Route::any('/{url_path?}', 'RouteController@route')->where('url_path', '(.*)')->name('theme');
    });
});