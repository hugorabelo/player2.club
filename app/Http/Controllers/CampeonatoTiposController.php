<?php

class CampeonatoTiposController extends Controller {

	/**
	 * CampeonatoTipo Repository
	 *
	 * @var CampeonatoTipo
	 */
	protected $campeonatoTipo;

	public function __construct(CampeonatoTipo $campeonatoTipo)
	{
		$this->campeonatoTipo = $campeonatoTipo;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index() {
        $campeonatoTipos = CampeonatoTipo::get();
        foreach ($campeonatoTipos as $campeonatoTipo) {
            $modelo = ModeloCampeonato::find($campeonatoTipo->modelo_campeonato_id);
            $campeonatoTipo->modelo_campeonato = $modelo->descricao;
        }
        $campeonatoTipos = $campeonatoTipos->sortBy('modelo_campeonato');
        return Response::json($campeonatoTipos->values()->all());
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store() {
		$input = Input::all();

		$validation = Validator::make($input, CampeonatoTipo::$rules);

		if ($validation->passes())
		{
			CampeonatoTipo::create(Input::all());

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
        return Response::json(CampeonatoTipo::find($id));
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
		$validation = Validator::make($input, CampeonatoTipo::$rules);

		if ($validation->passes())
		{
			$campeonatoTipo = $this->campeonatoTipo->find($id);
			$campeonatoTipo->update($input);

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
		CampeonatoTipo::destroy($id);

		return Response::json(array('success'=>true));
	}

}
