<?php

Use App\Repositories\PlataformaRepositoryEloquent;

class PlataformasController extends Controller {

	/**
	 * Plataforma Repository
	 *
	 * @var Plataforma
	 */
	protected $plataforma;

	public function __construct(PlataformaRepositoryEloquent $plataforma)
	{
		$this->plataforma = $plataforma;

	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$plataformas = $this->plataforma->all()->sortBy('descricao');
		return Response::json($plataformas->values()->all());
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::all();
		$validation = Validator::make($input, Plataforma::$rules);

		if ($validation->passes())
		{
			/*
			 * Movendo o arquivo para o diretório correto
			 */
			$arquivo = Input::hasFile('imagem_logomarca') ? Input::file('imagem_logomarca')
														  : null;

			if (isset($arquivo) && $arquivo->isValid()) {
				$destinationPath = 'uploads/';
				$fileName = 'plataforma_'.str_replace('.', '', microtime(true)).'.'.$arquivo->getClientOriginalExtension();
				$arquivo->move($destinationPath, $fileName);
				$input['imagem_logomarca'] = $fileName;
			}

			$this->plataforma->create($input);

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
		return Response::json($this->plataforma->find($id));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$input = array_except(Input::all(), array('_method', 'imagem_logomarca'));
		$validation = Validator::make($input, Plataforma::$rules);

		if ($validation->passes())
		{
			/*
			 * Movendo o arquivo para o diretório correto
			*/
			$arquivo = Input::hasFile('imagem_logomarca') ? Input::file('imagem_logomarca')
			: null;

			if (isset($arquivo) && $arquivo->isValid()) {
				$destinationPath = 'uploads/';
				$fileName = 'plataforma_'.str_replace('.', '', microtime(true)).'.'.$arquivo->getClientOriginalExtension();
				$arquivo->move($destinationPath, $fileName);
				$input['imagem_logomarca'] = $fileName;
			}

			$plataforma = $this->plataforma->find($id);
			$plataforma->update($input);

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
		$this->plataforma->delete($id);

		return Response::json(array('success'=>true));
	}

	public function getJogos($id, $apenasCampeonato = null) {
		$plataforma = $this->plataforma->find($id);
		return Response::json($plataforma->jogos($apenasCampeonato));
	}

}
