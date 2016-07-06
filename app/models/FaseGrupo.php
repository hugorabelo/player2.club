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
        return $this->belongsToMany('User', 'usuario_grupos', 'fase_grupos_id', 'users_id')->withPivot(array('pontuacao'))->orderBy('pontuacao', 'desc')->getResults();
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
                //$pontuacao = ($num_vitorias * 3) + $num_empates;
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
        $usuarios->sortByDesc('pontuacao');
        $usuarios->values()->all();
        return $usuarios;
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

        $usuarios = $this->usuarios();
        $partidas = $this->partidas();

        if ($usuarios->first() == null || $partidas->first() == null) {
            return array();
        }

        $usuariosClassificados = array();
        if ($fase->matamata) {
            $quantidadeClassificados = 1;
            if ($detalhesDoCampeonato->ida_volta) {
                // TODO Ainda precisa definir o placar extra (penaltis, etc)
                if ($detalhesDoCampeonato->fora_casa) {

                } else {

                }
            } else {
                $u1 = $partidas->first()->usuarios()->first();
                $u2 = $partidas->first()->usuarios()->last();
                if ($u1->placar > $u2->placar) {
                    return array($u1);
                } else {
                    return array($u2);
                }
            }
        } else {
            $quantidadeClassificadosMataMata = $detalhesDoCampeonato->classificados_proxima_fase;
            $quantidadeClassificados = $quantidadeClassificadosMataMata / $fase->grupos()->count();
            $quantidadeUsuariosInseridos = 0;
            foreach ($usuarios as $usuarioInserido) {
                array_push($usuariosClassificados, $usuarioInserido);
                $quantidadeUsuariosInseridos++;
                if ($quantidadeUsuariosInseridos == $quantidadeClassificados) {
                    break;
                }
            }
        }
        return $usuariosClassificados;
    }

}
