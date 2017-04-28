<?php

use Carbon\Carbon;

class CampeonatosController extends Controller
{

    /**
     * Campeonato Repository
     *
     * @var Campeonato
     */
    protected $campeonato;

    public function __construct(Campeonato $campeonato)
    {
        $this->campeonato = $campeonato;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $campeonatos = Campeonato::get();
        //$campeonato = new Campeonato();

        foreach ($campeonatos as $campeonato) {
            $campeonato->jogo = $campeonato->jogo()->descricao;
            $campeonato->jogo_imagem = $campeonato->jogo()->imagem_capa;
            $campeonato->campeonatoTipo = $campeonato->campeonatoTipo()->descricao;
            $campeonato->plataforma = $campeonato->plataforma()->descricao;
            $campeonato->plataforma_imagem = $campeonato->plataforma()->imagem_logomarca;
        }
        return Response::json($campeonatos);
    }

    public function show($id)
    {
        $campeonato = Campeonato::find($id);
        $campeonato->plataforma = Plataforma::find($campeonato->plataformas_id);
        $campeonato->jogo = Jogo::find($campeonato->jogos_id);
        $campeonato->tipo = CampeonatoTipo::find($campeonato->campeonato_tipos_id);
        $campeonato->dataInicio = $campeonato->faseInicial()->data_inicio;
        $campeonato->dataFinal = $campeonato->faseFinal()->data_fim;
        $campeonato->status = $campeonato->status();

        $quantidadeUsuario = DB::table('campeonato_usuarios')->where('users_id', '=', Auth::getUser()->id)->where('campeonatos_id', '=', $campeonato->id)->count('id');
        $campeonato->usuarioInscrito = false;
        if($quantidadeUsuario > 0) {
            $campeonato->usuarioInscrito = true;
        }

        $campeonato->usuarioAdministrador = false;
        $quantidadeAdministrador = DB::table('campeonato_admins')->where('users_id', '=', Auth::getUser()->id)->where('campeonatos_id', '=', $campeonato->id)->count('id');
        if($quantidadeAdministrador > 0) {
            $campeonato->usuarioAdministrador = true;
        }

        return Response::json($campeonato);
    }

