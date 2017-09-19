<?php

use Illuminate\Database\Eloquent\Collection;

class Partida extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'fase_grupos_id' => 'required',
		'rodada' => 'required'
	);

    public function grupo() {
        return FaseGrupo::find($this->fase_grupos_id);
    }

    /*
     * return
     * 1 - tudo ok
     * 2 - placar inválido
     * 3 - empate não é permitido na fase
     * 4 - pontuação não cadastrada
     */
    public function salvarPlacar($partida) {
        $permite_empate = $this->grupo()->fase()->permite_empate;
        $pontuacoes = FaseGrupo::find($partida['fase_grupos_id'])->fase()->pontuacoes();
        $usuarios = Collection::make($partida['usuarios']);
        $usuarios = $usuarios->sortByDesc('placar');
        $empate_computado = false;

        // Verificar se todos os usuários estão com o placar inserido
        foreach ($usuarios as $usuario) {
            if($usuario['placar'] == null) {
                return 2;
            }
        }

        // Verificar se a pontuação está toda cadastrada corretamente
        for($i = $permite_empate ? 0 : 1;$i<$usuarios->count();$i++) {
            if(!isset($pontuacoes[$i])) {
                return 4;
            }
        }

        if($usuarios->count() == 2) {
            if($usuarios->first()['placar'] == $usuarios->last()['placar']) {
                if($permite_empate) {
                    foreach ($usuarios as $usuario) {
                        $usuarioPartida = UsuarioPartida::find($usuario['id']);
                        $usuarioPartida->posicao = 0;
                        $usuarioPartida->pontuacao = $pontuacoes[0];
                        $usuarioPartida->placar = $usuario['placar'];
                        $usuarioPartida->save();
                    }
                    $empate_computado = true;
                } else {
                    return 3;
                }
            }
        }
        if(!$empate_computado) {
            $i = 1;
            foreach ($usuarios as $usuario) {
                $usuarioPartida = UsuarioPartida::find($usuario['id']);
                $usuarioPartida->posicao = $i;
                $usuarioPartida->pontuacao = $pontuacoes[$i];
                $usuarioPartida->placar = $usuario['placar'];
                $usuarioPartida->save();
                $i++;
            }
        }
        $this->usuario_placar = $partida['usuarioLogado'];
        $this->data_placar = date('Y-m-d H:i:s');
        $this->save();

        return 1;
    }

    public function confirmarPlacar($id_usuario, $placarContestado = false) {
        // Computar Pontuação e posição do usuario_partidas
        $this->usuario_confirmacao = $id_usuario;
        $this->data_confirmacao = date('Y-m-d H:i:s');
        $this->save();

        if($placarContestado) {
            $contestacao = ContestacaoResultado::where('partidas_id','=',$this->id)->first();
            if(isset($contestacao)) {
                $contestacao->resolvida = true;
                $contestacao->save();
            }
        }
    }

    public function confirmarPlacarAutomaticamente() {
        if($this->contestada()) {
            return null;
        }
        $agora = new DateTime();
        if(isset($this->data_placar)) {
            $placar = new DateTime($this->data_placar);
            $diferença = $agora->diff($placar);
            if($diferença->d >= 2) {
                $this->confirmarPlacar(null);
            }
        }
    }

    public function cancelarPlacar($id_usuario) {
        $usuarios = $this->usuarios(false);
        foreach ($usuarios as $usuarioPartida) {
            $usuarioPartida->posicao = null;
            $usuarioPartida->pontuacao = null;
            $usuarioPartida->placar = null;
            $usuarioPartida->placar_extra = null;
            $usuarioPartida->save();
        }

        $this->usuario_placar = null;
        $this->data_placar = null;
        $this->save();
    }

    public function placarSalvo() {
        return isset($this->data_placar);
    }

    public function placarConfirmado() {
        return isset($this->data_confirmacao);
    }

    public function usuarios($informacoes = true) {
        $tipo_competidor = $this->campeonato()->tipo_competidor;
        $usuarios = $this->hasMany('UsuarioPartida', 'partidas_id')->orderBy('id')->getResults();
        $usuarios->values()->all();
        if($informacoes) {
            foreach($usuarios as $usuario) {
                if($tipo_competidor == 'equipe') {
                    $usuarioBD = Equipe::find($usuario->equipe_id);
                    $usuarioCampeonato = CampeonatoUsuario::where('equipe_id','=',$usuario->equipe_id)->where('campeonatos_id','=',$this->campeonato()->id)->first();
                } else {
                    $usuarioBD = User::find($usuario->users_id);
                    $usuarioCampeonato = CampeonatoUsuario::where('users_id','=',$usuario->users_id)->where('campeonatos_id','=',$this->campeonato()->id)->first();
                }
                $nome_completo = $usuarioBD->nome;
                $nome_completo = explode(' ', $nome_completo);
                $nome_completo = count($nome_completo) > 2 ? array_shift($nome_completo).' '.array_pop($nome_completo) : $usuarioBD->nome;
                $usuario->nome = $nome_completo;
                $usuario->sigla = $usuarioBD->sigla != '' ? $usuarioBD->sigla : substr($usuario->nome, 0, 3);
                $time = null;
                if(isset($usuarioCampeonato->time_id) && !empty($usuarioCampeonato->time_id)) {
                    $time = Time::find($usuarioCampeonato->time_id);
                }
                $usuario->distintivo = (isset($time)) ? $time->distintivo : $usuarioBD->imagem_perfil;
            }
        }
        return $usuarios;
    }

    public function getDataLimitePlacar() {
        $dataPlacar = null;
        if(isset($this->data_placar)) {
            $dataPlacar = new DateTime($this->data_placar);
            $dataPlacar->add(new DateInterval('P1D'));
        }
        return $dataPlacar;
    }

    public function contestada() {
        $contestacao = ContestacaoResultado::where('partidas_id','=',$this->id)->first();
        if(isset($contestacao)) {
            return !$contestacao->resolvida;
        }
        return false;
    }

    public function fase() {
        return $this->grupo()->fase();
    }

    public function campeonato() {
        return $this->fase()->campeonato();
    }

    public function placarUsuario($idUsuario) {
        $tipo_competidor = $this->campeonato()->tipo_competidor;
        foreach ($this->usuarios(false) as $usuario) {
            if($tipo_competidor == 'equipe') {
                $idUsuarioVerificar = $usuario->equipe_id;
            } else {
                $idUsuarioVerificar = $usuario->users_id;
            }
            if($idUsuarioVerificar == $idUsuario) {
                return $usuario->placar;
            }
        }
        return null;
    }

    public function placarExtraUsuario($idUsuario) {
        $tipo_competidor = $this->campeonato()->tipo_competidor;
        foreach ($this->usuarios(false) as $usuario) {
            if($tipo_competidor == 'equipe') {
                $idUsuarioVerificar = $usuario->equipe_id;
            } else {
                $idUsuarioVerificar = $usuario->users_id;
            }
            if($idUsuarioVerificar == $idUsuario) {
                return $usuario->placar_extra;
            }
        }
        return null;
    }
}
