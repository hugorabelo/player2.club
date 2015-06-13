<?php

class UsuarioGruposController extends BaseController {

	/**
	 * UsuarioGrupo Repository
	 *
	 * @var UsuarioGrupo
	 */
	protected $usuarioGrupo;

	public function __construct(UsuarioGrupo $usuarioGrupo)
	{
		$this->usuarioGrupo = $usuarioGrupo;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$usuarioGrupos = $this->usuarioGrupo->all();

		return View::make('usuarioGrupos.index', compact('usuarioGrupos'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return View::make('usuarioGrupos.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::all();
		$validation = Validator::make($input, UsuarioGrupo::$rules);

		if ($validation->passes())
		{
			$this->usuarioGrupo->create($input);

			return Redirect::route('usuarioGrupos.index');
		}

		return Redirect::route('usuarioGrupos.create')
			->withInput()
			->withErrors($validation)
			->with('message', 'There were validation errors.');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$usuarioGrupo = $this->usuarioGrupo->find($id);

		if (is_null($usuarioGrupo))
		{
			return Redirect::route('usuarioGrupos.index');
		}

		return View::make('usuarioGrupos.edit', compact('usuarioGrupo'));
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
		$validation = Validator::make($input, UsuarioGrupo::$rules);

		if ($validation->passes())
		{
			$usuarioGrupo = $this->usuarioGrupo->find($id);
			$usuarioGrupo->update($input);

			return Redirect::route('usuarioGrupos.index', $id);
		}

		return Redirect::route('usuarioGrupos.edit', $id)
			->withInput()
			->withErrors($validation)
			->with('message', 'There were validation errors.');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$this->usuarioGrupo->find($id)->delete();

		return Redirect::route('usuarioGrupos.index');
	}

}
