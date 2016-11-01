<?php

class UsersController extends Controller {

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
        $usuario->seguidores = $usuario->seguidores()->get();
        $usuario->seguindo = $usuario->seguindo()->get();
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

			/*
			 * Movendo o arquivo para o diretório correto
			 */
			$arquivo = Input::hasFile('imagem_perfil') ? Input::file('imagem_perfil')
				: null;

			if (isset($arquivo) && $arquivo->isValid()) {
				$destinationPath = 'uploads/usuarios/';
				$fileName = 'usuario_'.str_replace('.', '', microtime(true)).'.'.$arquivo->getClientOriginalExtension();
				$arquivo->move($destinationPath, $fileName);
				$input['imagem_perfil'] = $fileName;
			} else {
				array_pull($input, 'imagem_perfil');
			}

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

		return Response::json($usuario);
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

			/*
			 * Movendo o arquivo para o diretório correto
			 */
			$arquivo = Input::hasFile('imagem_perfil') ? Input::file('imagem_perfil')
				: null;

			if (isset($arquivo) && $arquivo->isValid()) {
				$destinationPath = 'uploads/usuarios/';
				$fileName = 'usuario_'.str_replace('.', '', microtime(true)).'.'.$arquivo->getClientOriginalExtension();
				$arquivo->move($destinationPath, $fileName);
				$input['imagem_perfil'] = $fileName;
			} else {
				array_pull($input, 'imagem_perfil');
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

	/**
	 * Retorna uma lista com os campeonatos disponiveis para determinado usuario
	 * de acordo com as plataformas nas quais o usuario estiver cadastrado
	 * A lista vai exibir apenas os campeonatos que nao estejam com todas as vagas preenchidas
	 *
	 * @param int $id_usuario
	 * @return Response
	 */
	public function listaCampeonatosDisponiveis($id_usuario) {
		$plataformasDoUsuario = UserPlataforma::where("users_id", "=", $id_usuario)->get(array("plataformas_id"))->toArray();
		if(empty($plataformasDoUsuario)) {
			$plataformasDoUsuario = array("plataformas"=>0);
		}
		$campeonatosUsuario = CampeonatoUsuario::where("users_id", "=", $id_usuario)->get(array("campeonatos_id"))->toArray();
		if(empty($campeonatosUsuario)) {
			$campeonatosUsuario = array("campeonatos_id"=>0);
		}
		$campeonatosDisponiveisNaPlataforma = Campeonato::whereIn("plataformas_id", $plataformasDoUsuario)->whereNotIn("id", $campeonatosUsuario)->get();
		foreach($campeonatosDisponiveisNaPlataforma as $campeonato) {
			//Log::info($campeonato->id.': '.$campeonato->maximoUsuarios());
		}

		return Response::json($campeonatosDisponiveisNaPlataforma);
	}

	/**
	 * Retorna uma lista com os campeonatos nos quais o usuario esta inscrito
	 *
	 * @param int $idUsuario
	 * @return Response
	 */
	public function listaCampeonatosInscritos($idUsuario) {
		$campeonatosUsuario = CampeonatoUsuario::where("users_id", "=", $idUsuario)->get(array("campeonatos_id"))->toArray();
		$campeonatosInscritos = Campeonato::findMany($campeonatosUsuario);

		return Response::json($campeonatosInscritos);
	}

	/**
	 * Retorna uma lista com todas as partidas do usuário
	 *
	 * @param int $idUsuario
	 * @return Response
	 */
	public function listaPartidas($idUsuario, $idCampeonato = null) {
		$usuario = $this->user->find($idUsuario);
		if($usuario == null) {
			return Response::json();
		}
		$partidas = $usuario->partidas($idCampeonato);
		return Response::json($partidas);
	}

	public function adicionaSeguidor() {
		$input = Input::except('_token');
		$idUsuario = $input['idUsuarioSeguidor'];
        $idMestre = $input['idUsuarioMestre'];
	    $usuario = $this->user->find($idUsuario);
        if($usuario == null) {
            return Response::json();
        }
        $usuario->seguir($idMestre);
        return Response::json();
    }

    public function removeSeguidor() {
        $input = Input::except('_token');
        $idUsuario = $input['idUsuarioSeguidor'];
        $idMestre = $input['idUsuarioMestre'];
        $usuario = $this->user->find($idUsuario);
        if($usuario == null) {
            return Response::json();
        }
        $usuario->deixarDeSeguir($idMestre);
        return Response::json();
    }

    public function seguidores($idUsuario) {
        $usuario = $this->user->find($idUsuario);
		if($usuario == null) {
			return Response::json();
		}
        $seguidores = $usuario->seguidores()->get();
        return Response::json($seguidores);
    }

    public function seguindo($idUsuario) {
        $usuario = $this->user->find($idUsuario);
		if($usuario == null) {
			return Response::json();
		}
        $seguindo = $usuario->seguindo()->get();
        return Response::json($seguindo);
    }

    public function listaPostsUsuario() {
        $input = Input::except('_token');
        $idUsuario = $input['idUsuario'];
        $idUsuarioLeitor = $input['idUsuarioLeitor'];
        $quantidade = $input['quantidade'];
        $usuario = $this->user->find($idUsuario);
		if($usuario == null) {
			return Response::json();
		}
        $posts = $usuario->getPosts($idUsuarioLeitor, $quantidade);
        return Response::json($posts);
    }

	public function segue() {
		$input = Input::except('_token');
		$idUsuario = $input['idUsuarioSeguidor'];
		$idMestre = $input['idUsuarioMestre'];
		$usuario = $this->user->find($idUsuario);
		if($usuario == null) {
			return Response::json();
		}
		return Response::json(array('segue'=>$usuario->segue($idMestre)));
	}

	public function segueJogo() {
		$input = Input::except('_token');
		$idUsuario = $input['idUsuarioSeguidor'];
		$idJogo = $input['idJogo'];
		$usuario = $this->user->find($idUsuario);
		if($usuario == null) {
			return Response::json();
		}
		return Response::json(array('segue'=>$usuario->segueJogo($idJogo)));
	}

	public function listaJogos($idUsuario) {
	    $usuario = $this->user->find($idUsuario);
        if($usuario == null) {
            return Response::json();
        }
        $jogos = $usuario->jogos()->get();
        return Response::json(array('jogos'=> $jogos));
    }

    public function seguirJogo() {
        $input = Input::except('_token');
        $idUsuario = $input['idUsuarioSeguidor'];
        $idJogo = $input['idJogo'];
        $usuario = $this->user->find($idUsuario);
        if($usuario == null) {
            return Response::json();
        }
        $usuario->seguirJogo($idJogo);
    }

    public function removeSeguidorJogo() {
        $input = Input::except('_token');
        $idUsuario = $input['idUsuarioSeguidor'];
        $idJogo = $input['idJogo'];
        $usuario = $this->user->find($idUsuario);
        if($usuario == null) {
            return Response::json();
        }
        $usuario->deixarDeSeguirJogo($idJogo);
        return Response::json();
    }

	public function getFeed($idUsuario) {
		$usuario = $this->user->find($idUsuario);
		if($usuario == null) {
			return Response::json();
		}
		$atividades = $usuario->getAtividades();
		foreach ($atividades as $atividade) {
			if(isset($atividade->curtida_id)) {
				$curtida = DB::table('curtida')->where('id','=',$atividade->curtida_id)->first();
				$post = Post::find($curtida->post_id);
				$atividade->objeto = $post;
				$atividade->descricao = 'messages.curtiu';
			} else if(isset($atividade->post_id)) {
				$post = Post::find($atividade->post_id);
				if(isset($post->post_id)) {
					$post_compartilhado = Post::find($post->post_id);
					$post_compartilhado->usuario = User::find($post_compartilhado->users_id);
					$post->compartilhamento = $post_compartilhado;
					$atividade->objeto = $post;
					$atividade->descricao = 'messages.compartilhou';
				} else {
					$atividade->objeto = $post;
					$atividade->descricao = 'messages.publicou';
				}
			} else if(isset($atividade->comentarios_id)) {
				$comentario = Comentario::find($atividade->comentarios_id);
				$atividade->objeto = $comentario;
				$atividade->descricao = 'messages.comentou';
			} else if(isset($atividade->seguidor_id)) {
				$seguidor = DB::table('seguidor')->where('id','=',$atividade->seguidor_id)->first();
				$usuarioMestre = User::find($seguidor->users_id_mestre);
				$atividade->objeto = $usuarioMestre;
				$atividade->descricao = 'messages.seguiu';
			} else if(isset($atividade->curtida_comentario_id)) {
				$curtida_comentario = DB::table('curtida_comentario')->where('id','=',$atividade->curtida_comentario_id)->first();
				$comentario_curtido = Post::find($curtida_comentario->comentario_id);
				$atividade->objeto = $comentario_curtido;
				$atividade->descricao = 'messages.curtiu_comentario';
			} else if(isset($atividade->seguidor_jogo_id)) {
				$seguidor_jogo = DB::table('seguidor_jogo')->where('id','=',$atividade->seguidor_jogo_id)->first();
				$jogo = Jogo::find($seguidor_jogo->jogos_id);
				$atividade->objeto = $jogo;
				$atividade->descricao = 'messages.seguiu_jogo';
			}
			$usuario = User::find($atividade->users_id);
			$atividade->usuario = $usuario;
		}
		return Response::json($atividades);
	}


}
