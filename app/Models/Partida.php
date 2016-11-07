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

    public function confirmarPlacar($id_usuario) {
        // Computar Pontuação e posição do usuario_partidas
        $this->usuario_confirmacao = $id_usuario;
        $this->data_confirmacao = date('Y-m-d H:i:s');
        $this->save();
    }

    public function confirmarPlacarAutomaticamente() {
        if($this->contestada()) {
            return null;
        }
        $agora = new DateTime();
        if(isset($this->data_placar)) {
            $placar = new DateTime($this->data_placar);
            $diferença = $agora->diff($placar);
            if($diferença->d >= 1) {
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
        $usuarios = $this->hasMany('UsuarioPartida', 'partidas_id')->getResults()->sortBy('id');
        $usuarios->values()->all();
        if($informacoes) {
            foreach($usuarios as $usuario) {
                $usuarioBD = User::find($usuario->users_id);
                $usuario->nome = $usuarioBD->nome;
                $usuario->sigla = $usuarioBD->sigla;
                $usuario->distintivo = isset($usuarioBD->distintivo) ? $usuarioBD->distintivo : $usuarioBD->imagem_perfil;
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
        $contestacao = ContestacaoResultado::where('partidas_id','=',$this->id)->get();
        return !$contestacao->isEmpty();
    }

    public function fase() {
        return $this->grupo()->fase();
    }

    public function campeonato() {
        return $this->fase()->campeonato();
    }

    public function placarUsuario($idUsuario) {
        foreach ($this->usuarios(false) as $usuario) {
            if($usuario->users_id == $idUsuario) {
                return $usuario->placar;
            }
        }
        return null;
    }

    public function placarExtraUsuario($idUsuario) {
        foreach ($this->usuarios(false) as $usuario) {
            if($usuario->users_id == $idUsuario) {
                return $usuario->placar_extra;
            }
        }
        return null;
    }
}
