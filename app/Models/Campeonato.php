<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class Campeonato extends Eloquent {
	protected $guarded = array();

    protected $table = 'campeonatos';

	public static $rules = array(
		'descricao' => 'required',
		'jogos_id' => 'required',
		'campeonato_tipos_id' => 'required',
		'plataformas_id' => 'required'
	);

	public function jogo() {
		return Jogo::find($this->jogos_id);
	}

	public function campeonatoTipo() {
		return CampeonatoTipo::find($this->campeonato_tipos_id);
	}

	public function plataforma() {
		return Plataforma::find($this->plataformas_id);
	}

    public function administradores() {
		return $this->belongsToMany('User', 'campeonato_admins', 'campeonatos_id', 'users_id')->getResults();
    }

	public function usuariosInscritos() {
        if($this->tipo_competidor == 'equipe') {
            return $this->belongsToMany('Equipe', 'campeonato_usuarios', 'campeonatos_id', 'equipe_id')->withPivot(array('id', 'time_id'))->getResults();
        } else {
            $usuariosCadastrados = $this->belongsToMany('User', 'campeonato_usuarios', 'campeonatos_id', 'users_id')->withPivot(array('id', 'time_id'))->getResults();
            $usuariosAnonimos = $this->usuariosAnonimos();
            $usuariosNovo = $usuariosCadastrados;
            foreach ($usuariosAnonimos as $anonimo) {
                $anonimo->anonimo = true;
                $usuariosNovo->push($anonimo);
            }
            return $usuariosNovo;
        }
	}

    public function usuariosAnonimos() {
        return $this->belongsToMany('UserAnonimo', 'campeonato_usuarios', 'campeonatos_id', 'anonimo_id')->withPivot(array('id', 'time_id'))->getResults();
    }

	public function maximoUsuarios() {
		/*
		 * Alterar para quantidade maxima de usuarios da fase inicial
		 */
        if($this->faseInicial() == null) {
            return 0;
        }
        $quantidade_maxima = $this->faseInicial()->quantidade_usuarios;
		return $quantidade_maxima;
	}

	public function fases() {
        $fasesOrdenadas = new Collection();
        $faseAdicionada = $this->faseFinal();
        $fasesOrdenadas->prepend($faseAdicionada);
        while($faseAdicionada = $faseAdicionada->faseAnterior()) {
            $fasesOrdenadas->prepend($faseAdicionada);
        }
        return $fasesOrdenadas;
	}

	public function faseInicial() {
		return $this->hasMany('CampeonatoFase', 'campeonatos_id')->where('inicial', '=', 'true')->get()->first();
	}

	public function faseFinal() {
		return $this->hasMany('CampeonatoFase', 'campeonatos_id')->where('final', '=', 'true')->get()->first();
	}

	public function validarNumeroDeCompetidores($detalhes) {
		if($detalhes['quantidade_competidores'] > 0) {
			return '';
		}
		return 'messages.numero_competidores_maior_zero';
	}

	public function detalhes() {
		return $this->hasOne('CampeonatoDetalhes', 'campeonatos_id')->getResults();
	}

	public function salvarPlacar($partida) {
	    $partidaBD = Partida::find($partida['id']);
        $contestada = isset($partida['edita_contestacao']);
        $placarAdministrador = isset($partida['placar_administrador']);
        if(isset($partidaBD->data_placar) && !$contestada && !$placarAdministrador) {
            return 'messages.placares_existente';
        }
		$nomeClasse = $this->campeonatoTipo()->nome_classe_modelo;
        $retorno = $nomeClasse::salvarPlacarPartida($partida);
        return $retorno;
    }

    public function abreFase($dadosFase, $faseAtual, $campeonato) {
        $nomeClasse = $this->campeonatoTipo()->nome_classe_modelo;
		$novoCampeonato = new $nomeClasse($this->toArray());

        return $novoCampeonato->iniciaFase($dadosFase, $faseAtual, $campeonato);
    }

	public function fechaFase($dadosFase) {
		$nomeClasse = $this->campeonatoTipo()->nome_classe_modelo;
		$novoCampeonato = new $nomeClasse($this->toArray());

		return $novoCampeonato->encerraFase($dadosFase);
	}

	/**
	 * Atualizar as datas de cada fase, de acordo com a data de encerramento da fase atual, atualizar para as pr??ximas fases
	 *
	 * @param fase Fase atual com as informa????es
	 * @param novaData Data final da fase atual
	 *
	 */
	protected function atualizarDatasFases($fase, $novaData) {
        $novaData = strstr($novaData, " (", true);
		$data = Carbon::parse($novaData);
		$fase->data_fim = $data;
		$fase->update();

        $arrayUpdateRodadas = array('data_prazo'=>$data);

        if($fase->matamata) {
            $arrayUpdateRodadas['liberada'] = true;
        }

        Partida::whereIn('fase_grupos_id', FaseGrupo::where('campeonato_fases_id', '=', $fase->id)->get(array('id')))
                ->update($arrayUpdateRodadas);

        $outraData = DB::table('campeonato_fases')->selectRaw("data_fim + '1 day' as nova_data")->where('id','=',$fase->id)->first();
        $outraData = $outraData->nova_data;
        $outraData = strstr($outraData, " (", true);
        $outraData = Carbon::parse($outraData);

		$proximaFase = $fase;
		while($proximaFase = $proximaFase->proximaFase()) {
			$proximaFase->data_inicio = $outraData;
			$proximaFase->update();
		}
	}

	/**
	 * Recuperar a lista de crit??rios de classifica????o escolhidos para este campeonato
	 *
	 * @return Collection Lista Ordenada de Crit??rios
	 */
	public function criteriosOrdenados() {
		$criterios = $this->belongsToMany('CriterioClassificacao', 'campeonato_criterios', 'campeonatos_id', 'criterios_classificacao_id')->withPivot(array('ordem'))->orderBy('ordem')->getResults();
		return $criterios;
	}

	public function ordenarUsuariosPorCriterioDeClassificacao($usuarios) {
		$criteriosDeClassificacao = $this->criteriosOrdenados();

        /*
		$makeComparer = function($criteria) {
			$comparer = function ($first, $second) use ($criteria) {
				foreach ($criteria as $key => $orderType) {
					$orderType = strtolower($orderType);
					if ($first[$key] < $second[$key]) {
						return $orderType === "menor" ? -1 : 1;
					} else if ($first[$key] > $second[$key]) {
						return $orderType === "menor" ? 1 : -1;
					}
				}
				return 0;
			};
			return $comparer;
		};
        */

		$sort = app()->make(Collection::class);
		foreach ($criteriosDeClassificacao as $criterio) {
			$sort->put($criterio->valor, $criterio->ordenacao);
		}
        $sort->put('id_no_grupo', 'menor');
        $sort = $sort->toArray();
        /*
		$comparer = $makeComparer($sort);
		$usuariosRetorno = $usuarios->sort($comparer);
        $usuariosRetorno->values()->all();
        */

        $comparer = app()->make("collection.multiSort",$sort);
        $sorted = $usuarios->sort($comparer);
        $usuariosRetorno = app()->make(Collection::class);
        $usuariosRetorno = $sorted->values();

		return $usuariosRetorno;
	}

    public function iniciaFase($dadosFase, $faseAtual, $campeonato)
    {

        if($faseAtual->aberta) {
            return true;
        }
        /*
         * Objeto Fase deve conter os seguintes atributos:
         * - id : ID da fase
         * - data_encerramento: Data de encerramento da fase a ser iniciada (Para cada fase seguinte, atualizar as datas de in??cio, baseadas nesta)
         * - tipo_sorteio_matamata: Se for uma fase de mata mata, definir o tipo de sorteio (melhor geral x pior geral | melhor grupo x pior grupo | aleat??ria)
         */
        /*
         * 1. Verifica se a fase anterior est?? fechada, caso contr??rio fechar automaticamente (avisar ao usu??rio)
         * 2. Inscrever usu??rios classificados da fase anterior
         * 3. Sortear Grupos e Jogos
         * 4. Habilitar inser????o de resultados
         */

        /** 2. Inscrever usu??rios classificados da fase anterior */
        if ($faseAtual == $campeonato->faseInicial()) {
            // Remover usu??rios que j?? estejam na fase devido a algum erro
            UsuarioFase::where('campeonato_fases_id','=',$faseAtual->id)->delete();

            $usuariosDaFase = $campeonato->usuariosInscritos();

            foreach ($usuariosDaFase as $posicao => $usuario) {
                if($this->tipo_competidor == 'equipe') {
                    UsuarioFase::create(['equipe_id' => $usuario->id, 'campeonato_fases_id' => $faseAtual->id]);
                } else {
                    if($usuario->anonimo) {
                        UsuarioFase::create(['anonimo_id' => $usuario->id, 'campeonato_fases_id' => $faseAtual->id]);
                    } else {
                        UsuarioFase::create(['users_id' => $usuario->id, 'campeonato_fases_id' => $faseAtual->id]);
                    }
                }
            }
        } else {
            $faseAnterior = $faseAtual->faseAnterior();
            $usuariosDaFase = $faseAnterior->usuariosClassificados();
        }
        $gruposDaFase = $faseAtual->grupos();

        // Sortear Grupos e Jogos
        /** 3. Sortear Grupos e Jogos */
        $this->sorteioGrupos($gruposDaFase, $usuariosDaFase, $dadosFase);

        $detalhesCampeonato = $campeonato->detalhes();
        $idaVolta = $detalhesCampeonato->ida_volta;
        foreach ($faseAtual->grupos() as $grupo) {
            if ($idaVolta) {
                $this->sorteioJogosUmContraUm($grupo, 2);
            } else {
                $this->sorteioJogosUmContraUm($grupo, 1);
            }
        }

        $campeonato->atualizarDatasFases($faseAtual, $dadosFase['data_fim']);

        $faseAtual->aberta = true;
        $faseAtual->update();

        //TODO Enviar notifica????o para todos os membros das equipes (ou pelo menos para os administradores)
        $evento = NotificacaoEvento::where('valor','=','fase_iniciada')->first();
        if(isset($evento)) {
            $idEvento = $evento->id;
        }
        foreach ($usuariosDaFase as $usuario) {
            if(!$usuario->anonimo_id && !$usuario->anonimo) {
                $notificacao = new Notificacao();
                $notificacao->id_destinatario = $usuario->id;
                $notificacao->evento_notificacao_id = $idEvento;
                $notificacao->item_id = $faseAtual->id;
                $notificacao->save();
            }
        }

        return $usuariosDaFase;
    }

    public function encerraFase($dadosFase)
    {
        $fase = CampeonatoFase::find($dadosFase['id']);
        if(!$fase->aberta) {
            return true;
        }
        $proximaFase = $fase->proximaFase();

        if(isset($proximaFase)) {
            // Remover usu??rios que j?? estejam na fase seguinte devido a algum erro
            UsuarioFase::where('campeonato_fases_id', '=', $proximaFase->id)->delete();
        }

        foreach ($fase->grupos() as $grupo) {
            // contabilizar jogos sem resultado (0 pontos para todos os participantes)
            foreach ($grupo->partidas() as $partida) {
                if(!isset($partida->data_placar)) {
                    $partida->usuarios = $partida->usuarios();
                    $this->aplicarWO($partida);
                } else if (!isset($partida->data_confirmacao)) {
                    $partidaBD = Partida::find($partida['id']);
                    $partidaBD->data_confirmacao = date('Y-m-d H:i:s');
                    $partidaBD->save();
                }
            }

            // contabilizar pontua????o e quantidade de classificados (por grupo) - INSCREVER USU??RIOS CLASSIFICADOS NA FASE SEGUINTE
            if(isset($proximaFase)) {
                $posicaoUsuario = 1;


                foreach ($grupo->usuariosClassificados() as $usuario) {
                    $usuarioFase = new UsuarioFase();
                    $usuarioFase->campeonato_fases_id = $proximaFase->id;
                    if($this->tipo_competidor == 'equipe') {
                        $usuarioFase->equipe_id = $usuario->id;
                    } else {
                        if($usuario->anonimo) {
                            $usuarioFase->anonimo_id = $usuario->id;
                        } else {
                            $usuarioFase->users_id = $usuario->id;
                        }
                    }
                    $usuarioFase->posicao_fase_anterior = $posicaoUsuario;
                    $usuarioFase->save();
                    $posicaoUsuario++;
                }
            }
        }

        // Desabilitar inser????o de resultados
        $fase->aberta = false;
        $fase->encerrada = true;
        $fase->update();

        $evento = NotificacaoEvento::where('valor','=','fase_encerrada')->first();
        if(isset($evento)) {
            $idEvento = $evento->id;
        }
        $usuariosDaFase = $fase->usuarios();
        foreach ($usuariosDaFase as $usuario) {
            if($this->tipo_competidor !== 'equipe') {
                if(!$usuario->anonimo) {
                    $notificacao = new Notificacao();
                    $notificacao->id_destinatario = $usuario->id;
                    $notificacao->evento_notificacao_id = $idEvento;
                    $notificacao->item_id = $fase->id;
                    $notificacao->save();
                }
            }
        }

        /*
         * Cronograma de inser????o de resultados para uma partida de campeonato
         * - Usu??rio tem at?? a data final da fase para inserir o resultado (Caso n??o exista resultado, o jogo ser?? definido como sem resultado, onde ambos os participantes ficam com pontos de ??ltimo colocado)
         * - Outro Usu??rio tem at?? 24 horas depois da hora de inser????o do resultado para confirmar o mesmo (Caso n??o seja confirmado o resultado por outro usu??rio, o placar inserido ser?? dado como definitivo).
         * -
         */
    }

    protected function sorteioJogosUmContraUm($grupo, $turnos)
    {
        $usuarios = $grupo->usuarios();
        if($usuarios->count() % 2 == 1) {
            $usuarios->prepend(null);
        }
        $n = $usuarios->count();
        $m = $n / 2;
        $numero_rodadas_por_turno = ($n - 1);
        $numero_rodada = 1;

        // Remover partidas que j?? estejam no grupo devido a algum erro
        Partida::where('fase_grupos_id','=',$grupo->id)->delete();

        for ($t = 0; $t < $turnos; $t++) {
            for ($i = 0; $i < $numero_rodadas_por_turno; $i++) {
                for ($j = 0; $j < $m; $j++) {
                    if($usuarios->get($j) == null) {
                        continue;
                    }
                    $partida = Partida::create(['fase_grupos_id' => $grupo->id, 'rodada' => $numero_rodada]);
                    if ($t % 2 == 1) {
                        if ($j % 2 == 1 || $i % 2 == 1 && $j == 0) {
                            if($this->tipo_competidor == 'equipe') {
                                UsuarioPartida::create(['partidas_id' => $partida->id, 'equipe_id' => $usuarios->get($n - $j - 1)->id]);
                                UsuarioPartida::create(['partidas_id' => $partida->id, 'equipe_id' => $usuarios->get($j)->id]);
                            } else {
                                if($usuarios->get($n - $j - 1)->anonimo) {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'anonimo_id' => $usuarios->get($n - $j - 1)->id]);
                                } else {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($n - $j - 1)->id]);
                                }
                                if($usuarios->get($j)->anonimo) {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'anonimo_id' => $usuarios->get($j)->id]);
                                } else {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($j)->id]);
                                }
                            }
                        } else {
                            if($this->tipo_competidor == 'equipe') {
                                UsuarioPartida::create(['partidas_id' => $partida->id, 'equipe_id' => $usuarios->get($j)->id]);
                                UsuarioPartida::create(['partidas_id' => $partida->id, 'equipe_id' => $usuarios->get($n - $j - 1)->id]);
                            } else {
                                if($usuarios->get($j)->anonimo) {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'anonimo_id' => $usuarios->get($j)->id]);
                                } else {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($j)->id]);
                                }
                                if($usuarios->get($n - $j - 1)->anonimo) {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'anonimo_id' => $usuarios->get($n - $j - 1)->id]);
                                } else {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($n - $j - 1)->id]);
                                }
                            }
                        }
                    } else {
                        if ($j % 2 == 1 || $i % 2 == 1 && $j == 0) {
                            if($this->tipo_competidor == 'equipe') {
                                UsuarioPartida::create(['partidas_id' => $partida->id, 'equipe_id' => $usuarios->get($j)->id]);
                                UsuarioPartida::create(['partidas_id' => $partida->id, 'equipe_id' => $usuarios->get($n - $j - 1)->id]);
                            } else {
                                if($usuarios->get($j)->anonimo) {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'anonimo_id' => $usuarios->get($j)->id]);
                                } else {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($j)->id]);
                                }

                                if($usuarios->get($n - $j - 1)->anonimo) {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'anonimo_id' => $usuarios->get($n - $j - 1)->id]);
                                } else {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($n - $j - 1)->id]);
                                }
                            }
                        } else {
                            if($this->tipo_competidor == 'equipe') {
                                UsuarioPartida::create(['partidas_id' => $partida->id, 'equipe_id' => $usuarios->get($n - $j - 1)->id]);
                                UsuarioPartida::create(['partidas_id' => $partida->id, 'equipe_id' => $usuarios->get($j)->id]);
                            } else {
                                if($usuarios->get($n - $j - 1)->anonimo) {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'anonimo_id' => $usuarios->get($n - $j - 1)->id]);
                                } else {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($n - $j - 1)->id]);
                                }

                                if($usuarios->get($j)->anonimo) {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'anonimo_id' => $usuarios->get($j)->id]);
                                } else {
                                    UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($j)->id]);
                                }
                            }
                        }
                    }
                }
                $numero_rodada++;
                $usuarios = $this->sorteioReordena($usuarios);
            }
        }
    }

    private function sorteioReordena($colecao)
    {
        $novaColecao = new Collection();
        $novaColecao->add($colecao->shift());
        $novaColecao->add($colecao->pop());
        foreach ($colecao as $elemento) {
            $novaColecao->add($elemento);
        }
        return $novaColecao;
    }

    protected function sorteioGrupos($grupos, $usuarios, $dadosFase)
    {
        /*
         * Objeto Fase deve conter os seguintes atributos:
         * - id : ID da fase
         * - data_encerramento: Data de encerramento da fase a ser iniciada (Para cada fase seguinte, atualizar as datas de in??cio, baseadas nesta)
         * - tipo_sorteio_matamata: Se for uma fase de mata mata, definir o tipo de sorteio (melhor geral x pior geral | melhor grupo x pior grupo | aleat??rio)
         *      No futuro, o tipo de sorteio vai poder ser manual
         */

        $usuariosInseridos = array();
        $fase = CampeonatoFase::find($dadosFase['id']);

        // Remover usu??rios que j?? estejam no grupo devido a algum erro
        foreach ($grupos as $grupo) {
            UsuarioGrupo::where('fase_grupos_id','=',$grupo->id)->delete();
        }

        if($fase->faseAnterior() != null && $fase->faseAnterior()->matamata && $dadosFase['tipo_sorteio_matamata'] != 'aleatorio') {
            foreach ($usuarios as $usuario) {
                $grupoAnteriorDoUsuario = $this->getGrupoAnteriorUsuario($usuario->id, $fase);
                $usuario->grupoAnterior = $grupoAnteriorDoUsuario;
            }
            $usuarios = $usuarios->sortBy('grupoAnterior');
            foreach($grupos as $grupo) {
                $usuario1 = $usuarios->shift();
                $usuario2 = $usuarios->shift();
                if($this->tipo_competidor == 'equipe') {
                    UsuarioGrupo::create(['equipe_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                    UsuarioGrupo::create(['equipe_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);
                } else {
                    if($usuario1->anonimo) {
                        UsuarioGrupo::create(['anonimo_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                    } else {
                        UsuarioGrupo::create(['users_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                    }
                    if($usuario2->anonimo) {
                        UsuarioGrupo::create(['anonimo_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);
                    } else {
                        UsuarioGrupo::create(['users_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);
                    }
                }
            }
        } else {
            if ($fase->matamata && $dadosFase['tipo_sorteio_matamata'] != 'aleatorio') {
                $maximaPosicao = 0;
                foreach ($usuarios as $user) {
                    $posicao = UsuarioFase::encontraUsuarioFase($user->id, $fase->id, $this->tipo_competidor, $user->anonimo)->posicao_fase_anterior;
                    if ($posicao > $maximaPosicao) {
                        $maximaPosicao = $posicao;
                    }
                }
                for ($i = 1; $i<=$maximaPosicao; $i++) {
                    $lista[$i] = new Collection();
                }

                foreach ($usuarios as $usuario) {
                    $posicao = UsuarioFase::encontraUsuarioFase($usuario->id, $fase->id, $this->tipo_competidor, $usuario->anonimo)->posicao_fase_anterior;
                    $grupoAnteriorDoUsuario = $this->getGrupoAnteriorUsuario($usuario->id, $fase);
                    $usuario->grupoAnterior = $grupoAnteriorDoUsuario;
                    $lista[$posicao]->push($usuario);
                }

                // TODO Testar essa regra para mais grupos (Ex. 8 grupos)
                if ($dadosFase['tipo_sorteio_matamata'] == 'geral') {
                    // Precisa-se ordernar os usu??rios dentro de cada lista pelos crit??rios de classifica????o
                    for ($i = 1; $i<=$maximaPosicao; $i++) {
                        $lista[$i] = $this->ordenarUsuariosPorCriterioDeClassificacao($lista[$i]);
                    }

                    $indiceGrupoAtual = 0;
                    $indicePosicaoInicial = 1;
                    $indicePosicaoFinal = $maximaPosicao;
                    $invertePosicao = 2;

                    while($indiceGrupoAtual < $grupos->count()) {
                        $grupo = $grupos->get($indiceGrupoAtual);

                        if($invertePosicao > 0) {
                            $usuario1 = $lista[$indicePosicaoInicial]->shift();
                            $usuario2 = $lista[$indicePosicaoFinal]->pop();
                            $invertePosicao++;
                        } else if ($invertePosicao < 0){
                            $usuario1 = $lista[$indicePosicaoInicial]->pop();
                            $usuario2 = $lista[$indicePosicaoFinal]->shift();
                            $invertePosicao--;
                        }
                        if($this->tipo_competidor == 'equipe') {
                            UsuarioGrupo::create(['equipe_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                            UsuarioGrupo::create(['equipe_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);
                        } else {
                            if($usuario1->anonimo) {
                                UsuarioGrupo::create(['anonimo_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                            } else {
                                UsuarioGrupo::create(['users_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                            }
                            if($usuario2->anonimo) {
                                UsuarioGrupo::create(['anonimo_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);
                            } else {
                                UsuarioGrupo::create(['users_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);
                            }
                        }

                        $indicePosicaoInicial++;
                        $indicePosicaoFinal--;
                        if($indicePosicaoInicial > $indicePosicaoFinal) {
                            $indicePosicaoInicial = 1;
                            $indicePosicaoFinal = $maximaPosicao;
                        }
                        $indiceGrupoAtual++;
                        // Regra para inverter posi????es dos jogos para ficar 1,2,2,2...,1 (um do inicio, dois do fim, dois do in??cio, ...)
                        if($invertePosicao > 2) {
                            $invertePosicao = -1;
                        } else if($invertePosicao < -2) {
                            $invertePosicao = 1;
                        }
                    }

                } else if ($dadosFase['tipo_sorteio_matamata'] == 'grupo') {
                    // Precisa ordenar os usu??rios dentro de cada lista pelo ordem dos grupos
                    for ($i = 1; $i<=$maximaPosicao; $i++) {
                        $lista[$i] = $lista[$i]->sortBy('grupoAnterior');
                    }

                    $quantidadeGrupos = sizeof($lista[1]);
                    $indiceGrupoAtual = 0;
                    $indicePosicaoInicial = 1;
                    $indicePosicaoFinal = $maximaPosicao;
                    $invertePosicao = false;
                    $indiceGrupoInicial = 0;
                    $indiceGrupoFinal = $quantidadeGrupos-1;

                    //TODO Problema a partir daqui
                    while($indiceGrupoAtual < $grupos->count()) {
                        $grupo = $grupos->get($indiceGrupoAtual);
                        if(($indiceGrupoInicial == $quantidadeGrupos) && ($indiceGrupoFinal < 0)) {
                            $indiceGrupoInicial = 0;
                            $indiceGrupoFinal = $quantidadeGrupos-1;
                            $invertePosicao = !$invertePosicao;
                        }

                        if($invertePosicao) {
                            // Pegar mandante do final da lista
                            $usuario1 = $lista[$indicePosicaoInicial]->get($indiceGrupoFinal);
                            $indiceGrupoFinal--;
                            $usuario2 = $lista[$indicePosicaoFinal]->get($indiceGrupoFinal);
                            $indiceGrupoFinal--;
                        } else {
                            // Pegar mandante do in??cio da lista
                            $usuario1 = $lista[$indicePosicaoInicial]->get($indiceGrupoInicial);
                            $indiceGrupoInicial++;
                            $usuario2 = $lista[$indicePosicaoFinal]->get($indiceGrupoInicial);
                            $indiceGrupoInicial++;
                        }

                        if($this->tipo_competidor == 'equipe') {
                            UsuarioGrupo::create(['equipe_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                            UsuarioGrupo::create(['equipe_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);
                        } else {
                            if($usuario2->anonimo) {
                                UsuarioGrupo::create(['anonimo_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                            } else {
                                UsuarioGrupo::create(['users_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                            }
                            if($usuario2->anonimo) {
                                UsuarioGrupo::create(['anonimo_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);
                            } else {
                                UsuarioGrupo::create(['users_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);
                            }
                        }

                        $indicePosicaoInicial++;
                        $indicePosicaoFinal--;
                        if($indicePosicaoInicial > $indicePosicaoFinal) {
                            $indicePosicaoInicial = 1;
                            $indicePosicaoFinal = $maximaPosicao;
                        }
                        $indiceGrupoAtual++;
                        $invertePosicao = !$invertePosicao;
                    }
                }
            } else {
                if(isset($dadosFase['potes'])) {
                    $potes = array_except($dadosFase['potes'], 'Principal');
                    foreach ($grupos as $grupo) {
                        foreach ($potes as $key=>$pote) {
                            $quantidade_usuarios_pote = count($pote) / count($grupos);
                            for($i = 0; $i < $quantidade_usuarios_pote; $i++) {
                                $indice = rand(0, count($pote) - 1);
                                $usuario = User::find($pote[$indice]['id']);
                                while (in_array($usuario, $usuariosInseridos)) {
                                    $indice = rand(0, count($pote) - 1);
                                    $usuario = User::find($pote[$indice]['id']);
                                }
                                if($this->tipo_competidor == 'equipe') {
                                    UsuarioGrupo::create(['equipe_id' => $usuario->id, 'fase_grupos_id' => $grupo->id]);
                                } else {
                                    if($usuario->anonimo) {
                                        UsuarioGrupo::create(['anonimo_id' => $usuario->id, 'fase_grupos_id' => $grupo->id]);
                                    } else {
                                        UsuarioGrupo::create(['users_id' => $usuario->id, 'fase_grupos_id' => $grupo->id]);
                                    }
                                }
                                array_push($usuariosInseridos, $usuario);
                            }
                        }
                    }
                } else {
                    foreach ($grupos as $grupo) {
                        for ($i = 0; $i < $grupo->quantidade_usuarios; $i++) {
                            $usuario = $usuarios->random(1);
                            while (in_array($usuario, $usuariosInseridos)) {
                                $usuario = $usuarios->random(1);
                            }
                            if($this->tipo_competidor == 'equipe') {
                                UsuarioGrupo::create(['equipe_id' => $usuario->id, 'fase_grupos_id' => $grupo->id]);
                            } else {
                                if($usuario->anonimo) {
                                    UsuarioGrupo::create(['anonimo_id' => $usuario->id, 'fase_grupos_id' => $grupo->id]);
                                } else {
                                    UsuarioGrupo::create(['users_id' => $usuario->id, 'fase_grupos_id' => $grupo->id]);
                                }
                            }
                            array_push($usuariosInseridos, $usuario);
                        }
                    }
                }
            }
        }
    }



    private function getGrupoAnteriorUsuario($id_usuario, $fase)
    {
        $faseAnterior = $fase->faseAnterior();
        if($faseAnterior != null) {
            $gruposDaFase = $faseAnterior->grupos();
            if($this->tipo_competidor == 'equipe') {
                $gruposDoUsuario = UsuarioGrupo::where('equipe_id', '=', $id_usuario)->get(array('fase_grupos_id'));
            } else {
                $gruposDoUsuario = UsuarioGrupo::where('users_id', '=', $id_usuario)->get(array('fase_grupos_id'));
            }
            foreach ($gruposDaFase as $grupoFase) {
                foreach($gruposDoUsuario as $grupoUsuario) {
                    if($grupoUsuario->fase_grupos_id == $grupoFase->id) {
                        return $grupoFase;
                    }
                }
            }
        }
        return null;
    }

    public function status() {
        /*
         * 1. Inscri????es abertas
         * 2. A iniciar
         * 3. Em andamento
         * 4. Encerrado
         */
        if($this->faseInicial() == null || $this->faseFinal() == null) {
            return 4;
        }
        if($this->usuariosInscritos()->count() < $this->maximoUsuarios()) {
            return 1;
        }
        if($this->faseFinal()->encerrada) {
            return 4;
        }
        $fase = $this->faseInicial();
        if($fase->aberta || $fase->encerrada) {
            return 3;
        }
        while($fase = $fase->proximaFase()) {
            if($fase->aberta || $fase->encerrada) {
                return 3;
            }
        }
        return 2;
    }

    public function pontuacoes($idFase = null) {
        $nomeClasse = $this->campeonatoTipo()->nome_classe_modelo;
        $novoCampeonato = new $nomeClasse($this->toArray());

        return $novoCampeonato->pontuacoes($idFase);
    }

    public function partidas() {
        $partidasDoCampeonato = app()->make(Collection::class);
        $fases = $this->fases();
        foreach ($fases as $fase) {
            $grupos = $fase->grupos();
            foreach ($grupos as $grupo) {
                $partidas = $grupo->partidas();
                foreach ($partidas as $partida) {
                    $partida->usuarios = $partida->usuarios();
                    $partidasDoCampeonato->add($partida);
                }
            }
        }
        return $partidasDoCampeonato;
    }

    public function partidasPorRodada($aberta, $rodada) {
        if($aberta) {
            $partidas = Partida::whereIn('fase_grupos_id', FaseGrupo::whereIn('campeonato_fases_id', CampeonatoFase::where('campeonatos_id', '=', $this->id)->where('aberta','=','true')->get(array('id')))->get(array('id')))
                ->where('rodada','=',$rodada)
                ->whereNull('data_confirmacao')
                ->orderBy('rodada')
                ->orderBy('fase_grupos_id')
                ->orderBy('id')
                ->get();
        } else {
            $partidas = Partida::whereIn('fase_grupos_id', FaseGrupo::whereIn('campeonato_fases_id', CampeonatoFase::where('campeonatos_id', '=', $this->id)->where('aberta','=','true')->get(array('id')))->get(array('id')))
                ->where('rodada','=',$rodada)
                ->orderBy('rodada')
                ->orderBy('fase_grupos_id')
                ->orderBy('id')
                ->get();
        }
        foreach ($partidas as $partida) {
            $partida->usuarios = $partida->usuarios();
        }
        return $partidas;
    }

    public function partidasContestadas() {
        $partidasContestadas = app()->make(Collection::class);
        $partidas = $this->partidas();
        foreach ($partidas as $partida) {
            if($partida->contestada()) {
                $partida->contestacao = ContestacaoResultado::where('partidas_id','=',$partida->id)->first();
                $partidasContestadas->add($partida);
            }
        }
        return $partidasContestadas;
    }

    public function partidasEmAberto() {
        $fases = CampeonatoFase::where('campeonatos_id','=',$this->id)->get(array('id'))->toArray();
        $grupos = FaseGrupo::whereIn('campeonato_fases_id', $fases)->get(array('id'))->toArray();
        $partidas = Partida::whereIn('fase_grupos_id',$grupos)->whereNull('data_confirmacao')->orderBy('id')->get();
        foreach($partidas as $partida) {
            if($partida->contestada()) {
                $partida->contestada = true;
            }
            $usuarios = $partida->usuarios();
            $partida->usuarios = $usuarios;
            $partida->campeonato = $partida->campeonato()->descricao;
            $partida->fase = $partida->fase()->descricao;
        }
        $partidas = $partidas->values();
        return $partidas;
    }

    public function faseAtual() {
        $fases = $this->fases();
        foreach ($fases as $fase) {
            if($fase->aberta) {
                return $fase;
            }
        }
        return null;
    }

    public function tabelaCompleta() {
        $retorno = $this;
        $faseAtual = $this->faseInicial();
        if(!isset($faseAtual)) {
            return null;
        }
        $retorno->fases = $this->fases();
        $retorno->grupos = $faseAtual->grupos();
        $partidasDaRodada = [];
        foreach ($retorno->grupos as $grupo) {
            $partidasDaRodada[] = $grupo->partidasPorRodada(1);
            $grupo->rodadas = $grupo->rodadas();
            if($faseAtual->matamata) {
                $grupo->usuarios = $grupo->usuariosMataMata();
                $grupo->partidas = $grupo->partidas();
                foreach ($grupo->partidas as $partida) {
                    $partida->usuarios = $partida->usuarios();
                }
            } else {
                $grupo->classificacao = $grupo->usuariosComClassificacao(); //TODO: Melhorar desempenho
            }
        }
        $retorno->partidasDaRodada = $partidasDaRodada;
        return $retorno;
    }

    public function aplicarWO($partida, $vencedor = 0) {
        if($vencedor > 0) {
            for($i = 0; $i< count($partida['usuarios']); $i++) {
                if($partida['usuarios'][$i]['id'] == $vencedor) {
                    $partida['usuarios'][$i]['placar'] = 1;
                } else {
                    $partida['usuarios'][$i]['placar'] = 0;
                }
            }
            $this->salvarPlacar($partida);
            $partidaBD = Partida::find($partida['id']);
            $partidaBD->data_confirmacao = date('Y-m-d H:i:s');
            $partidaBD->update();
        } else {
            foreach ($partida['usuarios'] as $usuarioPartida) {
                $dadosUsuarioUpdate = array(
                    'posicao' => -1,
                    'pontuacao' => 0,
                    'placar' => 0
                );
                $usuarioPartidaBD = UsuarioPartida::find($usuarioPartida['id']);
                $usuarioPartidaBD->update($dadosUsuarioUpdate);
            }
            $partidaBD = Partida::find($partida['id']);
            $partidaBD->data_placar = date('Y-m-d H:i:s');
            $partidaBD->data_confirmacao = date('Y-m-d H:i:s');
            $partidaBD->save();
        }
    }

    public function rodadas() {
        $faseAtual = $this->faseAtual();
        if(!isset($faseAtual)) {
            return null;
        }
        $grupo = $faseAtual->grupos()->first();

        //Bloquear todas as partidas que est??o com o prazo vencido
        $idCampeonato = $this->id;
        DB::table('partidas')->where('liberada','=','true')->whereRaw('data_prazo < current_date')
            ->whereRaw("fase_grupos_id IN (select id from fase_grupos where campeonato_fases_id  IN (select id FROM campeonato_fases where campeonatos_id  = $idCampeonato))")
            ->update(array('liberada'=>'false'));

        $rodadas = DB::table('partidas')
            ->selectRaw('DISTINCT rodada as numero, data_prazo, liberada')
            ->where('fase_grupos_id', '=', $grupo->id)
            ->where('adiamento','=', 'false')
            ->groupBy('rodada', 'data_prazo', 'liberada')
            ->orderBy('rodada')
            ->get();
        return $rodadas;
    }

    public function salvarPrazoRodada($rodada, $data_prazo) {
        $faseAtual = $this->faseAtual();
        $partidas = Partida::whereIn('fase_grupos_id', FaseGrupo::where('campeonato_fases_id', '=', $faseAtual->id)->get(array('id')))
            ->where('rodada','=',$rodada)
            ->get();
        $data_prazo = strstr($data_prazo, " (", true);
        $data_prazo = Carbon::parse($data_prazo);
        foreach ($partidas as $partida) {
            $partida->data_prazo = $data_prazo;
            $partida->save();
        }
    }

    public function salvarLiberarRodada($rodada, $liberada) {
        $faseAtual = $this->faseAtual();
        $partidas = Partida::whereIn('fase_grupos_id', FaseGrupo::where('campeonato_fases_id', '=', $faseAtual->id)->get(array('id')))
            ->where('rodada','=',$rodada)
            ->get();
        foreach ($partidas as $partida) {
            $partida->liberada = $liberada;
            $partida->save();
        }
    }

    static public function precisaPlacarExtra($partida, $usuarios) {
        $fase = $partida->grupo()->fase();
        $campeonato = $fase->campeonato();
        $detalhesDoCampeonato = $campeonato->detalhes();
        $partidas = $partida->grupo()->partidas();

        $tem_placar_extra = true;

        foreach($usuarios as $user) {
            if(empty($user['placar_extra'])) {
                $tem_placar_extra = false;
                break;
            }
        }
        if($tem_placar_extra) {
            return false;
        }

        if($fase->matamata) {
            $partidas_sem_placar = DB::table('partidas')->where('fase_grupos_id','=', $partida->fase_grupos_id)->where('id','<>',$partida->id)->whereNull('data_placar')->count();
            if($partidas_sem_placar === 0) {
                if ($detalhesDoCampeonato->ida_volta) {
                    $u1A = $partidas->first()->usuarios()->first();
                    $u2A = $partidas->first()->usuarios()->last();
                    $u1B = $partidas->last()->usuarios()->last();
                    $u2B = $partidas->last()->usuarios()->first();
                    $placar1 = $u1A->placar + $u1B->placar;
                    $placar2 = $u2A->placar + $u2B->placar;
                    if($placar1 === $placar2) {
                        if ($detalhesDoCampeonato->fora_casa && ($u1A->placar != $u2B->placar)) {
                            return false;
                        } else {
                            return true;
                        }
                    }
                } else {
                    $u1 = $partidas->first()->usuarios()->first();
                    $u2 = $partidas->first()->usuarios()->last();

                    if(isset($detalhesDoCampeonato->numero_rounds) && $detalhesDoCampeonato->numero_rounds > 1) {
                        $placarUsuario1 = 0;
                        $placarUsuario2 = 0;
                        foreach ($partidas as $partida) {
                            if($partida->placarUsuario($u1->users_id) > $partida->placarUsuario($u2->users_id)) {
                                $placarUsuario1++;
                            } else if($partida->placarUsuario($u1->users_id) < $partida->placarUsuario($u2->users_id)) {
                                $placarUsuario2++;
                            }
                        }
                    } else {
                        $placarUsuario1 = $u1->placar;
                        $placarUsuario2 = $u2->placar;
                    }
                    if ($placarUsuario1 == $placarUsuario2) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function apagarFases() {
        CampeonatoFase::where('campeonatos_id','=',$this->id)->delete();
        return null;
    }

    public function verificaUsuarioInscrito($idUsuario) {
        $usuariosCadastrados = $this->belongsToMany('User', 'campeonato_usuarios', 'campeonatos_id', 'users_id')->withPivot(array('id', 'time_id'))->getResults();
        foreach ($usuariosCadastrados as $usuario) {
            if ($usuario->id == $idUsuario) {
                return true;
            }
        }
        return false;
    }
}
