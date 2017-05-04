<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class Campeonato extends Eloquent {
	protected $guarded = array();

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
		return $this->belongsToMany('User', 'campeonato_usuarios', 'campeonatos_id', 'users_id')->withPivot(array('id', 'time_id'))->getResults();
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
		return $this->hasMany('CampeonatoFase', 'campeonatos_id')->getResults();
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
        if(isset($partidaBD->data_placar) && !$contestada) {
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
	 * Atualizar as datas de cada fase, de acordo com a data de encerramento da fase atual, atualizar para as próximas fases
	 *
	 * @param fase Fase atual com as informações
	 * @param novaData Data final da fase atual
	 *
	 */
	protected function atualizarDatasFases($fase, $novaData) {
		$data = Carbon::parse($novaData);
		$fase->data_fim = $data;
		$fase->update();

        $outraData = DB::table('campeonato_fases')->selectRaw("data_fim + '1 day' as nova_data")->where('id','=',$fase->id)->first();
        $outraData = $outraData->nova_data;
        $outraData = Carbon::parse($outraData);

		$proximaFase = $fase;
		while($proximaFase = $proximaFase->proximaFase()) {
			$proximaFase->data_inicio = $outraData;
			$proximaFase->update();
		}
	}

	/**
	 * Recuperar a lista de critérios de classificação escolhidos para este campeonato
	 *
	 * @return Collection Lista Ordenada de Critérios
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
        /*
         * Objeto Fase deve conter os seguintes atributos:
         * - id : ID da fase
         * - data_encerramento: Data de encerramento da fase a ser iniciada (Para cada fase seguinte, atualizar as datas de início, baseadas nesta)
         * - tipo_sorteio_matamata: Se for uma fase de mata mata, definir o tipo de sorteio (melhor geral x pior geral | melhor grupo x pior grupo | aleatória)
         */
        /*
         * 1. Verifica se a fase anterior está fechada, caso contrário fechar automaticamente (avisar ao usuário)
         * 2. Inscrever usuários classificados da fase anterior
         * 3. Sortear Grupos e Jogos
         * 4. Habilitar inserção de resultados
         */

        /** 2. Inscrever usuários classificados da fase anterior */
        if ($faseAtual == $campeonato->faseInicial()) {
            $usuariosDaFase = $campeonato->usuariosInscritos();
            foreach ($usuariosDaFase as $posicao => $usuario) {
                UsuarioFase::create(['users_id' => $usuario->id, 'campeonato_fases_id' => $faseAtual->id]);
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

        $evento = NotificacaoEvento::where('valor','=','fase_iniciada')->first();
        if(isset($evento)) {
            $idEvento = $evento->id;
        }
        foreach ($usuariosDaFase as $usuario) {
            $notificacao = new Notificacao();
            $notificacao->id_destinatario = $usuario->id;
            $notificacao->evento_notificacao_id = $idEvento;
            $notificacao->item_id = $faseAtual->id;
            $notificacao->save();
        }

        return $usuariosDaFase;
    }

    public function encerraFase($dadosFase)
    {
        $fase = CampeonatoFase::find($dadosFase['id']);
        $proximaFase = $fase->proximaFase();
        foreach ($fase->grupos() as $grupo) {
            // contabilizar jogos sem resultado (0 pontos para todos os participantes)
            foreach ($grupo->partidas() as $partida) {
                if(!isset($partida->data_placar)) {
                    foreach ($partida->usuarios(false) as $usuarioPartida) {
                        $dadosUsuarioUpdate = array(
                            'posicao' => -1,
                            'pontuacao' => 0,
                            'placar' => 0
                        );
                        $usuarioPartida->update($dadosUsuarioUpdate);
                    }
                    $partida->data_placar = date('Y-m-d H:i:s');
                    $partida->data_confirmacao = date('Y-m-d H:i:s');
                    $partida->save();
                } else if (!isset($partida->data_confirmacao)) {
                    $partida->data_confirmacao = date('Y-m-d H:i:s');
                    $partida->save();
                }
            }

            // contabilizar pontuação e quantidade de classificados (por grupo) - INSCREVER USUÁRIOS CLASSIFICADOS NA FASE SEGUINTE
            if(isset($proximaFase)) {
                $posicaoUsuario = 1;
                foreach ($grupo->usuariosClassificados() as $usuario) {
                    $usuarioFase = new UsuarioFase();
                    $usuarioFase->campeonato_fases_id = $proximaFase->id;
                    $usuarioFase->users_id = $usuario->id;
                    $usuarioFase->posicao_fase_anterior = $posicaoUsuario;
                    $usuarioFase->save();
                    $posicaoUsuario++;
                }
            }
        }

        // Desabilitar inserção de resultados
        $fase->aberta = false;
        $fase->encerrada = true;
        $fase->update();

        $evento = NotificacaoEvento::where('valor','=','fase_encerrada')->first();
        if(isset($evento)) {
            $idEvento = $evento->id;
        }
        $usuariosDaFase = $fase->usuarios();
        foreach ($usuariosDaFase as $usuario) {
            $notificacao = new Notificacao();
            $notificacao->id_destinatario = $usuario->id;
            $notificacao->evento_notificacao_id = $idEvento;
            $notificacao->item_id = $fase->id;
            $notificacao->save();
        }

        /*
         * Cronograma de inserção de resultados para uma partida de campeonato
         * - Usuário tem até a data final da fase para inserir o resultado (Caso não exista resultado, o jogo será definido como sem resultado, onde ambos os participantes ficam com pontos de último colocado)
         * - Outro Usuário tem até 24 horas depois da hora de inserção do resultado para confirmar o mesmo (Caso não seja confirmado o resultado por outro usuário, o placar inserido será dado como definitivo).
         * -
         */
    }

    protected function sorteioJogosUmContraUm($grupo, $turnos)
    {
        $usuarios = $grupo->usuarios();
        $n = $usuarios->count();
        $m = $n / 2;
        $numero_rodadas_por_turno = ($n - 1);
        $numero_rodada = 1;
        for ($t = 0; $t < $turnos; $t++) {
            for ($i = 0; $i < $numero_rodadas_por_turno; $i++) {
                for ($j = 0; $j < $m; $j++) {
                    $partida = Partida::create(['fase_grupos_id' => $grupo->id, 'rodada' => $numero_rodada]);
                    if ($t % 2 == 1) {
                        if ($j % 2 == 1 || $i % 2 == 1 && $j == 0) {
                            UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($n - $j - 1)->id]);
                            UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($j)->id]);
                        } else {
                            UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($j)->id]);
                            UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($n - $j - 1)->id]);
                        }
                    } else {
                        if ($j % 2 == 1 || $i % 2 == 1 && $j == 0) {
                            UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($j)->id]);
                            UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($n - $j - 1)->id]);
                        } else {
                            UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($n - $j - 1)->id]);
                            UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuarios->get($j)->id]);
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
         * - data_encerramento: Data de encerramento da fase a ser iniciada (Para cada fase seguinte, atualizar as datas de início, baseadas nesta)
         * - tipo_sorteio_matamata: Se for uma fase de mata mata, definir o tipo de sorteio (melhor geral x pior geral | melhor grupo x pior grupo | aleatório)
         *      No futuro, o tipo de sorteio vai poder ser manual
         */

        $usuariosInseridos = array();
        $fase = CampeonatoFase::find($dadosFase['id']);
        if($fase->faseAnterior() != null && $fase->faseAnterior()->matamata && $dadosFase['tipo_sorteio_matamata'] != 'aleatorio') {
            foreach ($usuarios as $usuario) {
                $grupoAnteriorDoUsuario = $this->getGrupoAnteriorUsuario($usuario->id, $fase);
                $usuario->grupoAnterior = $grupoAnteriorDoUsuario;
            }
            $usuarios = $usuarios->sortBy('grupoAnterior');
            foreach($grupos as $grupo) {
                $usuario1 = $usuarios->shift();
                $usuario2 = $usuarios->shift();
                UsuarioGrupo::create(['users_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                UsuarioGrupo::create(['users_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);
            }
        } else {
            if ($fase->matamata && $dadosFase['tipo_sorteio_matamata'] != 'aleatorio') {
                $maximaPosicao = 0;
                foreach ($usuarios as $user) {
                    $posicao = UsuarioFase::encontraUsuarioFase($user->id, $fase->id)->posicao_fase_anterior;
                    if ($posicao > $maximaPosicao) {
                        $maximaPosicao = $posicao;
                    }
                }
                for ($i = 1; $i<=$maximaPosicao; $i++) {
                    $lista[$i] = new Collection();
                }

                foreach ($usuarios as $usuario) {
                    $posicao = UsuarioFase::encontraUsuarioFase($usuario->id, $fase->id)->posicao_fase_anterior;
                    $grupoAnteriorDoUsuario = $this->getGrupoAnteriorUsuario($usuario->id, $fase);
                    $usuario->grupoAnterior = $grupoAnteriorDoUsuario;
                    $lista[$posicao]->push($usuario);
                }

                // TODO Testar essa regra para mais grupos (Ex. 8 grupos)
                if ($dadosFase['tipo_sorteio_matamata'] == 'geral') {
                    // Precisa-se ordernar os usuários dentro de cada lista pelos critérios de classificação
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
                        UsuarioGrupo::create(['users_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                        UsuarioGrupo::create(['users_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);

                        $indicePosicaoInicial++;
                        $indicePosicaoFinal--;
                        if($indicePosicaoInicial > $indicePosicaoFinal) {
                            $indicePosicaoInicial = 1;
                            $indicePosicaoFinal = $maximaPosicao;
                        }
                        $indiceGrupoAtual++;
                        // Regra para inverter posições dos jogos para ficar 1,2,2,2...,1 (um do inicio, dois do fim, dois do início, ...)
                        if($invertePosicao > 2) {
                            $invertePosicao = -1;
                        } else if($invertePosicao < -2) {
                            $invertePosicao = 1;
                        }
                    }

                } else if ($dadosFase['tipo_sorteio_matamata'] == 'grupo') {
                    // Precisa ordenar os usuários dentro de cada lista pelo ordem dos grupos
                    for ($i = 1; $i<=$maximaPosicao; $i++) {
                        $lista[$i] = $lista[$i]->sortBy('grupoAnterior');
                    }

                    $indiceGrupoAtual = 0;
                    $indicePosicaoInicial = 1;
                    $indicePosicaoFinal = $maximaPosicao;
                    $invertePosicao = false;
                    $indiceGrupoInicial = 0;
                    $indiceGrupoFinal = $grupos->count()-1;

                    while($indiceGrupoAtual < $grupos->count()) {
                        $grupo = $grupos->get($indiceGrupoAtual);

                        if($invertePosicao) {
                            // Pegar mandante do final da lista
                            $usuario1 = $lista[$indicePosicaoInicial]->get($indiceGrupoFinal);
                            if($indiceGrupoFinal % 2 == 0) {
                                $usuario2 = $lista[$indicePosicaoFinal]->get($indiceGrupoFinal + 1);
                            } else {
                                $usuario2 = $lista[$indicePosicaoFinal]->get($indiceGrupoFinal - 1);
                            }
                        } else {
                            // Pegar mandante do início da lista
                            $usuario1 = $lista[$indicePosicaoInicial]->get($indiceGrupoInicial);
                            if($indiceGrupoInicial % 2 == 0) {
                                $usuario2 = $lista[$indicePosicaoFinal]->get($indiceGrupoFinal);
                            } else {
                                $usuario2 = $lista[$indicePosicaoFinal]->get($indiceGrupoFinal);
                            }
                        }

                        UsuarioGrupo::create(['users_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                        UsuarioGrupo::create(['users_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);

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
                //TODO: Definir se existem potes
                if(isset($dadosFase['potes'])) {
                    $potes = array_except($dadosFase['potes'], 'Principal');
                    foreach ($grupos as $grupo) {
                        foreach ($potes as $key=>$pote) {
                            $quantidade_usuarios_pote = count($pote) / $grupo->quantidade_usuarios;
                            for($i = 0; $i < $quantidade_usuarios_pote; $i++) {
                                $indice = rand(0, count($pote) - 1);
                                $usuario = User::find($pote[$indice]['id']);
                                while (in_array($usuario, $usuariosInseridos)) {
                                    $indice = rand(0, count($pote) - 1);
                                    $usuario = User::find($pote[$indice]['id']);
                                }
                                UsuarioGrupo::create(['users_id' => $usuario->id, 'fase_grupos_id' => $grupo->id]);
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
                            UsuarioGrupo::create(['users_id' => $usuario->id, 'fase_grupos_id' => $grupo->id]);
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
            $gruposDoUsuario = UsuarioGrupo::where('users_id', '=', $id_usuario)->get(array('fase_grupos_id'));
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
         * 1. Inscrições abertas
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
}
