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

Route::post('api/oauth/access_token', function () {
    return response()->json(Authorizer::issueAccessToken());
});

Route::group(array('prefix'=>'api', 'middleware' => 'oauth'), function() {

    Route::get('campeonato/participantes/{id}', 'CampeonatosController@getParticipantes');
    Route::get('campeonato/ultimasPartidasUsuario/{id}/{idCampeonato?}', 'CampeonatosController@getUltimasPartidasUsuario');
    Route::get('campeonato/partidas/{idCampeonato}', 'CampeonatosController@getPartidas');
    Route::get('campeonato/partidasPorRodada/{idCampeonato}/{aberta}/{rodada?}', 'CampeonatosController@getPartidasPorRodada');
    Route::get('campeonato/partidasContestadas/{idCampeonato}', 'CampeonatosController@getPartidasContestadas');
    Route::get('campeonato/partidasEmAberto/{id}', 'CampeonatosController@getPartidasEmAberto');
    Route::get('campeonato/tabelaCompleta/{id}', 'CampeonatosController@getTabelaCompleta');
    Route::post('campeonato/pesquisaFiltros', 'CampeonatosController@pesquisaFiltros');
    Route::post('campeonato/sortearClubes', 'CampeonatosController@sortearClubes');
    Route::post('campeonato/aplicarWO', 'CampeonatosController@aplicarWO');
    Route::get('campeonato/rodadas/{id}', 'CampeonatosController@getRodadas');
    Route::get('campeonato/naofinalizado', 'CampeonatosController@getNaoFinalizados');
    Route::post('campeonato/informacoesDaRodada', 'CampeonatosController@setInformacoesDaRodada');
    Route::resource('campeonato', 'CampeonatosController');

    Route::get('campeonatoTipos/arquivoDetalhe/{id}', 'CampeonatoTiposController@getArquivoDetalhe');
    Route::resource('campeonatoTipos', 'CampeonatoTiposController');

    Route::get('jogosDaPlataforma/{id}/{apenasCampeonato?}', 'PlataformasController@getJogos');
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
    Route::get('partidasParaUsuario/{id}/{idCampeonato?}/{confirmadas?}', 'UsersController@listaPartidas');
    Route::get('partidasEmAberto/{id}/{idCampeonato?}', 'UsersController@listaPartidasEmAberto');
    Route::get('partidasDisputadas/{id}/{idCampeonato?}', 'UsersController@listaPartidasDisputadas');
    Route::get('partidasNaoDisputadas/{id}/{idCampeonato?}', 'UsersController@listaPartidasNaoDisputadas');
    Route::post('usuario/adicionaSeguidor', 'UsersController@adicionaSeguidor');
    Route::post('usuario/removeSeguidor', 'UsersController@removeSeguidor');
    Route::get('usuario/seguindo/{id}', 'UsersController@seguindo');
    Route::get('usuario/seguidores/{id}', 'UsersController@seguidores');
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
    Route::get('usuario/equipes/{idUsuario?}/{tipo?}', 'UsersController@listaEquipes');
    Route::get('usuario/equipesAdministradas', 'UsersController@listaEquipesAdministradas');
    Route::get('usuario/convites', 'UsersController@listaConvites');
    Route::post('usuario/convidarUsuario', 'UsersController@convidarUsuario');
    Route::post('usuario/conviteCampeonato/{idCampeonato}/{idAmigo}', 'UsersController@convidarParaCampeonato');
    Route::post('usuario/finalizarWizard/{idUsuario}', 'UsersController@finalizarWizard');
    Route::post('usuario/saveAnonimo', 'UsersController@storeAnonimo');
    Route::get('usuario/pesquisa/{textoPesquisa}', 'UsersController@pesquisaPorNome');
    Route::post('usuario/associarAnonimo', 'UsersController@associarAnonimo');
    Route::get('usuario/pendencias', 'UsersController@verificarPendencias');
    Route::resource('usuario', 'UsersController');
    Route::post('usuario/{id}', 'UsersController@update');

    Route::resource('campeonatoAdmin', 'CampeonatoAdminsController');

    Route::get('campeonatoUsuarioNaoAdministrador/{id}', 'CampeonatoUsuariosController@getUsuarioNaoAdministrador');
    Route::post('campeonatoUsuario/salvarTime', 'CampeonatoUsuariosController@salvarTime');
    Route::post('campeonatoUsuario/salvarAnonimo', 'CampeonatoUsuariosController@salvarAnonimo');
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

    Route::get('atividade/pesquisa/{textoPesquisa}', 'AtividadeController@getItensPesquisa');
    Route::resource('atividade', 'AtividadeController');

    Route::resource('notificacaoEvento', 'NotificacaoEventoController');

    Route::resource('mensagem', 'MensagemController');

    Route::get('time/porModelo/{idModeloCampeonato}', 'TimeController@getTimesPorModelo');
    Route::resource('time', 'TimeController');

    Route::get('equipe/funcoes', 'EquipeController@getFuncoes');
    Route::post('equipe/mensagem', 'EquipeController@enviarMensagem');
    Route::get('equipe/integrante/{idEquipe}', 'EquipeController@getIntegrantes');
    Route::post('equipe/integrante/{idEquipe}/{idUsuario?}', 'EquipeController@adicionaIntegrante');
    Route::put('equipe/integrante', 'EquipeController@updateIntegrante');
    Route::delete('equipe/integrante/{idEquipe}/{idIntegrante?}', 'EquipeController@removeIntegrante');
    Route::get('equipe/solicitacao/{idEquipe}', 'EquipeController@getSolicitacoes');
    Route::post('equipe/solicitacao/{idEquipe}/{idUsuario?}', 'EquipeController@solicitarEntrada');
    Route::delete('equipe/solicitacao/{idEquipe}/{idUsuario?}', 'EquipeController@cancelarSolicitacao');
    Route::get('equipe/convites/{idEquipe}', 'EquipeController@getConvites');
    Route::get('equipe/convitesDisponiveis/{idEquipe}', 'EquipeController@getConvitesDisponiveis');
    Route::resource('equipe', 'EquipeController');
    Route::post('equipe/{id}', 'EquipeController@update');

    Route::post('tutorial/visualizado', 'TutorialController@getVisualizado');
    Route::post('tutorial/marcarVisualizado', 'TutorialController@setVisualizado');
    Route::resource('tutorial', 'TutorialController');
    Route::get('tutorial/{id}/{mobile}', 'TutorialController@show');

    Route::resource('tutorialItem', 'TutorialItemController');

    Route::post('agenda/agendarPartida', 'AgendaController@agendarPartida');
    Route::post('agenda/confirmarAgendamento', 'AgendaController@confirmarAgendamento');
    Route::post('agenda/recusarAgendamento', 'AgendaController@recusarAgendamento');
    Route::post('agenda/cancelarAgendamento', 'AgendaController@cancelarAgendamento');
    Route::post('agenda/historico', 'AgendaController@getHistoricoAgendamento');
    Route::get('agenda/{idCampeonato}/{idUsuario}', 'AgendaController@show');
    Route::get('agenda/listaHorarios/{idCampeonato}/{idUsuario}/{data?}', 'AgendaController@listaHorarios');
    Route::post('agenda/partidaNaoRealizada', 'AgendaController@justificaPartidaNaoRealizada');
    Route::resource('agenda', 'AgendaController');

    Route::get('validaAutenticacao', array('middleware' => 'oauth', function() {
        $user = Auth::getUser();
        $user->equipesAdministradas = $user->equipesAdministradas()->get();
        $retornoValidacao = Response::json($user);
        return $retornoValidacao;
    }));

    Route::get('checkAutenticacao', array('middleware' => 'oauth', function() {
        $retornoValidacao = Response::json(Auth::check());
        return $retornoValidacao;
    }));

    Route::get('mudaIdioma/{locale}', function ($locale) {
        App::setLocale($locale);
    });
});

Route::get('api/campeonato/tabelaPublica/{id}', 'CampeonatosController@getTabelaPublica');
Route::get('api/campeonatoPublico/{id}', 'CampeonatosController@showPublico');
Route::get('api/faseGrupo/partidasPorRodadaPublico/{rodada}/{idGrupo}', 'FaseGrupoController@getPartidasPorRodadaPublico');
Route::get('api/faseGrupoPublico/{id}', 'FaseGrupoController@showPublico');

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
