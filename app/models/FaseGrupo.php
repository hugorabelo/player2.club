<?php

class FaseGrupo extends Eloquent {
    protected $guarded = array();

    public static $rules = array(
        'descricao' => 'required',
        'quantidade_usuarios' => 'required',
        'campeonato_fases_id' => 'required'
    );

    public function usuarios() {
        return $this->belongsToMany('User', 'usuario_grupos', 'fase_grupos_id', 'users_id')->withPivot(array('pontuacao'))->orderBy('pontuacao', 'desc')->getResults();
    }

    public function usuariosComClassificacao() {
        $usuarios = $this->belongsToMany('User', 'usuario_grupos', 'fase_grupos_id', 'users_id')->getResults();
        $partidas = $this->partidas();
        foreach($partidas as $partida) {
            foreach($usuarios as $usuario) {
                //pegar a classificacao dos usuarios
                // Pontos, Jogos, Vitórias, empates, derrotas, gols pro, gols contra, saldo de gols

                $num_vitorias = 3;
                $num_empates = 3;
                $num_derrotas = 2;
                $num_gols_pro = 20;
                $num_gols_contra = 8;

                foreach($partida->usuarios as $usuarioPartida) {
                    if($usuarioPartida->id == $usuario->id) {

                    }
                }

                $num_jogos = $num_vitorias + $num_empates + $num_derrotas;
                $pontuacao = ($num_vitorias * 3) + $num_empates;
                $num_saldo_gols = $num_gols_pro - $num_gols_contra;

                $usuario->pontuacao = $pontuacao;
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
