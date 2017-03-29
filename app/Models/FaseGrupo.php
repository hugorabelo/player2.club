<?php

use Illuminate\Database\Eloquent\Collection;

class FaseGrupo extends Eloquent
{
    protected $guarded = array();

    public static $rules = array(
        'descricao' => 'required',
        'quantidade_usuarios' => 'required',
        'campeonato_fases_id' => 'required'
    );

    public function fase()
    {
        return CampeonatoFase::find($this->campeonato_fases_id);
    }

    /**
     * Retorna os usuários do grupo ordenados pelos critérios de classificação do campeonato
     *
     * @return Collection
     */
    public function usuarios()
    {
        $usuarios = $this->belongsToMany('User', 'usuario_grupos', 'fase_grupos_id', 'users_id')->getResults();
        foreach ($usuarios as $usuario) {
            if(($usuario->sigla == '') || ($usuario->sigla == null)) {
                $usuario->sigla = substr($usuario->nome, 0, 3);
            }
            $usuario->distintivo = (isset($usuario->distintivo) && !empty($usuario->distintivo)) ? $usuario->distintivo : $usuario->imagem_perfil;
        }
        return $usuarios;
    }

    public function usuariosMataMata() {
        $usuarios = $this->usuarios();
        if($usuarios->isEmpty()) {
            return null;
        }
        $partidas = $this->partidas();
        $usuario1 = $usuarios->first();
        $usuario2 = $usuarios->last();
        $usuario1->distintivo = (isset($usuario1->distintivo) && !empty($usuario1->distintivo)) ? $usuario1->distintivo : $usuario1->imagem_perfil;
        $usuario2->distintivo = (isset($usuario2->distintivo) && !empty($usuario2->distintivo)) ? $usuario2->distintivo : $usuario2->imagem_perfil;
        $usuario1->placares = app()->make(Collection::class);
        $usuario2->placares = app()->make(Collection::class);
        foreach ($partidas as $partida) {
            $placarUsuario1 = $partida->placarUsuario($usuario1->id);
            $usuario1->placares->add($placarUsuario1);
            $placarExtraUsuario1 = $partida->placarExtraUsuario($usuario1->id);
            if(isset($placarExtraUsuario1)) {
                $usuario1->placarExtra = $placarExtraUsuario1;
            }

            $placarUsuario2 = $partida->placarUsuario($usuario2->id);
            $usuario2->placares->add($placarUsuario2);
            $placarExtraUsuario2 = $partida->placarExtraUsuario($usuario2->id);
            if(isset($placarExtraUsuario2)) {
                $usuario2->placarExtra = $placarExtraUsuario2;
            }
        }
        $usuarios = app()->make(Collection::class);
        $usuarios->add($usuario1);
        $usuarios->add($usuario2);

        return $usuarios;
    }

    public function usuariosComClassificacao()
    {
        $usuarios = $this->usuarios();
        if($usuarios->isEmpty()) {
            return true;
        }
        $partidas = $this->partidas();
        /*
         * Para cada partida, trazer os usuários da partida;
         * Para cada usuário, associar com um usuário da coleção principal;
         * Verificar a pontuação do usuário.
         * Criar forma de pegar os dados (V, E, D, GP, GC)
         */

        $quantidade_jogadores_por_partida = 0;
        if ($partidas->first() != null) {
            $quantidade_jogadores_por_partida = $partidas->first()->usuarios()->count();
        }

        if ($quantidade_jogadores_por_partida == 2) {
            // Partida com Dois Jogadores
            foreach ($partidas as $partida) {
                $u1 = $partida->usuarios()->first();
                $u2 = $partida->usuarios()->last();
                $usuario1 = $usuarios->find($u1->users_id);
                $usuario2 = $usuarios->find($u2->users_id);
                $placar1 = $u1->placar;
                $placar2 = $u2->placar;
                if (isset($placar1)) {
                    $usuario1->gols_pro += $placar1;
                    $usuario1->gols_contra += $placar2;
                    $usuario1->pontuacao += $u1->pontuacao;

                    $usuario2->gols_pro += $placar2;
                    $usuario2->gols_contra += $placar1;
                    $usuario2->pontuacao += $u2->pontuacao;

                    if ($placar1 > $placar2) {
                        $usuario1->vitorias += 1;
                        $usuario2->derrotas += 1;
                    } else if ($placar2 > $placar1) {
                        $usuario1->derrotas += 1;
                        $usuario2->vitorias += 1;
                    } else if ($placar1 == $placar2) {
                        $usuario1->empates += 1;
                        $usuario2->empates += 1;
                    }
                }
            }

            foreach ($usuarios as $usuario) {
                $num_vitorias = intval($usuario->vitorias);
                $num_empates = intval($usuario->empates);
                $num_derrotas = intval($usuario->derrotas);
                $num_gols_pro = intval($usuario->gols_pro);
                $num_gols_contra = intval($usuario->gols_contra);

                $num_jogos = $num_vitorias + $num_empates + $num_derrotas;
                $num_saldo_gols = $num_gols_pro - $num_gols_contra;

                $usuario->pontuacao = intval($usuario->pontuacao);
                $usuario->jogos = $num_jogos;
                $usuario->vitorias = $num_vitorias;
                $usuario->empates = $num_empates;
                $usuario->derrotas = $num_derrotas;
                $usuario->gols_pro = $num_gols_pro;
                $usuario->gols_contra = $num_gols_contra;
                $usuario->saldo_gols = $num_saldo_gols;
            }
        } else {
            // Partida com mais de 2 jogadores
            foreach ($partidas as $partida) {
                foreach ($partida->usuarios() as $usuarioPartida) {
                    if (isset($usuarioPartida->posicao)) {
                        $usuarios->find($usuarioPartida->users_id)->pontuacao += intval($usuarioPartida->pontuacao);
                    }
                }
            }
        }

        $fase = $this->fase();
        $campeonato = $fase->campeonato();
        return $campeonato->ordenarUsuariosPorCriterioDeClassificacao($usuarios);
    }

