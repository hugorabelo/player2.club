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
		if(!isset($input['equipe_id']) && !isset($input['anonimo_id'])) {
			$usuario = Auth::getUser();
			$input['users_id'] = $usuario->id;
		}
		$validation = Validator::make($input, CampeonatoUsuario::$rules);

		if ($validation->passes())
		{
			$campeonato = Campeonato::find($input['campeonatos_id']);
			if($campeonato->tipo_competidor != 'equipe' && !isset($input['anonimo_id'])) {
				$usuarioPlataforma = UserPlataforma::where('users_id', '=', $usuario->id)->where('plataformas_id', '=', $campeonato->plataformas_id)->first();
				if($usuarioPlataforma == null) {
					return Response::json(array('success' => false,
						'errors' => array('messages.usuario_sem_plataforma')), 300);
				}
			}

			if($campeonato->usuariosInscritos()->count() < $campeonato->maximoUsuarios()) {
				if($campeonato->tipo_competidor == 'equipe') {
					if(isset($input['equipe_id'])) {
						$equipe = Equipe::find($input['equipe_id']);
						$idUsuarioLogado = Auth::getUser()->id;
						// Verificar se o usuario que está inscrevendo é administrador da equipe
						if(!$equipe->verificaFuncaoAdministrador($idUsuarioLogado)) {
							return Response::json(array('success' => false,
								'errors' => array('messages.inscricao_usuario_nao_administrador_equipe', $equipe->nome)), 300);
						}

						$administradoresEquipe = $equipe->administradores()->pluck('users_id');

						// Verificar se algum administrador da equipe já está cadastrado como administrador de outra equipe no mesmo campeonato
						foreach ($campeonato->usuariosInscritos() as $equipeInscrita) {
							foreach ($administradoresEquipe as $administrador) {
								if($equipeInscrita->verificaFuncaoAdministrador($administrador)) {
									$usuarioExistente = User::find($administrador);
									return Response::json(array('success' => false,
										'errors' => array('messages.inscricao_equipe_administrador_existente', $equipeInscrita->descricao, $usuarioExistente->nome)), 300);
								}
							}
						}


						$integrantes = $equipe->integrantes();
						if($integrantes->get()->count() < $campeonato->quantidade_minima_competidores) {
							return Response::json(array('success' => false,
								'errors' => array('messages.inscricao_equipe_sem_quantidade_minima')), 300);
						}
						$this->campeonatoUsuario->create($input);
						$idJogo = $campeonato->jogo()->id;
						foreach ($integrantes as $integrante) {
							if (!$integrante->segueJogo($idJogo)) {
								$integrante->seguirJogo($idJogo);
							}
						}
					}
				} else {
					$this->campeonatoUsuario->create($input);
					if(!isset($input['anonimo_id'])) {
						$idJogo = $campeonato->jogo()->id;
						if (!$usuario->segueJogo($idJogo)) {
							$usuario->seguirJogo($idJogo);
						}
					}
				}

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

	public function salvarTime() {
		$input = Input::all();
		$campeonatoUsuario = CampeonatoUsuario::find($input['idUsuario']);
		$time = null;
		if(!empty($input['idTime'])) {
			$campeonatoUsuario->time_id = $input['idTime'];
			$time = Time::find($input['idTime']);
		} else {
			$campeonatoUsuario->time_id = null;
		}
		$campeonatoUsuario->save();
		return $time;
	}

}
