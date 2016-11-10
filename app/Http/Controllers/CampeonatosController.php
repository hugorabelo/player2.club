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
            $campeonato->campeonatoTipo = $campeonato->campeonatoTipo()->descricao;
            $campeonato->plataforma = $campeonato->plataforma()->descricao;
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
            $campeonato->salvar($input);

            return Response::json(array('success' => true));
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
            $campeonato = $this->campeonato->find($id);
            $campeonato->update($input);

            $detalhes = $campeonato->detalhes();
            $detalhes->tipo_competidor_id = $inputDetalhes['tipo_competidor_id'];
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
        $this->campeonato->find($id)->delete();

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

}
