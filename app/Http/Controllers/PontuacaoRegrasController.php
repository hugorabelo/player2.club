<?php

class PontuacaoRegrasController extends Controller {

	/**
	 * PontuacaoRegra Repository
	 *
	 * @var PontuacaoRegra
	 */
	protected $pontuacaoRegra;

	public function __construct(PontuacaoRegra $pontuacaoRegra)
	{
		$this->pontuacaoRegra = $pontuacaoRegra;
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
		$pontuacaoRegras = PontuacaoRegra::where('campeonato_fases_id','=',$id)->get();
		return Response::json($pontuacaoRegras);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::all();
		$validation = Validator::make($input, PontuacaoRegra::$rules);

		if ($validation->passes())
		{
			$maximo_jogadores = $this->getMaximoJogadores($input['campeonato_fases_id']);
			if($input['posicao'] > $maximo_jogadores) {
				return Response::json(array('success'=>false,
					'errors'=>$validation->getMessageBag()->all(),
					'message'=>'A posição escolhida é maior que a quantidade de jogadores permitidos por partida: '.$maximo_jogadores),300);
			}
			if($this->existeRegra($input['posicao'], $input['campeonato_fases_id'])) {
				return Response::json(array('success'=>false,
					'errors'=>$validation->getMessageBag()->all(),
					'message'=>'Já existe uma pontuação cadastrada para esta posição'),300);
			}
			$this->pontuacaoRegra->create($input);

			return Response::json(array('success'=>true));
		}

		return Response::json(array('success'=>false,
			'errors'=>$validation->getMessageBag()->all(),
			'message'=>'There were validation errors.'),300);

	}

	private function existeRegra($posicao, $campeonato_fases_id) {
		$regraExistente = PontuacaoRegra::where('posicao', '=', $posicao)->where('campeonato_fases_id','=',$campeonato_fases_id)->count();
		return $regraExistente;
	}

	private function getMaximoJogadores($campeonato_fases_id) {
		$campeonato_fase = CampeonatoFase::find($campeonato_fases_id);
		$campeonato = Campeonato::find($campeonato_fase['campeonatos_id']);
		$campeonato_tipo = CampeonatoTipo::find($campeonato['campeonato_tipos_id']);
		$maximo_jogadores = $campeonato_tipo['maximo_jogadores_partida'];
		return $maximo_jogadores;
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		return Response::json(PontuacaoRegra::find($id));
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
		$validation = Validator::make($input, PontuacaoRegra::$rules);

		if ($validation->passes())
		{

			$pontuacao = $this->pontuacaoRegra->find($id);
			$pontuacao->update($input);

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
		$this->pontuacaoRegra->find($id)->delete();

		return Response::json(array('success'=>true));
	}

}
