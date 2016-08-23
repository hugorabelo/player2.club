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
        $usuarios = $this->belongsToMany('User', 'usuario_grupos', 'fase_grupos_id', 'users_id')->withPivot(array('pontuacao'))->orderBy('pontuacao', 'desc')->getResults();
        return $usuarios;
    }

    public function usuariosComClassificacao_NEW() {
        $fase = $this->fase();
        $campeonato = $fase->campeonato();
        $detalhesDoCampeonato = $campeonato->detalhes();

        $usuarios = $this->usuarios()->all();

        $usuariosOrdenados = $this->ordenaUsuariosCriteriosClassificacao($usuarios, $fase);

        $partidas = $this->partidas();

        if ($usuarios->first() == null || $partidas->first() == null) {
            return array();
        }

        return $usuariosOrdenados;
    }

    /** TODO */
    public function usuariosComClassificacao()
    {
        $usuarios = $this->belongsToMany('User', 'usuario_grupos', 'fase_grupos_id', 'users_id')->getResults();
        $partidas = $this->partidas();
        $pontuacoes = $this->fase()->pontuacoes();
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

        //TODO ORDENAR USUARIOS DE ACORDO COM AS REGRAS;
        $fase = $this->fase();
        $campeonato = $fase->campeonato();
        $criteriosDeClassificacao = $campeonato->criteriosOrdenados();
//        foreach ($criteriosDeClassificacao as $criterio) {
//            if($criterio->ordenacao == 'maior') {
//                $usuarios->sortByDesc($criterio->valor);
//            } else {
//                $usuarios->sortBy($criterio->valor);
//            }
//        }
//        $usuarios->sortByDesc('pontuacao');
        $usuarios->sortBy(function($usuario) {
            return sprintf('%-12s%s', $usuario->pontuacao, $usuario->vitorias, $usuario->saldo_gols);
        });
        $usuarios->values()->all();
        return $usuarios;
    }

    public function teste($post) {
        return sprintf('%-12s%s', $post->pontuacao, $post->vitorias);
    }

    public function partidas()
    {
        $partidas = $this->hasMany('Partida', 'fase_grupos_id')->getResults();
        return $partidas;
    }

    public function rodadas()
    {
        $partidas = $this->partidas();
        $partidas->sortBy('rodada');
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
        $partidas->sortBy('rodada');
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

        $usuarios = $this->usuarios()->all();

        $usuariosOrdenados = $this->ordenaUsuariosCriteriosClassificacao($usuarios, $fase);

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
            $quantidadeClassificadosMataMata = $detalhesDoCampeonato->classificados_proxima_fase;
            $quantidadeClassificados = $quantidadeClassificadosMataMata / $fase->grupos()->count();
            $quantidadeUsuariosInseridos = 0;
            foreach ($usuarios as $usuarioInserido) {
                $quantidadeUsuariosInseridos++;
                $usuariosClassificados->put($quantidadeUsuariosInseridos, $usuarioInserido);
                if ($quantidadeUsuariosInseridos == $quantidadeClassificados) {
                    break;
                }
            }
        }
        return $usuariosClassificados;
    }

    private function ordenaUsuariosCriteriosClassificacao($listaUsuarios, $fase) {
        $campeonato = Campeonato::find($fase->campeonatos_id);
        $this->criteriosDeClassificacao = $campeonato->criteriosOrdenados();
        $listaUsuarios->sort("comparaUsuariosCriteriosClassificacao");
        return $listaUsuarios;
    }

    private function comparaUsuariosCriteriosClassificacao($usuario1, $usuario2) {
        /*
         *
            $collection->sort(function($time1, $time2) {
               if($time1->pontos === $time2->pontos) {
                 if($time1->vitoria === $time2->vitoria) {
                   return 0;
                 }
                 return $time1->vitoria > $time2->vitoria ? -1 : 1;
               }
               return $time1->pontos > $time2->pontos ? -1 : 1;
            });
         */
        $criteriosClassificacao = $this->criteriosDeClassificacao;
        $criterio = $criteriosClassificacao->shift();
        $valor = $criterio->valor;
        $ordenacao = $criterio->ordenacao;
        if($usuario1->{$valor} === $usuario2->{$valor}) {
            if($criteriosClassificacao->count() == 0) {
                return 0;
            }
            return $this->comparaUsuariosCriteriosClassificacao($usuario1, $usuario2, $criteriosClassificacao);
        }
        if($ordenacao == 'maior') {
            return $usuario1->{$valor} > $usuario2->{$valor} ? -1 : 1;
        } else {
            return $usuario1->{$valor} < $usuario2->{$valor} ? -1 : 1;
        }
    }

}
