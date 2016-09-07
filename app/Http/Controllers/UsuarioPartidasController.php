<?php

class UsuarioPartidasController extends Controller {

	/**
	 * UsuarioPartida Repository
	 *
	 * @var UsuarioPartida
	 */
	protected $usuarioPartida;

	public function __construct(UsuarioPartida $usuarioPartida)
	{
		$this->usuarioPartida = $usuarioPartida;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$usuarioPartidas = $this->usuarioPartida->all();

		return View::make('usuarioPartidas.index', compact('usuarioPartidas'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return View::make('usuarioPartidas.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::all();
		$validation = Validator::make($input, UsuarioPartida::$rules);

		if ($validation->passes())
		{
			$this->usuarioPartida->create($input);

			return Redirect::route('usuarioPartidas.index');
		}

		return Redirect::route('usuarioPartidas.create')
			->withInput()
			->withErrors($validation)
			->with('message', 'There were validation errors.');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$usuarioPartida = $this->usuarioPartida->findOrFail($id);

		return View::make('usuarioPartidas.show', compact('usuarioPartida'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$usuarioPartida = $this->usuarioPartida->find($id);

		if (is_null($usuarioPartida))
		{
			return Redirect::route('usuarioPartidas.index');
		}

		return View::make('usuarioPartidas.edit', compact('usuarioPartida'));
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
		$validation = Validator::make($input, UsuarioPartida::$rules);

		if ($validation->passes())
		{
			$usuarioPartida = $this->usuarioPartida->find($id);
			$usuarioPartida->update($input);

			return Redirect::route('usuarioPartidas.show', $id);
		}

		return Redirect::route('usuarioPartidas.edit', $id)
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
		$this->usuarioPartida->find($id)->delete();

		return Redirect::route('usuarioPartidas.index');
	}

}
