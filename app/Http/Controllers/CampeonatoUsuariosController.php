<?php

class CampeonatoUsuariosController extends Controller {

	/**
	 * CampeonatoUsuario Repository
	 *
	 * @var CampeonatoUsuario
	 */
	protected $campeonatoUsuario;

	public function __construct(CampeonatoUsuario $campeonatoUsuario)
	{
		$this->campeonatoUsuario = $campeonatoUsuario;
	}

	public function index() {
		return null;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function show($id)
	{
		$campeonatoUsuarios = CampeonatoUsuario::where('campeonatos_id','=',$id)->get();
		foreach($campeonatoUsuarios as $usuario) {
			$usuario->usuario = $usuario->usuario()->nome;
		}
		return Response::json($campeonatoUsuarios);
	}

	public function getUsuarioNaoAdministrador($id) {
		$campeonatoAdministradores = CampeonatoAdmin::where('campeonatos_id','=',$id)->get(array('users_id'));
		$campeonatoAdministradores = $campeonatoAdministradores->toArray();
		$campeonatoUsuarios = CampeonatoUsuario::where('campeonatos_id','=',$id)->
												 whereNotIn('users_id', $campeonatoAdministradores)->get();
		foreach($campeonatoUsuarios as $usuario) {
			$usuario->usuario = $usuario->usuario()->nome;
		}
		return Response::json($campeonatoUsuarios);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::all();
		$validation = Validator::make($input, CampeonatoUsuario::$rules);

		if ($validation->passes())
		{
            $campeonato = Campeonato::find($input['campeonatos_id']);
            if($campeonato->usuariosInscritos()->count() < $campeonato->maximoUsuarios()) {
                $this->campeonatoUsuario->create($input);

                return Response::json(array('success'=>true));
            }

            return Response::json(array('success' => false,
                'errors' => array('messages.campeonato_sem_vagas')), 300);
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
		$this->campeonatoUsuario->find($id)->delete();

		return Response::json(array('success'=>true));
	}

}
