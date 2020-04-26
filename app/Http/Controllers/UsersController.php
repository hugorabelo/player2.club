<?php

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use GuzzleHttp\Client;

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
		if($id == 'undefined' || $id == null) {
			return null;
		}
		$usuario = User::find($id);
        $usuario->seguidores = $usuario->seguidores()->orderBy('ultimo_login', 'desc')->get()->take(6);
        $usuario->seguindo = $usuario->seguindo()->orderBy('ultimo_login', 'desc')->get()->take(6);
		$usuario->equipesAdministradas = $usuario->equipesAdministradas()->get();
		return Response::json($usuario);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$usuarios = User::orderBy('usuario_tipos_id')->orderBy('nome')->orderBy('email')->get();
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

			$input['email'] = strtolower($input['email']);
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

            $input['email'] = strtolower($input['email']);
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
		if($id_usuario == 'undefined' || $id_usuario == null) {
			return null;
		}
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
		if($idUsuario == 'undefined' || $idUsuario == null) {
			return null;
		}
		$equipes = DB::table('integrante_equipe')->where('users_id','=',$idUsuario)->pluck('equipe_id');
		$campeonatosUsuario = CampeonatoUsuario::where("users_id", "=", $idUsuario)->orwhereIn("equipe_id", $equipes)->get(array("campeonatos_id"))->toArray();
		$campeonatosInscritos = Campeonato::findMany($campeonatosUsuario);

		foreach ($campeonatosInscritos as $campeonato) {
			$campeonato->jogo = $campeonato->jogo()->descricao;
			$campeonato->jogo_imagem = $campeonato->jogo()->imagem_capa;
			$campeonato->campeonatoTipo = $campeonato->campeonatoTipo()->descricao;
			$campeonato->plataforma = $campeonato->plataforma()->descricao;
			$campeonato->plataforma_imagem = $campeonato->plataforma()->imagem_logomarca;
			$campeonato->jogo_imagem = $campeonato->jogo()->imagem_capa;
			$campeonato->tipo_campeonato= $campeonato->campeonatoTipo()->descricao;
			$campeonato->status = $campeonato->status();
		}

		return Response::json($campeonatosInscritos);
	}

	/**
	 * Retorna uma lista com todas as partidas do usuário
	 *
	 * @param int $idUsuario
	 * @return Response
	 */
	public function listaPartidas($idUsuario, $idCampeonato = null, $confirmadas = true) {
		$usuario = $this->user->find($idUsuario);
		if($usuario == null) {
			return Response::json();
		}
		$partidas = $usuario->partidas($idCampeonato, $confirmadas);
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

		if($idUsuario != $idMestre) {
			$evento = NotificacaoEvento::where('valor','=','seguir_usuario')->first();
			if(isset($evento)) {
				$idEvento = $evento->id;
			}

			$notificacao = new Notificacao();
			$notificacao->id_remetente = $idUsuario;
			$notificacao->id_destinatario = $idMestre;
			$notificacao->evento_notificacao_id = $idEvento;
			$notificacao->save();
		}

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

	public function segue() {
		$input = Input::except('_token');
		$idUsuario = $input['idUsuarioSeguidor'];
		$idMestre = $input['idUsuarioMestre'];
		if($idUsuario == null || $idUsuario == 'undefined') {
			return Response::json();
		}
		$usuario = $this->user->find($idUsuario);
		if($usuario == null || empty($idMestre) || $idMestre == 'undefined' || $idMestre == null) {
			return Response::json();
		}
		return Response::json(array('segue'=>$usuario->segue($idMestre)));
	}

	public function segueJogo() {
		$input = Input::except('_token');
		$idUsuario = $input['idUsuarioSeguidor'];
		$idJogo = $input['idJogo'];
		if($idUsuario == null || $idUsuario == 'undefined') {
			return Response::json();
		}
		$usuario = $this->user->find($idUsuario);
		if($usuario == null || $idJogo == 'undefined' || $idJogo == null) {
			return Response::json();
		}
		return Response::json(array('segue'=>$usuario->segueJogo($idJogo)));
	}

	public function listaJogos($idUsuario, $count = 0) {
		if($idUsuario === 'undefined') {
			return Response::json();
		}
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
			if(isset($atividade->partidas_id)) {
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
		$usuarioLogado = Auth::user();
		$campeonato = Campeonato::find($idCampeonato);
		if($campeonato->tipo_competidor == 'equipe') {
			$idEquipesUsuario = $usuarioLogado->equipesAdministradas()->pluck('equipe_id');
			CampeonatoUsuario::whereIn('equipe_id',$idEquipesUsuario)->where('campeonatos_id','=',$idCampeonato)->delete();
		} else {
			CampeonatoUsuario::where('users_id','=',$usuarioLogado->id)->where('campeonatos_id','=',$idCampeonato)->delete();
		}

		return Response::json(array('success'=>true));
	}

	public function listaNotificacoes($lidas = false) {
		$idUsuario = Auth::user()->id;
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
					if(!isset($fase)) {
						continue;
					}
					$notificacao->nome_campeonato = $fase->campeonato()->descricao;
					$notificacao->nome_fase = $fase->descricao;
					$notificacao->item_id = $fase->campeonato()->id;
					break;
				case 'sorteou_clubes':
					$campeonato = Campeonato::find($notificacao->item_id);
					if(!isset($campeonato)) {
						continue;
					}
					$notificacao->nome_campeonato = $campeonato->descricao;
					break;
                case 'convite_equipe':
                case 'solicitacao_equipe':
                case 'aceitacao_equipe':
                case 'convite_equipe_aceito':
                    $equipe = Equipe::find($notificacao->item_id);
                    if(!isset($equipe)) {
                        continue;
                    }
                    $notificacao->nome_equipe = $equipe->nome;
                    break;
				case 'convite_campeonato':
					$campeonato = Campeonato::find($notificacao->item_id);
					if(!isset($campeonato)) {
						continue;
					}
					$notificacao->nome_campeonato = $campeonato->descricao;
			}
            $notificacao->mensagem = $evento->mensagem;
            $notificacao->tipo_evento = $evento->valor;
        }
		return Response::json($notificacoes);
	}

	function lerNotificacao() {
        $input = Input::except('_token');
        $notificacao = Notificacao::find($input['id']);
        $notificacao->lida = true;
        $notificacao->save();
		return Response::json(array('success'=>true));
    }

	function adicionarNotificacaoEmail() {
		$input = Input::except('_token');
		$idEvento = $input['id_evento'];
		$usuario = Auth::user();
		$usuario->adicionaNotificacaoPorEmail($idEvento);
		return Response::json(array('success'=>true));
	}

	function removerNotificacaoEmail() {
		$input = Input::except('_token');
		$idEvento = $input['id_evento'];
		$usuario = Auth::user();
		$usuario->removeNotificacaoPorEmail($idEvento);
		return Response::json(array('success'=>true));
	}

	function listaConversas() {
		$idUsuario = Auth::user()->id;
		$usuario = User::find($idUsuario);
		$conversas = $usuario->getConversas();
		foreach ($conversas as $conversa) {
			$remetente = User::find($conversa->id_remetente);
			if (isset($remetente)) {
				$nome_completo = explode(' ', $remetente->nome);
				$nome_completo = count($nome_completo) > 2 ? array_shift($nome_completo) . ' ' . array_pop($nome_completo) : $remetente->nome;
				$remetente->nome = $nome_completo;
				$conversa->remetente = $remetente;
			}
		}
		return $conversas;
	}

	function listaMensagens($idRemetente) {
		$idUsuario = Auth::user()->id;
		$usuario = User::find($idUsuario);
		$mensagens = $usuario->getMensagens($idRemetente);
		foreach ($mensagens as $mensagem) {
			$remetente = User::find($mensagem->id_remetente);
			if (isset($remetente)) {
				$nome_completo = explode(' ', $remetente->nome);
				$nome_completo = count($nome_completo) > 2 ? array_shift($nome_completo) . ' ' . array_pop($nome_completo) : $remetente->nome;
				$remetente->nome = $nome_completo;
				$mensagem->remetente = $remetente;
			}
		}
		return $mensagens;
	}

	function listaEquipes($idUsuario = null, $tipo = null) {
		if(!isset($idUsuario)) {
			$idUsuario = Auth::user()->id;
		}
		$usuario = User::find($idUsuario);
		$equipes = $usuario->equipes($tipo)->orderBy('nome')->get();
		foreach ($equipes as $equipe) {
			$equipe->integrantes = $equipe->integrantes()->get();
		}
		return Response::json($equipes);
	}

	function listaEquipesAdministradas() {
		$usuario = User::find(Auth::user()->id);
		$equipes = $usuario->equipesAdministradas()->orderBy('nome')->get();
		return Response::json($equipes);
	}

	function listaConvites() {
        $idUsuario = Auth::user()->id;
        $usuario = User::find($idUsuario);
        $convites = $usuario->getConvites();
        foreach ($convites as $convite) {
            $convite->status = 'aguardando';
            $situacao = User::where('email','=',$convite->email)->where('nome','<>','username')->count();
            if($situacao != 0) {
                $convite->status = 'aceito';
            }
        }
        return $convites;
    }

    function convidarUsuario() {
        $input = Input::except('_token');
        $idUsuario = Auth::user()->id;
        if(isset($input['email'])) {
            $usuario = User::find($idUsuario);
            $retorno = $usuario->convidar($input['email']);
            if($retorno != '') {
                return Response::json(array('success' => false,
                    'errors' => array($retorno)), 300);
            }
            return Response::json(array('success'=>true));
        }
    }

	function convidarParaCampeonato($idCampeonato, $idAmigo) {
		$evento = NotificacaoEvento::where('valor','=','convite_campeonato')->first();
		if(isset($evento)) {
			$idEvento = $evento->id;
		}

		$idUsuario = Auth::user()->id;
		$notificacao = new Notificacao();
		$notificacao->id_remetente = $idUsuario;
		$notificacao->id_destinatario = $idAmigo;
		$notificacao->evento_notificacao_id = $idEvento;
		$notificacao->item_id = $idCampeonato;
		$notificacao->save();
	}

	function finalizarWizard($idUsuario) {
		$user = User::find($idUsuario);
		$user->exibe_wizard = false;
		$user->save();
	}

	public function storeAnonimo() {
		$input = Input::except('_token');

		$validation = Validator::make($input, UserAnonimo::$rules);

		if ($validation->passes())
		{

			//insertGetId
			$idNovo = DB::table('users_anonimos')->insertGetId($input);

			return Response::json(array('success'=>true, 'idNovoUsuario'=>$idNovo));
		}

		return Response::json(array('success'=>false,
			'errors'=>$validation->getMessageBag()->all(),
			'message'=>'There were validation errors.'),300);
	}

    public function pesquisaPorNome($textoPesquisa) {
        $user = new User();
        return Response::json($user->pesquisaPorNome($textoPesquisa));
    }

    public function associarAnonimo() {
        $input = (object)Input::all();
        $usuarioCadastrado = $input->usuarioCadastrado;
        $usuarioAnonimo = $input->usuarioAnonimo;
        $idCampeonato = $usuarioAnonimo['pivot']['campeonatos_id'];

		$campeonato = Campeonato::find($idCampeonato);
		if($campeonato->verificaUsuarioInscrito($usuarioCadastrado['id'])) {
			if($campeonato->status() < 3) {
				CampeonatoUsuario::where('users_id','=',$usuarioCadastrado['id'])->where('campeonatos_id','=',$idCampeonato)->delete();
			} else {
				return Response::json(array('success'=>false,
					'errors'=>'messages.usuario_cadastrado_associacao',
					'message'=>'There were validation errors.'),300);
			}
		}

        // campeonato_usuarios
        $campeonatoUsuario = CampeonatoUsuario::where('campeonatos_id','=',$idCampeonato)->where('anonimo_id','=',$usuarioAnonimo['id'])->first();
        $campeonatoUsuario->users_id = $usuarioCadastrado['id'];
		$campeonatoUsuario->anonimo_id = null;
		$campeonatoUsuario->save();

        // usuario_fases
		// select id from campeonato_fases where campeonatos_id = $campeonatoUsuario->campeonatos_id;
		$campeonatoFases = CampeonatoFase::where('campeonatos_id','=',$idCampeonato)->get(array('id'));
        $usuariosFase = UsuarioFase::whereIn('campeonato_fases_id', $campeonatoFases)->where('anonimo_id','=',$usuarioAnonimo['id'])->get();
		foreach ($usuariosFase as $userFase) {
			$userFase->users_id = $usuarioCadastrado['id'];
			$userFase->anonimo_id = null;
			$userFase->save();
		}


        // usuario_grupos
		$faseGrupos = FaseGrupo::whereIn('campeonato_fases_id',$campeonatoFases)->get(array('id'));
		$usuariosGrupo = UsuarioGrupo::whereIn('fase_grupos_id', $faseGrupos)->where('anonimo_id','=',$usuarioAnonimo['id'])->get();
		foreach ($usuariosGrupo as $userGrupo) {
			$userGrupo->users_id = $usuarioCadastrado['id'];
			$userGrupo->anonimo_id = null;
			$userGrupo->save();
		}

        // usuario_partidas
		$partidas = Partida::whereIn('fase_grupos_id', $faseGrupos)->get(array('id'));
		$usuariosPartida = UsuarioPartida::whereIn('partidas_id', $partidas)->where('anonimo_id','=',$usuarioAnonimo['id'])->get();
		foreach ($usuariosPartida as $userPartida) {
			$userPartida->users_id = $usuarioCadastrado['id'];
			$userPartida->anonimo_id = null;
			$userPartida->save();
		}
    }

    public function verificarPendencias() {
        $idUsuario = Auth::user()->id;
        $pendencias = array();

        //Partida agendada não realizada
        $agora = Carbon::parse();
		$partidasNaoRealizadas = AgendamentoMarcacao::whereRaw("horario_inicio + (duracao * INTERVAL '1 MINUTES') < now() and status < 2 and (usuario_host = $idUsuario or usuario_convidado = $idUsuario) and partidas_id IN (select partidas_id FROM usuario_partidas where placar is null and users_id = $idUsuario)")->get();
		// Pegar os agendamentos e procurar as partidas sem resultado

		//TODO: Verificar partidas sem resutaldo
		foreach($partidasNaoRealizadas as $partida) {
			$partidaTemp = Partida::find($partida->partidas_id);
			$partida->usuarios = $partidaTemp->usuarios();
			$partida->campeonato = $partidaTemp->campeonato();
			$partida->horario_inicio = Carbon::parse($partida->horario_inicio)->format('d/m/Y H:i');
			$partida->detalhesPartida = $partidaTemp;
		}
		if(sizeof($partidasNaoRealizadas) > 0) {
			$pendencias['partidas_nao_realizadas'] = $partidasNaoRealizadas;
		}

		//Partida com resultado faltando confirmação
		$usuario = User::find($idUsuario);
		$partidasNaoConfirmadas = array();
		foreach($usuario->partidasEmAberto() as $partida) {
			if($partida->pode_confirmar_contestar) {
				$partida->data_placar = Carbon::parse($partida->data_placar)->format('d/m/Y H:i');
				$partidasNaoConfirmadas[] = $partida;
			}
		}
		if(sizeof($partidasNaoConfirmadas) > 0) {
			$pendencias['partidas_nao_confirmadas'] = $partidasNaoConfirmadas;
		}

        return Response::json($pendencias);
	}
	
	public function enviarNovaSenha() {
		$input = Input::except('_token');
		$email = $input['email'] ?? null;
		$base_path = URL::to('/')."/";
		
		if(isset($email)) {
			$email = strtolower($email);
			$user = User::whereRaw("LOWER(email) = '$email'")->first();
			if($user) {
				$user->token_redefinir_senha = md5(bcrypt($email.time().rand()));
				$expiracao = Carbon::now()->addHours(3);
				$user->token_redefinir_senha_expires = Carbon::parse($expiracao)->format('Y-m-d H:i:s');
				$user->save();

				//Enviar Email
				$conteudo = trans("messages.redefinir_senha_conteudo");
				$link = $base_path."redefinir_senha/".$user->token_redefinir_senha;
				$texto_link = trans("messages.redefinir_senha_texto_link");
				$texto_pos = trans("messages.redefinir_senha_texto_pos");
				\Mail::send('notificacao', ['conteudo' =>  $conteudo, 'destinatario' => $user, 'link' => $link, 'texto_link' => $texto_link, 'texto_pos' => $texto_pos], function($message) use ($user) {
					$message->from('contato@player2.club', $name = 'player2.club');
					$message->to($user->email, $name = $user->nome);
					$message->subject(trans("messages.redefinir_senha_assunto"));
				});
			} else {
				return Response::json(array('success'=>false,
					'errors'=>'messages.email_nao_cadastrado',
					'message'=>'There were validation errors.'),300);
			}
		}
		return Response::json(array('success'=>true));
	}

	public function cadastrarNovaSenha() {
		/*
		 tokenRedefinirSenha: $stateParams.token,
                        novaSenha: vm.new_password,
                        repetirSenha: vm.repeat_password
		*/
		$input = Input::except('_token');
		$tokenRedefinirSenha = $input['tokenRedefinirSenha'] ?? null;
		$novaSenha = $input['novaSenha'] ?? null;
		$repetirSenha = $input['repetirSenha'] ?? null;
		if($novaSenha !== $repetirSenha) {
			return Response::json(array('success'=>false,
				'errors'=>'messages.senhas_diferentes',
				'message'=>'There were validation errors.'),300);
			}
		if(empty($novaSenha)) {
				return Response::json(array('success'=>false,
					'errors'=>'messages.senha_nao_pode_ser_vazia',
					'message'=>'There were validation errors.'),300);
		}
		$user = User::where('token_redefinir_senha','=',$tokenRedefinirSenha)->first();
		if($user) {
			if(Carbon::now() < $user->token_redefinir_senha_expires) {
				$user->password = Hash::make($novaSenha);
				$user->token_redefinir_senha = null;
				$user->token_redefinir_senha_expires = null;
				$user->save();
				return Response::json(array('success'=>true, 'email'=>$user->email));
			} else {
				return Response::json(array('success'=>false,
					'errors'=>'messages.token_expirado',
					'error_type' => 'token_error',
					'message'=>'There were validation errors.'),300);
				}
			} else {
				return Response::json(array('success'=>false,
				'errors'=>'messages.token_invalido',
				'error_type' => 'token_error',
				'message'=>'There were validation errors.'),300);
		}
	}

	public function authProvider($provider, Request $request)
	{
		switch ($provider) {
			case 'facebook':
				$dadosProvider = array(
					'client_secret' => Config::get('app.facebook_secret'),
					'access_token_url' => 'https://graph.facebook.com/v2.5/oauth/access_token',
					'access_token_method' => 'GET',
					'profile_url' => 'https://graph.facebook.com/v2.5/me'
				);
			break;
			case 'google':
				$dadosProvider = array(
					'client_secret' => Config::get('app.google_secret'),
					'access_token_url' => 'https://accounts.google.com/o/oauth2/token',
					'access_token_method' => 'POST',
					'profile_url' => 'https://www.googleapis.com/oauth2/v3/userinfo/'
				);
			break;
			case 'live':
				$dadosProvider = array(
					'client_secret' => Config::get('app.live_secret'),
					'access_token_url' => 'https://login.microsoftonline.com/6170b526-4643-4a76-8365-572cde287ff0/oauth2/v2.0/token',
					'access_token_method' => 'POST',
					'profile_url' => 'https://graph.microsoft.com/oidc/userinfo'
				);
				break;
			default:
				$dadosProvider = array(
					'client_secret' => '',
					'access_token_url' => '',
					'access_token_method' => '',
					'profile_url' => ''
				);
				break;
		}

		$client = new GuzzleHttp\Client();

		$request->merge(array('client_secret'=> $dadosProvider['client_secret']));

		$params = [
			'code' => $request->input('code'),
			'client_id' => $request->input('client_id'),
			'redirect_uri' => $request->input('redirect_uri'),
			'client_secret' => $request->input('client_secret'),
			'grant_type' => 'authorization_code',
		];

		// Step 1. Exchange authorization code for access token.
		$accessTokenResponse = $client->request($dadosProvider['access_token_method'], $dadosProvider['access_token_url'], [
			'query' => $params,
			'form_params' => $params
		]);
		$accessToken = json_decode($accessTokenResponse->getBody(), true);

		// Step 2. Retrieve profile information about the current user.
		if($provider === 'facebook') {
			$fields = 'email,first_name,last_name,name,picture';
			$profileResponse = $client->request('GET', $dadosProvider['profile_url'], [
				'query' => [
					'access_token' => $accessToken['access_token'],
					'fields' => $fields
				]
			]);
		} else {
			$profileResponse = $client->request('GET', $dadosProvider['profile_url'], [
				'headers' => array('Authorization' => 'Bearer ' . $accessToken['access_token'])
			]);
		}
		$profile = json_decode($profileResponse->getBody(), true);

		if(isset($profile['email'])) {
            $email_verificar = explode('@', $profile['email']);
            $email_verificar = str_replace('.','', $email_verificar[0]).'@'.$email_verificar[1];
            $user = User::whereRaw("lower('$email_verificar') = lower(replace(split_part(email, '@', 1), '.', '') ||  '@' || split_part(email, '@', 2))")->first();
        } else {
            $user = null;
        }

		if ($user) {
			$server = Authorizer::getIssuer();
			$clientId = $request->input('client_id');
			$redirectUri = $request->input('redirect_uri');
			$client = $server->getClientStorage()->get(
				$clientId,
				null,
				$redirectUri,
				'authorization_code'
			);
			$params['client'] = $client;
			$scopeParam = $server->getRequest()->get('scope', '');
			$params['scopes'] = $server->getGrantType('authorization_code')->validateScopes($scopeParam, $client, $redirectUri);
			$params['state'] = null;
			$params['user_id'] = $user->id;
			$params['grant_type'] = 'authorization_code';
			$redirectUriNew = Authorizer::issueAuthCode('user', $params['user_id'], $params);
			$redirectUriNew = explode('?code=', $redirectUriNew);
			$request->merge(array('code'=>$redirectUriNew[1]));
	
			Authorizer::checkAuthCodeRequest();

			//TODO: Atualizar dados do usuário
			if($provider === 'facebook') {
				$profilePicture = $profile['picture']['data']['url'];
			} else {
				$profilePicture = $profile['picture'];
			}
			$this->atualizaDadosUsuario($user, $profilePicture, $profile['name']);

			return Response::json(Authorizer::issueAccessToken());
		} else {
			return Response::json(array('success'=>false,
			'error'=>'usuario_nao_cadastrado',
			'message'=>'messages.titulo_alerta_login'),300);
		}
	}

	private function atualizaDadosUsuario($user, $profilePicture, $profileName) {
		if(!isset($user->imagem_perfil) || ($user->imagem_perfil == 'perfil_padrao_homem.png')) {
			if(isset($profilePicture)) {
				try {
					$curl_handle=curl_init();
					curl_setopt($curl_handle, CURLOPT_URL, $profilePicture);
					curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
					curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curl_handle, CURLOPT_USERAGENT, 'player2.club');
					$arquivo = curl_exec($curl_handle);
					curl_close($curl_handle);

					if(stripos($arquivo, 'error'))  {
						$user->imagem_perfil = 'perfil_padrao_homem.png';
					} else {
						$fileName = 'usuario_'.str_replace('.', '', microtime(true)).'.jpg';
						file_put_contents( "uploads/usuarios/$fileName", $arquivo, FILE_APPEND );
						$user->imagem_perfil = $fileName;
					}
				} catch (ErrorException $e) {
					$user->imagem_perfil = 'perfil_padrao_homem.png';
				}
			}
		}
		if($user->nome === 'username' || $user->nome === $user->email) {
			$user->nome = $profileName;
		}
		// Recuperando IP do Usuário e Inserindo dados de Localização
        if(!isset($user->pais)) {
            $ip = \Request::getClientIp();
            $cliente = new Client(['base_uri' => 'http://ip-api.com/json/'.$ip]);
            $response = $cliente->request('GET');
            $objeto = json_decode($response->getBody(), true);
            if($objeto['status'] == 'success') {
                $user->localizacao = $objeto['city'];
                $user->uf = $objeto['region'];
                $user->pais = $objeto['countryCode'];
            }
        }

        $user->ultimo_login = date('Y-m-d H:i:s');;
        $user->save();
	}
}
