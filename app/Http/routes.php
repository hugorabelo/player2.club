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
header('Access-Control-Allow-Origin:  *');
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');

Route::get('login', 'LoginController@getLogar');

Route::post('login', 'LoginController@postLogar');

Route::get('api/logout', 'LoginController@logout');

Route::get('/', function()
{
    return View::make('inicio');
});

//Route::group(array('before'=>'auth'), function() {
//Route::group(array('middleware' => 'cors', 'prefix'=>'api'), function() {
Route::group(array('prefix'=>'api'), function() {

    Route::resource('campeonato', 'CampeonatosController');

    Route::get('campeonatoTipos/arquivoDetalhe/{id}', 'CampeonatoTiposController@getArquivoDetalhe');
    Route::resource('campeonatoTipos', 'CampeonatoTiposController');

    Route::get('jogosDaPlataforma/{id}', 'PlataformasController@getJogos');
    Route::resource('plataformas', 'PlataformasController');
    Route::post('plataformas/{id}', 'PlataformasController@update');

    Route::get('tiposDeCampeonatoDoJogo/{id}', 'JogosController@getTiposDeCampeonato');
    Route::get('campeonatosAbertosDoJogo/{id}', 'JogosController@getCampeonatosAbertos');
    Route::resource('jogos', 'JogosController');
    Route::post('jogos/{id}', 'JogosController@update');

    Route::resource('usuarioTipo', 'UsuarioTiposController');

    Route::get('campeonatosDisponiveisParaUsuario/{id}', 'UsersController@listaCampeonatosDisponiveis');
    Route::get('campeonatosInscritosParaUsuario/{id}', 'UsersController@listaCampeonatosInscritos');
    Route::get('partidasParaUsuario/{id}', 'UsersController@listaPartidas');
    Route::post('usuario/adicionaSeguidor', 'UsersController@adicionaSeguidor');
    Route::post('usuario/removeSeguidor', 'UsersController@removeSeguidor');
    Route::get('usuario/seguindo/{id}', 'UsersController@seguindo');
    Route::get('usuario/seguidores/{id}', 'UsersController@seguidores');
    Route::post('usuario/getPosts', 'UsersController@listaPostsUsuario');
    Route::post('usuario/segue', 'UsersController@segue');
    Route::get('usuario/getJogos/{id}', 'UsersController@listaJogos');
    Route::post('usuario/adicionaSeguidorJogo', 'UsersController@seguirJogo');
    Route::post('usuario/removeSeguidorJogo', 'UsersController@removeSeguidorJogo');
    Route::post('usuario/segueJogo', 'UsersController@segueJogo');
    Route::resource('usuario', 'UsersController');
    Route::post('usuario/{id}', 'UsersController@update');

    Route::resource('campeonatoAdmin', 'CampeonatoAdminsController');

    Route::get('campeonatoUsuarioNaoAdministrador/{id}', 'CampeonatoUsuariosController@getUsuarioNaoAdministrador');
    Route::resource('campeonatoUsuario', 'CampeonatoUsuariosController');

    Route::get('campeonatoFase/create/{id}', 'CampeonatoFasesController@create');
    Route::post('campeonatoFase/abreFase', 'CampeonatoFasesController@abreFase');
    Route::post('campeonatoFase/fechaFase', 'CampeonatoFasesController@fechaFase');
    Route::resource('campeonatoFase', 'CampeonatoFasesController');

    Route::resource('pontuacaoRegra', 'PontuacaoRegrasController');

    Route::get('faseGrupo/usuariosComClassificacao/{id}', 'FaseGrupoController@getUsuariosComClassificacao');
    Route::get('faseGrupo/partidasDaFase/{id}', 'FaseGrupoController@getPartidas');
    Route::post('faseGrupo/partidasPorRodada', 'FaseGrupoController@getPartidasPorRodada');
    Route::get('faseGrupo/partidasMataMata/{id}', 'FaseGrupoController@getPartidasMataMata');
    Route::resource('faseGrupo', 'FaseGrupoController');

    Route::get('menuTree', 'MenuController@getMenuTree');
    Route::resource('menu', 'MenuController');

    Route::resource('permissao', 'PermissaoController');

    Route::resource('userPlataforma', 'UserPlataformaController');

    Route::resource('usuarioFases', 'UsuarioFasesController');

    Route::resource('usuariogrupos', 'UsuarioGruposController');

    Route::post('partidas/contestar/{id}', 'PartidasController@contestarResultado');
    Route::put('partidas/cancelar/{id}', 'PartidasController@cancelarResultado');
    Route::resource('partidas', 'PartidasController');

    Route::resource('usuariopartidas', 'UsuarioPartidasController');

    Route::resource('tipoCompetidor', 'TipoCompetidorController');

    Route::resource('acessoCampeonato', 'AcessoCampeonatoController');

    Route::get('modeloCampeonato/getCriteriosClassificacao/{id}', 'ModeloCampeonatoController@getCriteriosClassificacao');
    Route::resource('modeloCampeonato', 'ModeloCampeonatoController');

    Route::resource('criterioClassificacao', 'CriterioClassificacaoController');

    Route::post('post/curtir', 'PostController@curtir');
    Route::post('post/usuarioCurtiu', 'PostController@usuarioCurtiu');
    Route::post('post/getComentarios', 'PostController@getComentarios');
    Route::resource('post', 'PostController');

    Route::post('comentario/curtir', 'ComentarioController@curtir');
    Route::resource('comentario', 'ComentarioController');

});

Route::any('{catchall}', function() {
    return View::make('inicio');
})->where('catchall', '.*');

/*
 */
Event::listen('Illuminate\Database\Events\QueryExecuted', function($query)
{
//    Log::info($query->sql);
    //DB::getQueryLog();
});
/* */
