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

/* */
header('Access-Control-Allow-Origin:  *');
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');
/* */

\Firebase\JWT\JWT::$leeway = 2;

Route::group(array('prefix'=>'api', 'middleware' => 'auth0.jwt'), function() {

    Route::get('campeonato/participantes/{id}', 'CampeonatosController@getParticipantes');
    Route::get('campeonato/ultimasPartidasUsuario/{id}/{idCampeonato?}', 'CampeonatosController@getUltimasPartidasUsuario');
    Route::get('campeonato/partidas/{idCampeonato}', 'CampeonatosController@getPartidas');
    Route::get('campeonato/partidasContestadas/{idCampeonato}', 'CampeonatosController@getPartidasContestadas');
    Route::get('campeonato/partidasEmAberto/{id}', 'CampeonatosController@getPartidasEmAberto');
    Route::post('campeonato/pesquisaFiltros', 'CampeonatosController@pesquisaFiltros');
    Route::post('campeonato/sortearClubes', 'CampeonatosController@sortearClubes');
    Route::resource('campeonato', 'CampeonatosController');

    Route::get('campeonatoTipos/arquivoDetalhe/{id}', 'CampeonatoTiposController@getArquivoDetalhe');
    Route::resource('campeonatoTipos', 'CampeonatoTiposController');

    Route::get('jogosDaPlataforma/{id}', 'PlataformasController@getJogos');
    Route::resource('plataformas', 'PlataformasController');
    Route::post('plataformas/{id}', 'PlataformasController@update');

    Route::get('tiposDeCampeonatoDoJogo/{id}', 'JogosController@getTiposDeCampeonato');
    Route::get('campeonatosDoJogo/{id}', 'JogosController@getCampeonatos');
    Route::get('jogos/feed/{id}/{offset?}/{quantidade?}', 'JogosController@getFeed');
    Route::get('jogos/plataformas/{id}', 'JogosController@getPlataformas');
    Route::resource('jogos', 'JogosController');
    Route::post('jogos/{id}', 'JogosController@update');

    Route::resource('usuarioTipo', 'UsuarioTiposController');

    Route::get('campeonatosDisponiveisParaUsuario/{id}', 'UsersController@listaCampeonatosDisponiveis');
    Route::get('campeonatosInscritosParaUsuario/{id}', 'UsersController@listaCampeonatosInscritos');
    Route::get('partidasParaUsuario/{id}/{idCampeonato?}', 'UsersController@listaPartidas');
    Route::get('partidasEmAberto/{id}/{idCampeonato?}', 'UsersController@listaPartidasEmAberto');
    Route::get('partidasDisputadas/{id}/{idCampeonato?}', 'UsersController@listaPartidasDisputadas');
    Route::get('partidasNaoDisputadas/{id}/{idCampeonato?}', 'UsersController@listaPartidasNaoDisputadas');
    Route::post('usuario/adicionaSeguidor', 'UsersController@adicionaSeguidor');
    Route::post('usuario/removeSeguidor', 'UsersController@removeSeguidor');
    Route::get('usuario/seguindo/{id}', 'UsersController@seguindo');
    Route::get('usuario/seguidores/{id}', 'UsersController@seguidores');
    Route::post('usuario/getPosts', 'UsersController@listaPostsUsuario');
    Route::post('usuario/segue', 'UsersController@segue');
    Route::get('usuario/getJogos/{id}/{count?}', 'UsersController@listaJogos');
    Route::post('usuario/adicionaSeguidorJogo', 'UsersController@seguirJogo');
    Route::post('usuario/removeSeguidorJogo', 'UsersController@removeSeguidorJogo');
    Route::post('usuario/segueJogo', 'UsersController@segueJogo');
    Route::get('usuario/feed/{id}/{todos?}/{offset?}/{quantidade?}', 'UsersController@getFeed');
    Route::delete('usuario/desistirCampeonato/{idCampeonato}', 'UsersController@desistirCampeonato');
    Route::get('usuario/notificacoes/{lidas?}', 'UsersController@listaNotificacoes');
    Route::post('usuario/lerNotificacao', 'UsersController@lerNotificacao');
    Route::post('usuario/adicionarNotificacaoEmail', 'UsersController@adicionarNotificacaoEmail');
    Route::post('usuario/removerNotificacaoEmail', 'UsersController@removerNotificacaoEmail');
    Route::get('usuario/conversas', 'UsersController@listaConversas');
    Route::get('usuario/mensagens/{idRemetente}', 'UsersController@listaMensagens');
    Route::get('usuario/equipes/{idUsuario?}', 'UsersController@listaEquipes');
    Route::resource('usuario', 'UsersController');
    Route::post('usuario/{id}', 'UsersController@update');

    Route::resource('campeonatoAdmin', 'CampeonatoAdminsController');

    Route::get('campeonatoUsuarioNaoAdministrador/{id}', 'CampeonatoUsuariosController@getUsuarioNaoAdministrador');
    Route::post('campeonatoUsuario/salvarTime', 'CampeonatoUsuariosController@salvarTime');
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

    Route::post('permissao/bugReport', 'PermissaoController@reportarBug');
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
    Route::post('post/{id}', 'PostController@update');
    Route::get('post/imagens/{id}', 'PostController@getImagens');
    Route::resource('post', 'PostController');

    Route::post('atividade/curtir', 'AtividadeController@curtir');
    Route::post('atividade/usuarioCurtiu', 'AtividadeController@usuarioCurtiu');
    Route::get('atividade/curtidas/{id}', 'AtividadeController@getCurtidas');
    Route::post('atividade/getComentarios', 'AtividadeController@getComentarios');
    Route::get('atividade/pesquisa/{textoPesquisa}', 'AtividadeController@getItensPesquisa');
    Route::resource('atividade', 'AtividadeController');

    Route::post('comentario/curtir', 'ComentarioController@curtir');
    Route::resource('comentario', 'ComentarioController');

    Route::resource('notificacaoEvento', 'NotificacaoEventoController');

    Route::resource('mensagem', 'MensagemController');

    Route::get('time/porModelo/{idModeloCampeonato}', 'TimeController@getTimesPorModelo');
    Route::resource('time', 'TimeController');

    Route::get('equipe/funcoes', 'EquipeController@getFuncoes');
    Route::post('equipe/mensagem', 'EquipeController@enviarMensagem');
    Route::get('equipe/integrante/{idEquipe}', 'EquipeController@getIntegrantes');
    Route::post('equipe/integrante/{idEquipe}/{idUsuario}', 'EquipeController@adicionaIntegrante');
    Route::put('equipe/integrante', 'EquipeController@updateIntegrante');
    Route::delete('equipe/integrante/{idEquipe}/{idIntegrante?}', 'EquipeController@removeIntegrante');
    Route::get('equipe/solicitacao/{idEquipe}', 'EquipeController@getSolicitacoes');
    Route::post('equipe/solicitacao/{idEquipe}/{idUsuario?}', 'EquipeController@solicitarEntrada');
    Route::delete('equipe/solicitacao/{idEquipe}/{idUsuario?}', 'EquipeController@cancelarSolicitacao');
    Route::get('equipe/convites/{idEquipe}', 'EquipeController@getConvites');
    Route::resource('equipe', 'EquipeController');
    Route::post('equipe/{id}', 'EquipeController@update');

    Route::get('validaAutenticacao', array('middleware' => 'auth0.jwt', function() {
        $retornoValidacao = Response::json(Auth::getUser());
        return $retornoValidacao;
    }));

    Route::get('checkAutenticacao', array('middleware' => 'auth0.jwt', function() {
        $retornoValidacao = Response::json(Auth::check());
        return $retornoValidacao;
    }));

    Route::get('mudaIdioma/{locale}', function ($locale) {
        App::setLocale($locale);
    });
});

Route::get('api/callback', function() {
    return Response::json(Auth::check());
});

Route::get('api/times/baseFifa', 'TimeController@getBaseFifa');

Route::any('{catchall}', function() {
    return redirect('/');
})->where('catchall', '.*');

/*
Event::listen('Illuminate\Database\Events\QueryExecuted', function($query)
{
    if(count($query->bindings) == 0) {
        Log::info('##'.$query->time.'##'.$query->sql);
    } else {
        $sql = str_replace('?', '%s', $query->sql);
        Log::info('##'.$query->time.'##'.vsprintf($sql, $query->bindings));
    }
});
/* */
