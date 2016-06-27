<?php

class TipoCompetidorController extends BaseController {

	/**
	 * TipoCompetidor Repository
	 *
	 * @var TipoCompetidor
	 */
	protected $tipoCompetidor;

	public function __construct(TipoCompetidor $tipoCompetidor)
	{
		$this->tipoCompetidor = $tipoCompetidor;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index() {
		$tiposcompetidor = TipoCompetidor::get()->sortBy('descricao');
		return Response::json($tiposcompetidor->values()->all());
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store() {
		$input = Input::all();
		$validation = Validator::make($input, TipoCompetidor::$rules);

		if ($validation->passes())
		{
			TipoCompetidor::create(Input::all());

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
        return Response::json(TipoCompetidor::find($id));
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
		$validation = Validator::make($input, TipoCompetidor::$rules);

		if ($validation->passes())
		{
			$tipoCompetidor = $this->tipoCompetidor->find($id);
			$tipoCompetidor->update($input);

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
		TipoCompetidor::destroy($id);

		return Response::json(array('success'=>true));
	}

}
