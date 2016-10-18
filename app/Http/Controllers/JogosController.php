<?php

class JogosController extends Controller {

	/**
	 * Jogo Repository
	 *
	 * @var Jogo
	 */
	protected $jogo;

	public function __construct(Jogo $jogo)
	{
		$this->jogo = $jogo;
	}

    public function show($id) {
        $jogo = Jogo::find($id);
        $jogo->seguidores = $jogo->seguidores()->get();
        return Response::json($jogo);
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return Response::json(Jogo::get());
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{

		$input = Input::all();

		$validation = Validator::make($input, Jogo::$rules);

		if ($validation->passes())
		{
			/*
			 * Movendo o arquivo para o diretório correto
			 */

			$arquivo = Input::hasFile('imagem_capa') ? Input::file('imagem_capa')
			: null;

			if (isset($arquivo) && $arquivo->isValid()) {
				$destinationPath = 'uploads/';
				$fileName = 'jogo_'.str_replace('.', '', microtime(true)).'.'.$arquivo->getClientOriginalExtension();
				$arquivo->move($destinationPath, $fileName);
				$input['imagem_capa'] = $fileName;
			}

			Jogo::create($input);

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
		return Response::json(Jogo::find($id));
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
		$validation = Validator::make($input, Jogo::$rules);

		if ($validation->passes())
		{
			/*
			 * Movendo o arquivo para o diretório correto
			 */

			$arquivo = Input::hasFile('imagem_capa') ? Input::file('imagem_capa')
			: null;

			if (isset($arquivo) && $arquivo->isValid()) {
				$destinationPath = 'uploads/';
				$fileName = 'jogo_'.str_replace('.', '', microtime(true)).'.'.$arquivo->getClientOriginalExtension();
				$arquivo->move($destinationPath, $fileName);
				$input['imagem_capa'] = $fileName;
			}

			$jogo = $this->jogo->find($id);
			$jogo->update($input);

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
		Jogo::destroy($id);

		return Response::json(array('success'=>true));
	}

	public function getTiposDeCampeonato($id) {
		$jogo = Jogo::find($id);
		if(isset($jogo)) {
			return Response::json($jogo->tiposCampeonato());
		}
		return Response::json();
	}

}
