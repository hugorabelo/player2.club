<?php

use Illuminate\Support\Collection;

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
        $usuario->seguidores = $usuario->seguidores()->orderBy('ultimo_login', 'desc')->get()->take(6);
        $usuario->seguindo = $usuario->seguindo()->orderBy('ultimo_login', 'desc')->get()->take(6);
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
		foreach ($input as $key=>$valor) {
			if($valor == 'undefined' || $valor == 'null') {
				$input[$key] = null;
			}
		}
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
			$arquivoPerfil = Input::hasFile('imagem_perfil') ? Input::file('imagem_perfil') : null;

			if (isset($arquivoPerfil) && $arquivoPerfil->isValid()) {
				$destinationPath = 'uploads/usuarios/';
				$fileName = 'usuario_'.str_replace('.', '', microtime(true)).'.'.$arquivoPerfil->getClientOriginalExtension();
				$arquivoPerfil->move($destinationPath, $fileName);
				$input['imagem_perfil'] = $fileName;
			} else {
				array_pull($input, 'imagem_perfil');
			}

			$arquivoCapa = Input::hasFile('imagem_capa') ? Input::file('imagem_capa') : null;

			if (isset($arquivoCapa) && $arquivoCapa->isValid()) {
				$destinationPath = 'uploads/usuarios/capa/';
				$fileName = 'usuario_'.str_replace('.', '', microtime(true)).'.'.$arquivoCapa->getClientOriginalExtension();
				$arquivoCapa->move($destinationPath, $fileName);
				$input['imagem_capa'] = $fileName;
			} else {
				array_pull($input, 'imagem_capa');
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
		$campeonatosAbertos = app()->make(Collection::class);
		foreach($campeonatosDisponiveisNaPlataforma as $campeonato) {
			if($campeonato->status() == 1) {
				$campeonatosAbertos->push($campeonato);
			}
		}

		return Response::json($campeonatosAbertos);
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

    public function listaPartidasEmAberto($idUsuario, $idCampeonato = null) {
        $usuario = $this->user->find($idUsuario);
        if($usuario == null) {
            return Response::json();
        }
        $partidas = $usuario->partidasEmAberto($idCampeonato);
        return Response::json($partidas);
    }

    public function listaPartidasDisputadas($idUsuario, $idCampeonato = null) {
        $usuario = $this->user->find($idUsuario);
        if($usuario == null) {
            return Response::json();
        }
        $partidas = $usuario->partidasDisputadas($idCampeonato);
        return Response::json($partidas);
    }

	public function listaPartidasNaoDisputadas($idUsuario, $idCampeonato = null) {
		$usuario = $this->user->find($idUsuario);
		if($usuario == null) {
			return Response::json();
		}
		$partidas = $usuario->partidasNaoDisputadas($idCampeonato);
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

		$evento = NotificacaoEvento::where('valor','=','seguir_usuario')->first();
		if(isset($evento)) {
			$idEvento = $evento->id;
		}

		$notificacao = new Notificacao();
		$notificacao->id_remetente = $idUsuario;
		$notificacao->id_destinatario = $idMestre;
		$notificacao->evento_notificacao_id = $idEvento;
		$notificacao->save();

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

	public function listaJogos($idUsuario, $count = 0) {
	    $usuario = $this->user->find($idUsuario);
        if($usuario == null) {
            return Response::json();
        }
        $jogos = $usuario->jogos()->get();
		if($count > 0) {
			$jogos = $jogos->take($count);
		}
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

	public function getFeed($idUsuario, $todos = false, $offset = 0, $quantidade = 5) {
		$usuario = $this->user->find($idUsuario);
		if($usuario == null) {
			return Response::json();
		}
		$atividades = $usuario->getAtividades($todos, $offset, $quantidade);
		foreach ($atividades as $atividade) {
			if(isset($atividade->post_id)) {
				$post = Post::find($atividade->post_id);
				if(isset($post->jogos_id)) {
					$post->descricao_jogo = Jogo::find($post->jogos_id)->descricao;
					$atividade->descricao = 'messages.escreveu_sobre_jogo';
				}
				if(isset($post->destinatario_id)) {
					$post->descricao_destinatario = User::find($post->destinatario_id)->nome;
					$atividade->descricao = 'messages.mensagem_para_usuario';
				}
				$post->imagens = $post->getimages();
				if(isset($post->post_id)) {
					$post_compartilhado = Post::find($post->post_id);
					$post_compartilhado->usuario = User::find($post_compartilhado->users_id);
					$post_compartilhado->imagens = $post_compartilhado->getimages();
					$post->compartilhamento = $post_compartilhado;
					$atividade->objeto = $post;
					$atividade->descricao = isset($atividade->descricao) ? $atividade->descricao : 'messages.compartilhou';
				} else {
					$atividade->objeto = $post;
					$atividade->descricao = isset($atividade->descricao) ? $atividade->descricao : 'messages.publicou';
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
			} else if(isset($atividade->seguidor_jogo_id)) {
				$seguidor_jogo = DB::table('seguidor_jogo')->where('id','=',$atividade->seguidor_jogo_id)->first();
				$jogo = Jogo::find($seguidor_jogo->jogos_id);
				$atividade->objeto = $jogo;
				$atividade->descricao = 'messages.seguiu_jogo';
			} else if(isset($atividade->partidas_id)) {
			    $partida = Partida::find($atividade->partidas_id);
                $partida->usuarios = $partida->usuarios();
				$partida->campeonato = $partida->campeonato();
                $atividade->objeto = $partida;
                $atividade->descricao = 'messages.disputou_partida';
            } else if(isset($atividade->campeonato_usuarios_id)) {
                $campeonatoUsuario = CampeonatoUsuario::find($atividade->campeonato_usuarios_id);
                $campeonato = Campeonato::find($campeonatoUsuario->campeonatos_id);
                $atividade->objeto = $campeonato;
                $atividade->descricao = 'messages.inscreveu_campeonato';
            }
			$usuario = User::find($atividade->users_id);
			$atividade->usuario = $usuario;
		}
		return Response::json($atividades);
	}

	public function desistirCampeonato($idCampeonato) {
		$idUsuario = Auth::getUser()->id;
		$idUsuarioCampeonato = CampeonatoUsuario::where('users_id','=',$idUsuario)->where('campeonatos_id','=',$idCampeonato)->first()->id;
		$usuarioCampeonato = CampeonatoUsuario::find($idUsuarioCampeonato);
		$usuarioCampeonato->delete();

		return Response::json(array('success'=>true));
	}

	public function listaNotificacoes($lidas = false) {
		$idUsuario = Auth::getUser()->id;
		$usuario = User::find($idUsuario);
		$notificacoes = $usuario->getNotificacoes($lidas);
        foreach ($notificacoes as $notificacao) {
            $evento = NotificacaoEvento::find($notificacao->evento_notificacao_id);
            $remetente = User::find($notificacao->id_remetente);
            if(isset($remetente)) {
                $nome_completo = explode(' ', $remetente->nome);
                $nome_completo = count($nome_completo) > 2 ? array_shift($nome_completo).' '.array_pop($nome_completo) : $remetente->nome;
                $remetente->nome = $nome_completo;
                $notificacao->remetente = $remetente;
            }
			switch ($evento->valor) {
				case 'fase_iniciada':
				case 'fase_encerrada':
				case 'fase_encerramento_breve':
					$fase = CampeonatoFase::find($notificacao->item_id);
					$notificacao->nome_campeonato = $fase->campeonato()->descricao;
					$notificacao->nome_fase = $fase->descricao;
					$notificacao->item_id = $fase->campeonato()->id;
					break;
			}
            $notificacao->mensagem = $evento->mensagem;
            $notificacao->tipo_evento = $evento->valor;
        }
		return Response::json($notificacoes);
	}

}