    public function create()
    {
        $jogos = Jogo::get();
        $campeonatoTipos = CampeonatoTipo::get();
        $plataformas = Plataforma::get();
        return Response::json(compact('jogos', 'campeonatoTipos', 'plataformas'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $input = Input::all();
        $validation = Validator::make($input, Campeonato::$rules);

        if ($validation->passes()) {
            $campeonatoTipo = CampeonatoTipo::find($input['campeonato_tipos_id']);
            $nomeClasse = $campeonatoTipo->nome_classe_modelo;
            $campeonato = new $nomeClasse;
            $validacaoNumeroCompetidores = $campeonato->validarNumeroDeCompetidores($input['detalhes']);
            if ($validacaoNumeroCompetidores != '') {
                return Response::json(array('success' => false,
                    'errors' => array($validacaoNumeroCompetidores)), 300);
            }
            $campeonatoSalvo = $campeonato->salvar($input);


            return Response::json(array('success' => true, 'id'=> $campeonatoSalvo->id));
        }

        return Response::json(array('success' => false,
            'errors' => $validation->getMessageBag()->all(),
            'message' => 'There were validation errors.'), 300);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $campeonato = Campeonato::find($id);
        $campeonato->plataforma = Plataforma::find($campeonato->plataformas_id);
        $campeonato->jogo = Jogo::find($campeonato->jogos_id);
        $campeonato->tipo = CampeonatoTipo::find($campeonato->campeonato_tipos_id);
        $campeonato->dataInicio = $campeonato->faseInicial()->data_inicio;
        $campeonato->dataFinal = $campeonato->faseFinal()->data_fim;
        $campeonato->detalhes = $campeonato->detalhes();
        $campeonato->criterios = $campeonato->criteriosOrdenados();
        $campeonato->pontuacao = $campeonato->pontuacoes();
        return Response::json($campeonato);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        $inputAll = Input::all();
        $inputDetalhes = $inputAll['detalhes'];
        $dataInicial = $inputAll['dataInicio'];
        $dataFinal = $inputAll['dataFinal'];
        $input = array_except($inputAll, ['_method', 'criterios', 'dataFinal', 'dataInicio', 'detalhes', 'jogo', 'plataforma', 'pontuacao', 'tipo', 'novo']);
        $validation = Validator::make($input, Campeonato::$rules);

        if ($validation->passes()) {
            if($input['acesso_campeonato_id'] == null) {
                unset($input['acesso_campeonato_id']);
            }
            if($input['imagem_logo'] == null) {
                unset($input['imagem_logo']);
            }

            $campeonato = $this->campeonato->find($id);
            $campeonato->update($input);

            $detalhes = $campeonato->detalhes();
            if($inputDetalhes['tipo_competidor_id'] != null) {
                $detalhes->tipo_competidor_id = $inputDetalhes['tipo_competidor_id'];
            }
            $detalhes->update();

            $faseInicial = $campeonato->faseInicial();
            $faseInicial->data_inicio = Carbon::parse($dataInicial);
            $faseInicial->update();

            $faseFinal = $campeonato->faseFinal();
            $faseFinal->data_fim = Carbon::parse($dataFinal);
            $faseFinal->update();

            return Response::json(array('success' => true));
        }

        return Response::json(array('success' => false,
            'errors' => $validation->getMessageBag()->all(),
            'message' => 'There were validation errors.'), 300);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        $campeonato = $this->campeonato->find($id);
        if($campeonato->status() > 2) {
            return Response::json(array('success' => false,
                'errors' => 'messages.campeonato_iniciado'), 300);
        }

        $usuarioLogado = Auth::getUser();
        if($usuarioLogado->id != $campeonato->criador) {
            return Response::json(array('success'=>false,
                'errors'=> 'messages.exclusao_permitida_apenas_criador'),300);
        }

        $campeonato->delete();
        return Response::json(array('success' => true));
    }

    public function show2($id)
    {
        $campeonato = Campeonato::find($id);
        $usuarios = $campeonato->usuariosInscritos();
        $campeonatoAdministradores = $campeonato->administradores();
        $campeonatoUsuarios = array();
        foreach ($usuarios as $usuario) {
            if (!$campeonatoAdministradores->contains($usuario->id)) {
                array_push($campeonatoUsuarios, $usuario);
            }
        }
        $campeonatoFases = $campeonato->fases();
        return Response::json(compact('campeonatoUsuarios', 'campeonatoAdministradores', 'campeonatoFases'));
    }

    public function iniciaCampeonato($id)
    {
        $campeonato = Campeonato::find($id);
        $fase_inicial = $campeonato->faseInicial();
        foreach ($campeonato->usuariosInscritos() as $usuario) {
        }
    }

    public function getParticipantes($id) {
        $campeonato = Campeonato::find($id);
        $participantes = $campeonato->usuariosInscritos();
        foreach ($participantes as $participante) {
            $nome_completo = $participante->nome;
            $nome_completo = explode(' ', $nome_completo);
            $nome_completo = count($nome_completo) > 2 ? array_shift($nome_completo).' '.array_pop($nome_completo) : $participante->nome;
            $participante->nome = $nome_completo;

            $time = Time::find($participante->pivot->time_id);
            $participante->time = $time;
        }
        return Response::json($participantes);
    }

    public function getUltimasPartidasUsuario($idUsuario, $idCampeonato = null) {
        $usuario = User::find($idUsuario);
        $partidas = $usuario->partidas($idCampeonato)->take(6);
        return Response::json($partidas);
    }

    public function getPartidas($idCampeonato) {
        $campeonato = Campeonato::find($idCampeonato);
        $partidas = $campeonato->partidas();
        return Response::json($partidas);
    }

    public function getPartidasContestadas($idCampeonato) {
        $campeonato = Campeonato::find($idCampeonato);
        $partidas = $campeonato->partidasContestadas();
        return Response::json($partidas);
    }

    public function getPartidasEmAberto($idCampeonato) {
        $campeonato = Campeonato::find($idCampeonato);
        if($campeonato== null) {
            return Response::json();
        }
        $partidas = $campeonato->partidasEmAberto();
        return Response::json($partidas);
    }

    public function pesquisaFiltros() {
        $input = Input::all();
        $pesquisa = Campeonato::whereNotNull('id');

        if(isset($input['plataformas_id'])) {
            $pesquisa->where('plataformas_id','=',$input['plataformas_id']);
        }

        if(isset($input['jogos_id'])) {
            $pesquisa->where('jogos_id','=',$input['jogos_id']);
        }

        if(isset($input['campeonatotipos_id'])) {
            $pesquisa->where('campeonato_tipos_id','=',$input['campeonatotipos_id']);
        }

        $resultadoPesquisa = $pesquisa->get();

        if(isset($input['status_id'])) {
            foreach ($resultadoPesquisa as $key=>$campeonato) {
                if($campeonato->status() != $input['status_id']) {
                    $resultadoPesquisa->pull($key);
                }
            }
        }

        foreach ($resultadoPesquisa as $campeonato) {
            $campeonato->nome_plataforma = $campeonato->plataforma()->descricao;
            $campeonato->plataforma_imagem = $campeonato->plataforma()->imagem_logomarca;
            $campeonato->nome_jogo = $campeonato->jogo()->descricao;
            $campeonato->jogo_imagem = $campeonato->jogo()->imagem_capa;
            $campeonato->tipo_campeonato= $campeonato->campeonatoTipo()->descricao;
            $campeonato->status = $campeonato->status();
        }

        return Response::json($resultadoPesquisa);
    }

    public function sortearClubes() {
        $input = Input::all();
        $idCampeonato = $input['idCampeonato'];
        $timesSelecionados = $input['timesSelecionados'];
        $campeonato = Campeonato::find($idCampeonato);
        $usuarios = $campeonato->usuariosInscritos();
        $usuarios = $usuarios->shuffle()->values();

        $timesInseridos = array();

        foreach ($usuarios as $usuario) {
            $random = rand(0, count($timesSelecionados) - 1);
            $time = $timesSelecionados[$random];
            while (in_array($time['id'], $timesInseridos)) {
                $random = rand(0, count($timesSelecionados) - 1);
                $time = $timesSelecionados[$random];
            }

            $campeonatoUsuario = CampeonatoUsuario::find($usuario->pivot->id);
            if(isset($campeonatoUsuario)) {
                $campeonatoUsuario->time_id = $time['id'];
                $campeonatoUsuario->save();
                array_push($timesInseridos, $time['id']);
            }

        }

        $campeonato->times_sorteados = true;
        $campeonato->save();
    }

}