    public function partidas()
    {
        $partidas = $this->hasMany('Partida', 'fase_grupos_id')->getResults();
        return $partidas;
    }

    public function rodadas()
    {
        $partidas = $this->partidas();
        $partidas = $partidas->sortBy('rodada');
        $partidas->values()->all();
        $rodadas = new Collection();
        foreach ($partidas as $partida) {
            $rodadaAtual = $partida->rodada;
            if ($rodadas->get($rodadaAtual) == null) {
                $rodadas->put($rodadaAtual, $rodadaAtual);
            }
        }
        return $rodadas;
    }

    public function partidasPorRodada($rodada)
    {
        $partidas = $this->partidas();
        $partidas = $partidas->sortBy('rodada');
        $partidas->values()->all();
        $partidasPorRodada = new Collection();
        foreach ($partidas as $partida) {
            if ($partida->rodada == $rodada) {
                $partida->usuarios = $partida->usuarios();
                $partidasPorRodada->add($partida);
            }
        }
        return $partidasPorRodada;
    }

    public function usuariosClassificados()
    {
        $fase = $this->fase();
        $campeonato = $fase->campeonato();
        $detalhesDoCampeonato = $campeonato->detalhes();

        $usuarios = $this->usuarios();

        $partidas = $this->partidas();

        if ($usuarios->first() == null || $partidas->first() == null) {
            return array();
        }

        $usuariosClassificados = new Collection();
        if ($fase->matamata) {
            if ($detalhesDoCampeonato->ida_volta) {
                $u1A = $partidas->first()->usuarios()->first();
                $u2A = $partidas->first()->usuarios()->last();
                $u1B = $partidas->last()->usuarios()->last();
                $u2B = $partidas->last()->usuarios()->first();
                $placar1 = $u1A->placar + $u1B->placar;
                $placar2 = $u2A->placar + $u2B->placar;
                if($placar1 > $placar2) {
                    $usuariosClassificados->put(1, User::find($u1A->users_id));
                } else if($placar1 < $placar2) {
                    $usuariosClassificados->put(1, User::find($u2A->users_id));
                } else {
                    if ($detalhesDoCampeonato->fora_casa && ($u1A->placar != $u2B->placar)) {
                        if($u1A->placar > $u2B->placar) {
                            $usuariosClassificados->put(1, User::find($u2A->users_id));
                        } else {
                            $usuariosClassificados->put(1, User::find($u1A->users_id));
                        }
                    } else {
                        if($u1B->placar_extra > $u2B->placar_extra) {
                            $usuariosClassificados->put(1, User::find($u1A->users_id));
                        } else {
                            $usuariosClassificados->put(1, User::find($u2A->users_id));
                        }
                    }
                }
            } else {
                $u1 = $partidas->first()->usuarios()->first();
                $u2 = $partidas->first()->usuarios()->last();
                if ($u1->placar > $u2->placar) {
                    $usuariosClassificados->put(1, User::find($u1->users_id));
                } else {
                    $usuariosClassificados->put(1, User::find($u2->users_id));
                }
            }
        } else {
            $proximaFase = $fase->proximaFase();
            $quantidadeClassificados = $proximaFase->quantidade_usuarios / $proximaFase->grupos()->count();
            $usuariosComClassificacao = $this->usuariosComClassificacao();
            $usuariosClassificados = $usuariosComClassificacao->take($quantidadeClassificados);
        }
        return $usuariosClassificados;
    }

}
