<?php

class CampeonatosController extends BaseController {

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

		foreach($campeonatos as $campeonato) {
			$campeonato->jogo = $campeonato->jogo()->descricao;
			$campeonato->campeonatoTipo = $campeonato->campeonatoTipo()->descricao;
			$campeonato->plataforma = $campeonato->plataforma()->descricao;
		}
		return Response::json($campeonatos);
	}

    public function show($id) {
        return Response::json(Campeonato::find($id));
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

		if ($validation->passes())
		{

			$campeonatoTipo = CampeonatoTipo::find($input['campeonato_tipos_id']);
			$nomeClasse = $campeonatoTipo->nome_classe_modelo;
			$campeonato = new $nomeClasse;
			$campeonato->salvar($input);

			return Response::json(array('success'=>true));
		}


		return Response::json(array('success'=>false,
			'errors'=>$validation->getMessageBag()->all(),
			'message'=>'There were validation errors.'),300);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$campeonato = $this->campeonato->find($id);

		$jogos = Jogo::get();
		$campeonatoTipos = CampeonatoTipo::get();
		$plataformas = Plataforma::get();
		return Response::json(compact('campeonato', 'jogos', 'campeonatoTipos', 'plataformas'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$input = array_except(Input::all(), '_method');
		$validation = Validator::make($input, Campeonato::$rules);

		if ($validation->passes())
		{
			$campeonato = $this->campeonato->find($id);
			$campeonato->update($input);

			return Response::json(array('success'=>true));
		}

		return Response::json(array('success'=>false,
			'errors'=>$validation->getMessageBag()->all(),
			'message'=>'There were validation errors.'),300);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$this->campeonato->find($id)->delete();

		return Response::json(array('success'=>true));
	}

	public function show2($id)
	{
		$campeonato = Campeonato::find($id);
		$usuarios = $campeonato->usuariosInscritos();
		$campeonatoAdministradores = $campeonato->administradores();
		$campeonatoUsuarios = array();
		foreach($usuarios as $usuario) {
			if(!$campeonatoAdministradores->contains($usuario->id)) {
				array_push($campeonatoUsuarios, $usuario);
			}
		}
		$campeonatoFases = $campeonato->fases();
		return Response::json(compact('campeonatoUsuarios','campeonatoAdministradores', 'campeonatoFases'));
	}

    public function iniciaCampeonato($id) {
        $campeonato = Campeonato::find($id);
        $fase_inicial = $campeonato->faseInicial();
        foreach($campeonato->usuariosInscritos() as $usuario) {
        }
    }

}
