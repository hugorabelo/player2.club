<?php

class AcessoCampeonatoController extends Controller {

	/**
	 * AcessoCampeonato Repository
	 *
	 * @var AcessoCampeonato
	 */
	protected $acessoCampeonato;

	public function __construct(AcessoCampeonato $acessoCampeonato)
	{
		$this->acessoCampeonato = $acessoCampeonato;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index() {
		$acessos = AcessoCampeonato::get()->sortBy('descricao');
		return Response::json($acessos->values()->all());
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store() {
		$input = Input::all();
		$validation = Validator::make($input, AcessoCampeonato::$rules);

		if ($validation->passes())
		{
			AcessoCampeonato::create(Input::all());

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
        return Response::json(AcessoCampeonato::find($id));
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
		$validation = Validator::make($input, AcessoCampeonato::$rules);

		if ($validation->passes())
		{
			$acessoCampeonato = $this->acessoCampeonato->find($id);
			$acessoCampeonato->update($input);

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
	public function destroy($id) {
		AcessoCampeonato::destroy($id);

		return Response::json(array('success'=>true));
	}

}
