<?php

class UsuarioFasesController extends Controller {

	/**
	 * UsuarioFase Repository
	 *
	 * @var UsuarioFase
	 */
	protected $usuarioFase;

	public function __construct(UsuarioFase $usuarioFase)
	{
		$this->usuarioFase = $usuarioFase;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$usuarioFases = $this->usuarioFase->all();

		return View::make('usuarioFases.index', compact('usuarioFases'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return View::make('usuarioFases.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::all();
		$validation = Validator::make($input, UsuarioFase::$rules);

		if ($validation->passes())
		{
			$this->usuarioFase->create($input);

			return Redirect::route('usuarioFases.index');
		}

		return Redirect::route('usuarioFases.create')
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
		$usuarioFase = $this->usuarioFase->find($id);

		if (is_null($usuarioFase))
		{
			return Redirect::route('usuarioFases.index');
		}

		return View::make('usuarioFases.edit', compact('usuarioFase'));
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
		$validation = Validator::make($input, UsuarioFase::$rules);

		if ($validation->passes())
		{
			$usuarioFase = $this->usuarioFase->find($id);
			$usuarioFase->update($input);

			return Redirect::route('usuarioFases.index', $id);
		}

		return Redirect::route('usuarioFases.edit', $id)
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
		$this->usuarioFase->find($id)->delete();

		return Redirect::route('usuarioFases.index');
	}

}
