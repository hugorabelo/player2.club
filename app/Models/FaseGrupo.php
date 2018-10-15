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
        $tipo_competidor = $this->fase()->campeonato()->tipo_competidor;
        if($tipo_competidor == 'equipe') {
            $usuarios = $this->belongsToMany('Equipe', 'usuario_grupos', 'fase_grupos_id', 'equipe_id')->withPivot(array('id'))->getResults();
        } else {
            $usuariosCadastrados = $this->belongsToMany('User', 'usuario_grupos', 'fase_grupos_id', 'users_id')->withPivot(array('id'))->getResults();
            $usuariosAnonimos = $this->usuariosAnonimos();
            $usuarios = $usuariosCadastrados;
            foreach ($usuariosAnonimos as $anonimo) {
                $anonimo->anonimo = true;
                $usuarios->push($anonimo);
            }
        }
        foreach ($usuarios as $usuario) {
            if($tipo_competidor == 'equipe') {
                $usuarioCampeonato = CampeonatoUsuario::where('equipe_id','=',$usuario->id)->where('campeonatos_id','=',$this->fase()->campeonatos_id)->first();
            } else {
                if($usuario->anonimo) {
                    $usuarioCampeonato = CampeonatoUsuario::where('anonimo_id','=',$usuario->id)->where('campeonatos_id','=',$this->fase()->campeonatos_id)->first();
                } else {
                    $usuarioCampeonato = CampeonatoUsuario::where('users_id','=',$usuario->id)->where('campeonatos_id','=',$this->fase()->campeonatos_id)->first();
                }
            }
            if(($usuario->sigla == '') || ($usuario->sigla == null)) {
                $usuario->sigla = substr($usuario->nome, 0, 3);
            }
            $nome_completo = $usuario->nome;
            $nome_completo = explode(' ', $nome_completo);
            $nome_completo = count($nome_completo) > 2 ? array_shift($nome_completo).' '.array_pop($nome_completo) : $usuario->nome;
            $usuario->nome = $nome_completo;
            $time = null;
            if(isset($usuarioCampeonato->time_id) && !empty($usuarioCampeonato->time_id)) {
                $time = Time::find($usuarioCampeonato->time_id);
            }
            $usuario->distintivo = (isset($time)) ? $time->distintivo : $usuario->imagem_perfil;
        }
        return $usuarios;
    }

    public function usuariosAnonimos() {
        return $this->belongsToMany('UserAnonimo', 'usuario_grupos', 'fase_grupos_id', 'anonimo_id')->withPivot(array('id'))->getResults();
    }

    public function usuariosMataMata() {
        $usuarios = $this->usuarios();
        if($usuarios->isEmpty()) {
            return null;
        }
        $partidas = $this->partidas();
        $usuario1 = $usuarios->first();
        $usuario2 = $usuarios->last();

        $nome_completo = $usuario1->nome;
        $nome_completo = explode(' ', $nome_completo);
        $nome_completo = count($nome_completo) > 2 ? array_shift($nome_completo).' '.array_pop($nome_completo) : $usuario1->nome;
        $usuario1->nome = $nome_completo;

        $nome_completo2 = $usuario2->nome;
        $nome_completo2 = explode(' ', $nome_completo2);
        $nome_completo2 = count($nome_completo2) > 2 ? array_shift($nome_completo2).' '.array_pop($nome_completo2) : $usuario2->nome;
        $usuario2->nome = $nome_completo2;

        //$usuario1->distintivo = (isset($usuario1->distintivo) && !empty($usuario1->distintivo)) ? $usuario1->distintivo : $usuario1->imagem_perfil;
        //$usuario2->distintivo = (isset($usuario2->distintivo) && !empty($usuario2->distintivo)) ? $usuario2->distintivo : $usuario2->imagem_perfil;
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

    public function usuariosComClassificacao_old()
    {
        $pontuacoes = $this->fase()->pontuacoes();
        $pontuacaoVitoria = $pontuacoes[1];
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
                if($usuario->jogos > 0) {
                    $usuario->aproveitamento = number_format(($usuario->pontuacao)/($usuario->jogos*$pontuacaoVitoria)*100, 2);
                } else {
                    $usuario->aproveitamento = number_format(0, 2);
                }

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

        foreach ($usuarios as $usuario) {
            $usuario->id_no_grupo = $usuario->pivot->id;
        }

        $fase = $this->fase();
        $campeonato = $fase->campeonato();
        return $campeonato->ordenarUsuariosPorCriterioDeClassificacao($usuarios);
    }

    public function usuariosComClassificacao() {
        $tipo_competidor = $this->fase()->campeonato()->tipo_competidor;

        $pontuacoes = $this->fase()->pontuacoes();
        $pontuacaoVitoria = $pontuacoes[1];
        $usuarios = $this->usuarios();

        if($usuarios->isEmpty()) {
            return true;
        }
        $partida = $this->partidas()->first();

        $quantidade_jogadores_por_partida = 0;
        if ($partida != null) {
            $quantidade_jogadores_por_partida = $partida->usuarios()->count();
        }

        if ($quantidade_jogadores_por_partida == 2) {
            // Partida com Dois Jogadores


            foreach ($usuarios as $usuario) {
                $idFaseGrupo = $this->id;
                $idUsuario = $usuario->id;

                if($tipo_competidor == 'equipe') {
                    $tabelaCampeonato = DB::table(DB::raw("(select 	p1.partidas_id as partidas, p1.placar as placar1, p1.pontuacao,
                            p2.placar as placar2,
                            (case when p1.placar > p2.placar then 1 end) as vitorias,
                            (case when p1.placar < p2.placar then 1 end) as derrotas,
                            (case when p1.placar = p2.placar then 1 end) as empates
                        from usuario_partidas p1, usuario_partidas p2
                        where p1.equipe_id = $idUsuario and p2.equipe_id <> $idUsuario and p1.partidas_id = p2.partidas_id AND p1.partidas_id IN (
                            select id from partidas where data_placar IS NOT NULL AND fase_grupos_id = $idFaseGrupo AND id IN (
                                select partidas_id from usuario_partidas where equipe_id = $idUsuario
                            )
                            order by rodada
                        ) order by p1.partidas_id) as tabela"))
                        ->selectRaw("sum(pontuacao) as pontuacao,
                        count(partidas) as partidas,
                        sum(vitorias) as vitorias,
                        sum(empates) as empates,
                        sum(derrotas) as derrotas,
                        sum(placar1) as gols_pro,
                        sum(placar2) as gols_contra,
                        sum(placar1) - sum(placar2) as saldo_gols")->first();
                } else {
                    if($usuario->anonimo) {
                        $compara_usuario = "p1.anonimo_id = $idUsuario and p2.anonimo_id IS DISTINCT FROM $idUsuario";
                        $compara_usuario2 = "anonimo_id = $idUsuario";
                    } else {
                        $compara_usuario = "p1.users_id = $idUsuario and p2.users_id IS DISTINCT FROM $idUsuario";
                        $compara_usuario2 = "users_id = $idUsuario";
                    }
                    $tabelaCampeonato = DB::table(DB::raw("(select 	p1.partidas_id as partidas, p1.placar as placar1, p1.pontuacao,
                            p2.placar as placar2,
                            (case when p1.placar > p2.placar then 1 end) as vitorias,
                            (case when p1.placar < p2.placar then 1 end) as derrotas,
                            (case when p1.placar = p2.placar then 1 end) as empates
                        from usuario_partidas p1, usuario_partidas p2
                        where $compara_usuario and p1.partidas_id = p2.partidas_id AND p1.partidas_id IN (
                            select id from partidas where data_placar IS NOT NULL AND fase_grupos_id = $idFaseGrupo AND id IN (
                                select partidas_id from usuario_partidas where $compara_usuario2
                            )
                            order by rodada
                        ) order by p1.partidas_id) as tabela"))
                        ->selectRaw("sum(pontuacao) as pontuacao,
                        count(partidas) as partidas,
                        sum(vitorias) as vitorias,
                        sum(empates) as empates,
                        sum(derrotas) as derrotas,
                        sum(placar1) as gols_pro,
                        sum(placar2) as gols_contra,
                        sum(placar1) - sum(placar2) as saldo_gols")->first();
                }


                $usuario->pontuacao = intval($tabelaCampeonato->pontuacao);
                $usuario->jogos = intval($tabelaCampeonato->partidas);
                $usuario->vitorias = intval($tabelaCampeonato->vitorias);
                $usuario->empates = intval($tabelaCampeonato->empates);
                $usuario->derrotas = intval($tabelaCampeonato->derrotas);
                $usuario->gols_pro = intval($tabelaCampeonato->gols_pro);
                $usuario->gols_contra = intval($tabelaCampeonato->gols_contra);
                $usuario->saldo_gols = intval($tabelaCampeonato->saldo_gols);
                if($usuario->jogos > 0) {
                    $usuario->aproveitamento = number_format(($usuario->pontuacao)/($usuario->jogos*$pontuacaoVitoria)*100, 2);
                } else {
                    $usuario->aproveitamento = number_format(0, 2);
                }

            }
        } else {
            // Partida com mais de 2 jogadores
            $partidas = $this->partidas();
            foreach ($partidas as $partida) {
                foreach ($partida->usuarios() as $usuarioPartida) {
                    if (isset($usuarioPartida->posicao)) {
                        if($tipo_competidor == 'equipe') {
                            $usuarios->find($usuarioPartida->equipe_id)->pontuacao += intval($usuarioPartida->pontuacao);
                        } else {
                            if($usuarioPartida->anonimo_id) {
                                $usuarios->find($usuarioPartida->anonimo_id)->pontuacao += intval($usuarioPartida->pontuacao);
                            } else {
                                $usuarios->find($usuarioPartida->users_id)->pontuacao += intval($usuarioPartida->pontuacao);
                            }
                        }
                    }
                }
            }
        }

        foreach ($usuarios as $usuario) {
            $usuario->id_no_grupo = $usuario->pivot->id;
        }

        $fase = $this->fase();
        $campeonato = $fase->campeonato();
        return $campeonato->ordenarUsuariosPorCriterioDeClassificacao($usuarios);
    }

    public function partidas()
    {
        $partidas = $this->hasMany('Partida', 'fase_grupos_id')->orderBy('rodada')->getResults();
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

        $tipo_competidor = $this->fase()->campeonato()->tipo_competidor;

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
                    if($tipo_competidor == 'equipe') {
                        $usuariosClassificados->put(1, User::find($u1A->equipe_id));
                    } else {
                        if($u1A->anonimo_id) {
                            $usuariosClassificados->put(1, UserAnonimo::find($u1A->anonimo_id));
                        } else {
                            $usuariosClassificados->put(1, User::find($u1A->users_id));
                        }
                    }
                } else if($placar1 < $placar2) {
                    if($tipo_competidor == 'equipe') {
                        $usuariosClassificados->put(1, User::find($u2A->equipe_id));
                    } else {
                        if($u2A->anonimo_id) {
                            $usuariosClassificados->put(1, UserAnonimo::find($u2A->anonimo_id));
                        } else {
                            $usuariosClassificados->put(1, User::find($u2A->users_id));
                        }
                    }
                } else {
                    if ($detalhesDoCampeonato->fora_casa && ($u1A->placar != $u2B->placar)) {
                        if($u1A->placar > $u2B->placar) {
                            if($tipo_competidor == 'equipe') {
                                $usuariosClassificados->put(1, User::find($u2A->equipe_id));
                            } else {
                                if($u2A->anonimo_id) {
                                    $usuariosClassificados->put(1, UserAnonimo::find($u2A->anonimo_id));
                                } else {
                                    $usuariosClassificados->put(1, User::find($u2A->users_id));
                                }
                            }
                        } else {
                            if($tipo_competidor == 'equipe') {
                                $usuariosClassificados->put(1, User::find($u1A->equipe_id));
                            } else {
                                if($u1A->anonimo_id) {
                                    $usuariosClassificados->put(1, UserAnonimo::find($u1A->anonimo_id));
                                } else {
                                    $usuariosClassificados->put(1, User::find($u1A->users_id));
                                }
                            }
                        }
                    } else {
                        if($u1B->placar_extra > $u2B->placar_extra) {
                            if($tipo_competidor == 'equipe') {
                                $usuariosClassificados->put(1, User::find($u1A->equipe_id));
                            } else {
                                if($u1A->anonimo_id) {
                                    $usuariosClassificados->put(1, UserAnonimo::find($u1A->anonimo_id));
                                } else {
                                    $usuariosClassificados->put(1, User::find($u1A->users_id));
                                }
                            }
                        } else {
                            if($tipo_competidor == 'equipe') {
                                $usuariosClassificados->put(1, User::find($u2A->equipe_id));
                            } else {
                                if($u2A->anonimo_id) {
                                    $usuariosClassificados->put(1, UserAnonimo::find($u2A->anonimo_id));
                                } else {
                                    $usuariosClassificados->put(1, User::find($u2A->users_id));
                                }
                            }
                        }
                    }
                }
            } else {
                $u1 = $partidas->first()->usuarios()->first();
                $u2 = $partidas->first()->usuarios()->last();

                if(isset($detalhesDoCampeonato->numero_rounds) && $detalhesDoCampeonato->numero_rounds > 1) {
                    $placarUsuario1 = 0;
                    $placarUsuario2 = 0;
                    foreach ($partidas as $partida) {
                        if($tipo_competidor == 'equipe') {
                            if($partida->placarUsuario($u1->equipe_id) > $partida->placarUsuario($u2->equipe_id)) {
                                $placarUsuario1++;
                            } else if($partida->placarUsuario($u1->equipe_id) < $partida->placarUsuario($u2->equipe_id)) {
                                $placarUsuario2++;
                            }
                        } else {
                            if($partida->placarUsuario($u1->users_id, $u1->anonimo_id) > $partida->placarUsuario($u2->users_id, $u2->anonimo_id)) {
                                $placarUsuario1++;
                            } else if($partida->placarUsuario($u1->users_id, $u1->anonimo_id) < $partida->placarUsuario($u2->users_id, $u2->anonimo_id)) {
                                $placarUsuario2++;
                            }
                        }
                    }
                } else {
                    $placarUsuario1 = $u1->placar;
                    $placarUsuario2 = $u2->placar;
                }
                if ($placarUsuario1 > $placarUsuario2) {
                    if($tipo_competidor == 'equipe') {
                        $usuariosClassificados->put(1, User::find($u1->equipe_id));
                    } else {
                        if($u1->anonimo_id) {
                            $usuarioAnonimo = UserAnonimo::find($u1->anonimo_id);
                            $usuarioAnonimo->anonimo = true;
                            $usuariosClassificados->put(1, $usuarioAnonimo);
                        } else {
                            $usuariosClassificados->put(1, User::find($u1->users_id));
                        }
                    }
                } else {
                    if($tipo_competidor == 'equipe') {
                        $usuariosClassificados->put(1, User::find($u2->equipe_id));
                    } else {
                        if($u2->anonimo_id) {
                            $usuarioAnonimo = UserAnonimo::find($u2->anonimo_id);
                            $usuarioAnonimo->anonimo = true;
                            $usuariosClassificados->put(1, $usuarioAnonimo);
                        } else {
                            $usuariosClassificados->put(1, User::find($u2->users_id));
                        }
                    }
                }
            }
        } else {
            $proximaFase = $fase->proximaFase();
            $quantidadeClassificados = $proximaFase->quantidade_usuarios / $fase->grupos()->count();
            $usuariosComClassificacao = $this->usuariosComClassificacao();
            $usuariosClassificados = $usuariosComClassificacao->take($quantidadeClassificados);
        }
        return $usuariosClassificados;
    }

}
