<?php

class FaseGrupo extends Eloquent {
    protected $guarded = array();

    public static $rules = array(
        'descricao' => 'required',
        'quantidade_usuarios' => 'required',
        'campeonato_fases_id' => 'required'
    );

    public function fase() {
        return CampeonatoFase::find($this->campeonato_fases_id);
    }

    public function usuarios() {
        return $this->belongsToMany('User', 'usuario_grupos', 'fase_grupos_id', 'users_id')->withPivot(array('pontuacao'))->orderBy('pontuacao', 'desc')->getResults();
    }

    public function usuariosComClassificacao() {
        $usuarios = $this->belongsToMany('User', 'usuario_grupos', 'fase_grupos_id', 'users_id')->getResults();
        $partidas = $this->partidas();
        $pontuacoes = $this->fase()->pontuacoes();
        Log::info($pontuacoes);
        Log::info($partidas);

        /*
         * Para cada partida, trazer os usuários da partida;
         * Para cada usuário, associar com um usuário da coleção principal;
         * Verificar a pontuação do usuário.
         */


        foreach($partidas as $partida) {
            foreach($usuarios as $usuario) {
                //pegar a classificacao dos usuarios
                // Pontos, Jogos, Vitórias, empates, derrotas, gols pro, gols contra, saldo de gols

                $num_vitorias = 3;
                $num_empates = 3;
                $num_derrotas = 2;
                $num_gols_pro = 20;
                $num_gols_contra = 8;

                $pontuacao = 0;

                foreach($partida->usuarios as $usuarioPartida) {
                    if($usuarioPartida->users_id == $usuario->id) {
                        if($usuarioPartida->posicao != null) {
                            $pontuacao = $pontuacoes[$usuarioPartida->posicao];
                            if($usuario->id == 1) {
                                Log::info($usuarioPartida);
                            }
                        }
                        break;
                    }
                }

                $num_jogos = $num_vitorias + $num_empates + $num_derrotas;
                //$pontuacao = ($num_vitorias * 3) + $num_empates;
                $num_saldo_gols = $num_gols_pro - $num_gols_contra;

                $usuario->pontuacao += $pontuacao;
                $usuario->jogos = $num_jogos;
                $usuario->vitorias = $num_vitorias;
                $usuario->empates = $num_empates;
                $usuario->derrotas = $num_derrotas;
                $usuario->gols_pro = $num_gols_pro;
                $usuario->gols_contra = $num_gols_contra;
                $usuario->saldo_gols = $num_saldo_gols;
            }
        }
        //TODO ORDENAR USUARIOS;
        return $usuarios;
    }

    public function partidas() {
        $partidas = $this->hasMany('Partida', 'fase_grupos_id')->getResults();
        foreach($partidas as $partida) {
            $usuarios = $partida->hasMany('UsuarioPartida', 'partidas_id')->getResults()->sortBy('posicao');
            $partida->usuarios = $usuarios;
        }
        return $partidas;
    }

}
