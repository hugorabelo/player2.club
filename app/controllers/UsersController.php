<?php

class UsersController extends BaseController {

	/**
	 * User Repository
	 *
	 * @var User
	 */
	protected $user;

	public function __construct(User $user)
	{
		$this->user = $user;
	}

	public function show($id) {
		$usuario = User::find($id);
		return Response::json($usuario);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$usuarios = User::get();
		foreach($usuarios as $usuario) {
			$usuario->descricaoTipo = $usuario->usuarioTipo()->descricao;
		}
		return Response::json($usuarios);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::except('_token');
		$validation = Validator::make($input, User::$rules);

		if ($validation->passes())
		{
			$input['password'] = Hash::make($input['password']);

			$this->user->create($input);

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
		$usuario = $this->user->find($id);

		$usuarioTipos = UsuarioTipo::get();
		return Response::json(compact('usuario', 'usuarioTipos'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$input = array_except(Input::all(), array('_method', '_token'));
		User::$rules['email'] = 'required|email|unique:users,email,' . $id;
		User::$rules['password'] = '';
		$validation = Validator::make($input, User::$rules);

		if ($validation->passes())
		{
			$user = $this->user->find($id);
			if(isset($input['password']) && ($input['password'] != '')) {
				$input['password'] = Hash::make($input['password']);
			} else {
				array_pull($input, 'password');
			}
			$user->update($input);

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
		$this->user->find($id)->delete();

		return Response::json(array('success'=>true));
	}

}
