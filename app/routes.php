<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('login', 'LoginController@getLogar');

Route::post('login', 'LoginController@postLogar');

Route::get('api/logout', 'LoginController@logout');

Route::get('/', function()
{
    return View::make('inicio');
});

//Route::group(array('before'=>'auth'), function() {

    Route::group(array('prefix'=>'api'), function() {
        Route::resource('campeonato', 'CampeonatosController');
    });

    Route::group(array('prefix'=>'api'), function() {
        Route::resource('campeonatoTipos', 'CampeonatoTiposController');
    });

    Route::group(array('prefix'=>'api'), function() {
        Route::resource('plataformas', 'PlataformasController');
    });

// Inserido para fazer o update de imagens via Angularjs
    Route::post('api/plataformas/{id}', 'PlataformasController@update');

    Route::group(array('prefix'=>'api'), function() {
        Route::resource('jogos', 'JogosController');
    });

// Inserido para fazer o update de imagens via Angularjs
    Route::post('api/jogos/{id}', 'JogosController@update');

    Route::group(array('prefix'=>'api'), function() {
        Route::resource('usuarioTipo', 'UsuarioTiposController');
    });

    Route::group(array('prefix'=>'api'), function() {
        Route::resource('usuario', 'UsersController');
    });

    // Inserido para fazer o update de imagens via Angularjs
    Route::post('api/usuario/{id}', 'UsersController@update');

    Route::group(array('prefix'=>'api'), function() {
        Route::resource('campeonatoAdmin', 'CampeonatoAdminsController');
    });

    Route::get('api/campeonatoUsuarioNaoAdministrador/{id}', 'CampeonatoUsuariosController@getUsuarioNaoAdministrador');
    Route::group(array('prefix'=>'api'), function() {
        Route::resource('campeonatoUsuario', 'CampeonatoUsuariosController');
    });

    Route::get('api/campeonatoFase/create/{id}', 'CampeonatoFasesController@create');
    Route::group(array('prefix'=>'api'), function() {
        Route::resource('campeonatoFase', 'CampeonatoFasesController');
    });

    Route::group(array('prefix'=>'api'), function() {
        Route::resource('pontuacaoRegra', 'PontuacaoRegrasController');
    });

    Route::group(array('prefix'=>'api'), function() {
        Route::resource('faseGrupo', 'FaseGrupoController');
    });

    Route::get('api/menuTree', 'MenuController@getMenuTree');
    Route::group(array('prefix'=>'api'), function() {
        Route::resource('menu', 'MenuController');
    });

    Route::group(array('prefix'=>'api'), function() {
        Route::resource('permissao', 'PermissaoController');
    });

    Route::group(array('prefix'=>'api'), function() {
        Route::resource('userPlataforma', 'UserPlataformaController');
    });

//});

App::missing(function($exception) {
   return View::make('inicio');
});

/*
 *
Event::listen('illuminate.query', function($query)
{
    Log::info($query);
});
/* */
