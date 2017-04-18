<?php

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Eloquent implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	protected $guarded = array();

	protected $fillable = array('nome','email','password', 'usuario_tipos_id', 'imagem_perfil', 'localizacao', 'descricao', 'sigla', 'imagem_capa');

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');

    /**
     * Atributo para definir as regras de validação para este objeto
     *
     */
    public static $rules = array('nome'=>'required',
                                 'email'=>'required|email|unique:users',
                                 'password'=>'required|min:6');

	public static $regrasLogin = array('email'=>'required|email',
								 'password'=>'required');

    public function usuarioTipo() {
    	return UsuarioTipo::find($this->usuario_tipos_id);
    }

	public function partidas($idCampeonato = null) {
		$usuarioPartidas = UsuarioPartida::where("users_id", "=", $this->id)->get(array("partidas_id"))->toArray();
		if(isset($idCampeonato)) {
			//TODO exibir apenas partidas de um determinado campeonato
			$fases = CampeonatoFase::where('campeonatos_id','=',$idCampeonato)->get(array('id'))->toArray();
			$grupos = FaseGrupo::whereIn('campeonato_fases_id', $fases)->get(array('id'))->toArray();
			$partidas = Partida::whereIn('fase_grupos_id',$grupos)->findMany($usuarioPartidas)->sortByDesc('id');
		} else {
			$partidas = Partida::findMany($usuarioPartidas)->sortByDesc('data_placar');
		}
		foreach($partidas as $partida) {
			if($partida->contestada()) {
				$partida->contestada = true;
			}
			$usuarios = $partida->usuarios();
			$partida->usuarios = $usuarios;
			// TODO incluir dados a serem utilizados do usuário para exibição das partidas
			if($partida->data_placar != null) {
				$partida->data_placar_limite = $partida->getDataLimitePlacar();
			}
		}

		$partidas = $partidas->values();
		return $partidas;
	}

	public function partidasEmAberto($idCampeonato = null) {
        $usuarioPartidas = UsuarioPartida::where("users_id", "=", $this->id)->get(array("partidas_id"))->toArray();
        if(isset($idCampeonato)) {
            //TODO exibir apenas partidas de um determinado campeonato
            $fases = CampeonatoFase::where('campeonatos_id','=',$idCampeonato)->get(array('id'))->toArray();
            $grupos = FaseGrupo::whereIn('campeonato_fases_id', $fases)->get(array('id'))->toArray();
            $partidas = Partida::whereIn('fase_grupos_id',$grupos)->whereNull('data_confirmacao')->findMany($usuarioPartidas)->sortByDesc('id');
        } else {
            $partidas = Partida::whereNull('data_confirmacao')->findMany($usuarioPartidas)->sortBy('id');
        }
        foreach($partidas as $partida) {
            if($partida->contestada()) {
                $partida->contestada = true;
            }
            $usuarios = $partida->usuarios();
            $partida->usuarios = $usuarios;
            if($partida->data_placar != null) {
                $partida->data_placar_limite = $partida->getDataLimitePlacar();
            }
            $partida->campeonato = $partida->campeonato()->descricao;
			$partida->fase = $partida->fase()->descricao;
        }
        $partidas = $partidas->values();
        return $partidas;
    }

    public function partidasDisputadas($idCampeonato = null) {
        $usuarioPartidas = UsuarioPartida::where("users_id", "=", $this->id)->get(array("partidas_id"))->toArray();
        if(isset($idCampeonato)) {
            //TODO exibir apenas partidas de um determinado campeonato
            $fases = CampeonatoFase::where('campeonatos_id','=',$idCampeonato)->get(array('id'))->toArray();
            $grupos = FaseGrupo::whereIn('campeonato_fases_id', $fases)->get(array('id'))->toArray();
            $partidas = Partida::whereIn('fase_grupos_id',$grupos)->whereNotNull('data_placar')->findMany($usuarioPartidas)->sortByDesc('data_placar');
        } else {
            $partidas = Partida::whereNotNull('data_placar')->findMany($usuarioPartidas)->sortBy('id');
        }
        foreach($partidas as $partida) {
            if($partida->contestada()) {
                $partida->contestada = true;
            }
            $usuarios = $partida->usuarios();
            $partida->usuarios = $usuarios;
            if($partida->data_placar != null) {
                $partida->data_placar_limite = $partida->getDataLimitePlacar();
            }
            $partida->campeonato = $partida->campeonato()->descricao;
            $partida->fase = $partida->fase()->descricao;
        }
        $partidas = $partidas->values();
        return $partidas;
    }

	public function partidasNaoDisputadas($idCampeonato = null) {
		$usuarioPartidas = UsuarioPartida::where("users_id", "=", $this->id)->get(array("partidas_id"))->toArray();
		if(isset($idCampeonato)) {
			//TODO exibir apenas partidas de um determinado campeonato
			$fases = CampeonatoFase::where('campeonatos_id','=',$idCampeonato)->get(array('id'))->toArray();
			$grupos = FaseGrupo::whereIn('campeonato_fases_id', $fases)->get(array('id'))->toArray();
			$partidas = Partida::whereIn('fase_grupos_id',$grupos)->whereNull('data_placar')->findMany($usuarioPartidas)->sortBy('id');
		} else {
			$partidas = Partida::whereNull('data_placar')->findMany($usuarioPartidas)->sortBy('id');
		}
		foreach($partidas as $partida) {
			if($partida->contestada()) {
				$partida->contestada = true;
			}
			$usuarios = $partida->usuarios();
			$partida->usuarios = $usuarios;
			if($partida->data_placar != null) {
				$partida->data_placar_limite = $partida->getDataLimitePlacar();
			}
			$partida->campeonato = $partida->campeonato()->descricao;
			$partida->fase = $partida->fase()->descricao;
		}
		$partidas = $partidas->values();
		return $partidas;
	}

	public function campeonatos() {
		// Exibir campeonatos do usuário
		// Cada campeonato, possuirá uma coleção das partidas do usuário
		// Fases?
	}

	public function seguidores() {
		return $this->belongsToMany('User', 'seguidor', 'users_id_mestre', 'users_id_seguidor')->withTimestamps();
	}

	public function seguindo() {
		return $this->belongsToMany('User', 'seguidor', 'users_id_seguidor', 'users_id_mestre')->withTimestamps();
	}

	public function seguir($idUsuario) {
	    $this->seguindo()->attach($idUsuario);

		$seguidor_id = $this->seguindo()->withPivot('id')->first()->pivot->id;

		$atividade = new Atividade();
		$atividade->users_id = $this->id;
		$atividade->seguidor_id = $seguidor_id;
		$atividade->save();
    }
    public function deixarDeSeguir($idUsuario) {
        $this->seguindo()->detach($idUsuario);
    }

	public function segue($idUsuario) {
		$segue = $this->seguindo()->wherePivot('users_id_mestre', '=', $idUsuario)->get();
		if($segue->count() > 0) {
			return true;
		}
		return false;
	}

	public function segueJogo($idJogo) {
		$segue = $this->jogos()->wherePivot('jogos_id', '=', $idJogo)->get();
		if($segue->count() > 0) {
			return true;
		}
		return false;
	}

	public function jogos() {
        return $this->belongsToMany('Jogo', 'seguidor_jogo', 'users_id', 'jogos_id')->withTimestamps();
    }

    public function seguirJogo($idJogo) {
        $this->jogos()->attach($idJogo);

		$seguidor_jogo_id = $this->jogos()->withPivot(['id', 'created_at'])->orderBy('pivot_created_at', 'desc')->first()->pivot->id;

		$atividade = new Atividade();
		$atividade->users_id = $this->id;
		$atividade->seguidor_jogo_id = $seguidor_jogo_id;
		$atividade->save();
    }

    public function deixarDeSeguirJogo($idJogo) {
        $this->jogos()->detach($idJogo);
    }

	public function getAtividades($todos, $offset, $quantidade) {
	    if(boolval($todos)) {
			$idSeguidores = $this->seguindo()->getRelatedIds();
			$idSeguidores->push($this->id);
            $postsDestinatarios = Post::where('destinatario_id','=', $this->id)->get(array('id'));
			//TODO Quando existir atividades de seguidores e comentários, remover essas condições whereNull: ->whereNull('seguidor_id')->whereNull('comentario_id')->whereNull('seguidor_jogo_id')
            $atividades = Atividade::whereIn('users_id', $idSeguidores)->whereNull('seguidor_id')->whereNull('comentario_id')->whereNull('seguidor_jogo_id')->orWhereIn('post_id', $postsDestinatarios)->take($quantidade)->skip($offset)->orderBy('created_at', 'desc')->get();
        } else {
			$postsDestinatarios = Post::where('destinatario_id','=', $this->id)->get(array('id'));
			//TODO Quando existir atividades de seguidores e comentários, remover essas condições whereNull: ->whereNull('seguidor_id')->whereNull('comentario_id')->whereNull('seguidor_jogo_id')

            $atividades = Atividade::where('users_id','=', $this->id)->whereNull('seguidor_id')->whereNull('comentario_id')->whereNull('seguidor_jogo_id')->orWhereIn('post_id', $postsDestinatarios)->take($quantidade)->skip($offset)->orderBy('created_at', 'desc')->get();
        }
		foreach ($atividades as $atividade) {
			$atividade->curtidas = $atividade->curtidas()->get();
			$atividade->comentarios = $atividade->comentarios($this->id);
			$atividade->curtiu = $atividade->curtiu($this->id);
		}
		return $atividades;
	}

	public function getNotificacoes($lidas = false) {
	    if($lidas) {
            $notificacoes = Notificacao::where('id_destinatario', '=', $this->id)->orderBy('created_at', 'desc')->get();
        } else {
            $notificacoes = Notificacao::where('id_destinatario', '=', $this->id)->where('lida','=','false')->orderBy('created_at', 'desc')->get();
        }
        return $notificacoes;
    }

	public function emailsNotificacao() {
		return $this->belongsToMany('NotificacaoEvento', 'notificacao_email', 'users_id', 'evento_notificacao_id')->withTimestamps();
	}

	public function adicionaNotificacaoPorEmail($idEvento) {
		$this->emailsNotificacao()->attach($idEvento);
	}

	public function removeNotificacaoPorEmail($idEvento) {
		$this->emailsNotificacao()->detach($idEvento);
	}

	public function getConversas() {
		$conversas  = DB::table('mensagem as m')
							->selectRaw('id_remetente, '.
										'(SELECT count(lida) FROM mensagem where id_remetente = m.id_remetente AND lida is false) as nao_lidas, '.
										'(SELECT mensagem FROM mensagem where id_remetente = m.id_remetente ORDER BY created_at DESC LIMIT 1) as ultima_mensagem, '.
										'(SELECT created_at FROM mensagem where id_remetente = m.id_remetente ORDER BY created_at DESC LIMIT 1)')
							->where('id_destinatario','=',$this->id)->groupBy('id_remetente')->orderBy('created_at', 'desc')->get();
		return $conversas;
	}

	public function getMensagens($idRemetente) {
		$mensagens = Mensagem::where('id_destinatario','=',$this->id)->where('id_remetente','=',$idRemetente)->orderBy('created_at', 'desc')->get();
		return $mensagens;
	}

}
