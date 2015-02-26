<?php

class CampeonatoFasesController extends BaseController {

	/**
	 * CampeonatoFase Repository
	 *
	 * @var CampeonatoFase
	 */
	protected $campeonatoFase;

	public function __construct(CampeonatoFase $campeonatoFase)
	{
		$this->campeonatoFase = $campeonatoFase;
	}

	public function index() {
		return null;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function show($id)
	{
		$campeonatoFases = CampeonatoFase::where('campeonatos_id','=',$id)->get();
		return Response::json($campeonatoFases);
	}

	public function create($id_campeonato)
	{
		$fases = CampeonatoFase::where('campeonatos_id', '=', $id_campeonato)->get();
		return Response::json(compact('fases'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::all();
		$validation = Validator::make($input, CampeonatoFase::$rules);

		if ($validation->passes())
		{
			$input['data_inicio'] = date('Y-m-d', strtotime($input['data_inicio']));
			$input['data_fim'] = date('Y-m-d', strtotime($input['data_fim']));
			if(empty($input['fase_anterior_id'])) {
				$input['fase_anterior_id'] = null;
			}
			if($this->existeFaseInicial($input['campeonatos_id'])) {
				return Response::json(array('success'=>false,
					'message'=>'Já existe uma Fase Inicial cadastrada para este campeonato'),300);
			}
			if($this->existeFaseFinal($input['campeonatos_id'])) {
				return Response::json(array('success'=>false,
					'message'=>'Já existe uma Fase Final cadastrada para este campeonato'),300);
			}
			$this->campeonatoFase->create($input);

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
		$fase = CampeonatoFase::find($id);
		$fases = CampeonatoFase::where('campeonatos_id', '=', $fase['campeonatos_id'])->get()->except($id);
		return Response::json(compact('fases', 'fase'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$input = array_except(Input::all(), array('_method', 'imagem_capa'));
		$validation = Validator::make($input, CampeonatoFase::$rules);

		if ($validation->passes())
		{
			$fase = $this->campeonatoFase->find($id);
			$input['data_inicio'] = date('Y-m-d', strtotime($input['data_inicio']));
			$input['data_fim'] = date('Y-m-d', strtotime($input['data_fim']));
			if(empty($input['fase_anterior_id'])) {
				$input['fase_anterior_id'] = null;
			}
			if(($input['inicial'] == 'true') && $this->existeFaseInicial($input['campeonatos_id'], $id)) {
				return Response::json(array('success'=>false,
					'message'=>'Já existe uma Fase Inicial cadastrada para este campeonato'),300);
			}
			if(($input['final'] == 'true') && $this->existeFaseFinal($input['campeonatos_id'], $id)) {
				return Response::json(array('success'=>false,
					'message'=>'Já existe uma Fase Final cadastrada para este campeonato'),300);
			}
			$fase->update($input);

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
		$this->campeonatoFase->find($id)->delete();

		return Response::json(array('success'=>true));
	}

	public function abreFase() {
		// verifica se a fase anterior está fechada, caso contrário fechar automaticamente (avisar ao usuário)
		// Inscrever usuários classificados da fase anterior
		// Habilitar inserção de resultados
	}

	public function fechaFase() {
		// contabilizar jogos sem resultado (0 pontos para todos os participantes)
		// contabilizar pontuação e quantidade de classificados (por grupo)
		// Desabilitar inserção de resultados

		/*
		 * Gronograma de inserção de resultados para uma partida de campeonato
		 * - Usuário tem até a data final da fase para inserir o resultado (Caso não exista resultado, o jogo será definido como sem resultado, onde ambos os participantes ficam com pontos de último colocado)
		 * - Outro Usuário tem até 24 horas depois da hora de inserção do resultado para confirmar o mesmo (Caso não seja confirmado o resultado por outro usuário, o placar inserido será dado como definitivo).
		 * -
		 */
	}

	private function existeFaseInicial($campeonatos_id, $id_fase) {
		return CampeonatoFase::where('campeonatos_id', '=', $campeonatos_id)->where('inicial','=', true)->where('id', '!=', $id_fase)->get()->count();
	}

	private function existeFaseFinal($campeonatos_id, $id_fase) {
		return CampeonatoFase::where('campeonatos_id', '=', $campeonatos_id)->where('final','=', true)->where('id', '!=', $id_fase)->get()->count();
	}

}
