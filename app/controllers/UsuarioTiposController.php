<?php

class UsuarioTiposController extends BaseController {

	/**
	 * UsuarioTipo Repository
	 *
	 * @var UsuarioTipo
	 */
	protected $usuarioTipo;

	public function __construct(UsuarioTipo $usuarioTipo)
	{
		$this->usuarioTipo = $usuarioTipo;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return Response::json(UsuarioTipo::get());
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::all();
		$validation = Validator::make($input, UsuarioTipo::$rules);

		if ($validation->passes())
		{
			UsuarioTipo::create($input);

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
		return Response::json(UsuarioTipo::find($id));
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
		$validation = Validator::make($input, UsuarioTipo::$rules);

		if ($validation->passes())
		{
			$usuarioTipo = $this->usuarioTipo->find($id);
			$usuarioTipo->update($input);

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
		UsuarioTipo::destroy($id);

		return Response::json(array('success'=>true));
	}

}
