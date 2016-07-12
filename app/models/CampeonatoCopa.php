<?php

/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 07/06/16
 * Time: 21:11
 */

use Illuminate\Database\Eloquent\Collection;

class CampeonatoCopa extends Campeonato implements CampeonatoEspecificavel
{

    public function salvar($input)
    {

        $dadosCampeonato = array_except($input, array('criteriosClassificacaoSelecionados', 'detalhes', 'pontuacao', 'fases'));
        $detalhes = $input['detalhes'];
        $this->criteriosClassificacao = $input['criteriosClassificacaoSelecionados'];
        $this->pontuacao = $input['pontuacao'];
        $this->detalhesFases = $input['fases'];

//      1. Salvar campeonato e detalhes
        $this->campeonato = Campeonato::create($dadosCampeonato);
        $detalhes['campeonatos_id'] = $this->campeonato->id;
        $this->detalhesCampeonato = CampeonatoDetalhes::create($detalhes);

//      2. Criar fases
//      3. Cria regras de pontuação para cada fase
//      4. Cria grupos da primeira fase
        $this->criaFases();

    }

    private function criaFases()
    {

        /*
         * 1. Criar primeira fase
         * 2. Criar critérios de classificação para o campeonato
         * 3. Criar grupos da primeira fase
         * 4. Criar regras de pontuação para a fase, baseado em vitoria, empate e derrota
         * 5. Cadastrar cada fases seguintes (mata-mata) com o número respectivo de competidores
         * 6. Criar grupos para cada uma das fases, com apenas 2 competidores por grupo
         */

        /**  1. Criar primeira fase **/
        $primeiraFase = array();
        $primeiraFase['descricao'] = 'messages.primeira_fase';
        $primeiraFase['permite_empate'] = true;
        $primeiraFase['data_inicio'] = $this->detalhesFases['data_inicio'];
        $primeiraFase['data_fim'] = $this->detalhesFases['data_fim'];
        $primeiraFase['campeonatos_id'] = $this->campeonato->id;
        $primeiraFase['quantidade_usuarios'] = $this->detalhesCampeonato->quantidade_competidores;
        $primeiraFase['inicial'] = true;
        $faseAtual = CampeonatoFase::create($primeiraFase);

        /** 2. Criar critérios de classificacao */
        $ordem = 1;
        foreach ($this->criteriosClassificacao as $criterio) {
            $novoCriterio = array();
            $novoCriterio['campeonatos_id'] = $this->campeonato->id;
            $novoCriterio['criterios_classificacao_id'] = $criterio['id'];
            $novoCriterio['ordem'] = $ordem;
            CampeonatoCriterio::create($novoCriterio);
            $ordem++;
        }


        /**  3. Criar grupos da primeira fase **/
        $quantidadeUsuarios = $this->detalhesCampeonato->quantidade_competidores / $this->detalhesCampeonato->quantidade_grupos;

        for ($i = 1; $i <= $this->detalhesCampeonato->quantidade_grupos; $i++) {
            $grupo = array('campeonato_fases_id' => $faseAtual->id, 'quantidade_usuarios' => $quantidadeUsuarios);
            $grupo['descricao'] = $i;
            FaseGrupo::create($grupo);
        }

        /**  4. Criar regras de pontuação para a fase **/
        // Vitória
        $pontuacao = array();
        $pontuacao['posicao'] = 1;
        $pontuacao['qtde_pontos'] = $this->pontuacao['vitoria'];
        $pontuacao['campeonato_fases_id'] = $faseAtual->id;
        PontuacaoRegra::create($pontuacao);

        // Derrota
        $pontuacao = array();
        $pontuacao['posicao'] = 2;
        $pontuacao['qtde_pontos'] = $this->pontuacao['derrota'];
        $pontuacao['campeonato_fases_id'] = $faseAtual->id;
        PontuacaoRegra::create($pontuacao);

        // Empate
        $pontuacao = array();
        $pontuacao['posicao'] = 0;
        $pontuacao['qtde_pontos'] = $this->pontuacao['empate'];
        $pontuacao['campeonato_fases_id'] = $faseAtual->id;
        PontuacaoRegra::create($pontuacao);

        /** 5/6. Cadastrar Fases seguintes (mata mata) e criar grupos para cada fase **/
        $qtdeParticipantesFase = $this->detalhesCampeonato->classificados_proxima_fase;
        while ($qtdeParticipantesFase >= 2) {
            $faseCriada = array();
            $faseCriada['descricao'] = 'messages.matamata' . $qtdeParticipantesFase;
            if ($this->detalhesFases['ida_volta']) {
                $faseCriada['permite_empate'] = true;
            } else {
                $faseCriada['permite_empate'] = false;
            }
            $faseCriada['data_inicio'] = $this->detalhesFases['data_inicio'];
            $faseCriada['data_fim'] = $this->detalhesFases['data_fim'];
            $faseCriada['campeonatos_id'] = $this->campeonato->id;
            $faseCriada['fase_anterior_id'] = $faseAtual->id;
            $faseCriada['quantidade_usuarios'] = $qtdeParticipantesFase;
            $faseCriada['matamata'] = true;
            if ($qtdeParticipantesFase == 2) {
                $faseCriada['final'] = true;
            }
            $faseAtual = CampeonatoFase::create($faseCriada);

            $gruposDaFase = $qtdeParticipantesFase / 2;
            for ($j = 1; $j <= $gruposDaFase; $j++) {
                $grupo = array('campeonato_fases_id' => $faseAtual->id, 'quantidade_usuarios' => 2);
                $grupo['descricao'] = $j;
                FaseGrupo::create($grupo);
            }

            $qtdeParticipantesFase = $qtdeParticipantesFase / 2;
        }
    }

    public function iniciaFase($fase)
    {
        /*
         * Objeto Fase deve conter os seguintes atributos:
         * - id : ID da fase
         * - data_encerramento: Data de encerramento da fase a ser iniciada (Para cada fase seguinte, atualizar as datas de início, baseadas nesta)
         * - tipo_sorteio mata-mata: Se for uma fase de mata mata, definir o tipo de sorteio (melhor geral x pior geral | melhor grupo x pior grupo | aleatória)
         */
        /*
         * 1. Verifica se a fase anterior está fechada, caso contrário fechar automaticamente (avisar ao usuário)
         * 2. Inscrever usuários classificados da fase anterior
         * 3. Sortear Grupos e Jogos
         * 4. Habilitar inserção de resultados
         */

        /** 2. Inscrever usuários classificados da fase anterior */
        $faseAtual = CampeonatoFase::find($fase['id']);
        $campeonato = Campeonato::find($faseAtual->campeonatos_id);

        if ($faseAtual == $campeonato->faseInicial()) {
            $usuariosDaFase = $campeonato->usuariosInscritos();
        } else {
            $faseAnterior = CampeonatoFase::find($faseAtual->fase_anterior_id);
            if ($faseAnterior != null) {
                $gruposAnterior = $faseAnterior->grupos();

                $usuariosDaFase = new Collection();

                foreach ($gruposAnterior as $grupo) {
                    $usuariosDoGrupo = $grupo->usuariosClassificados();
                    foreach ($usuariosDoGrupo as $posicao => $usuarioInserido) {
                        $usuariosDaFase->put($posicao, $usuarioInserido);
                    }
                }
            }
        }
        foreach ($usuariosDaFase as $posicao => $usuario) {
            UsuarioFase::create(['users_id' => $usuario->id, 'campeonato_fases_id' => $faseAtual->id, 'posicao_fase_anterior' => $posicao]);
        }
        $gruposDaFase = $faseAtual->grupos();

        // Sortear Grupos e Jogos
        /** 3. Sortear Grupos e Jogos */
        $this->sorteioGrupos($gruposDaFase, $usuariosDaFase, $fase);

        $idaVolta = $campeonato->detalhes()->ida_volta;
        foreach ($faseAtual->grupos() as $grupo) {
            if ($idaVolta) {
                $this->sorteioJogosUmContraUm($grupo, 2);
            } else {
                $this->sorteioJogosUmContraUm($grupo, 1);
            }
        }

        return Response::json($usuariosDaFase);

    }

    public function encerraFase($fase)
    {

    }

    static public function salvarPlacarPartida($dados)
    {
        $partida = Partida::find($dados['id']);
        $fase = $partida->grupo()->fase();
        $permite_empate = $fase->permite_empate;
        $pontuacoes = $fase->pontuacoes();
        $usuarios = Collection::make($dados['usuarios']);
        $usuarios->sortByDesc('placar');
        $empate_computado = false;

        // Verificar se todos os usuários estão com o placar inserido
        foreach ($usuarios as $usuario) {
            if ($usuario['placar'] == null) {
                return 'messages.placares_invalidos';
            }
        }

        // Verificar se a pontuação está toda cadastrada corretamente
        if (!$fase->matamata) {
            for ($i = $permite_empate ? 0 : 1; $i < $usuarios->count(); $i++) {
                if (!isset($pontuacoes[$i])) {
                    return 'messages.pontuacao_nao_cadastrada';
                }
            }
        }

        if ($usuarios->first()['placar'] == $usuarios->last()['placar']) {
            if ($permite_empate) {
                foreach ($usuarios as $usuario) {
                    $usuarioPartida = UsuarioPartida::find($usuario['id']);
                    $usuarioPartida->posicao = 0;
                    if (!$fase->matamata) {
                        $usuarioPartida->pontuacao = $pontuacoes[0];
                    }
                    $usuarioPartida->placar = $usuario['placar'];
                    $usuarioPartida->save();
                }
                $empate_computado = true;
            } else {
                return 'messages.empate_nao_permitido';
            }
        }

        if (!$empate_computado) {
            $i = 1;
            foreach ($usuarios as $usuario) {
                $usuarioPartida = UsuarioPartida::find($usuario['id']);
                $usuarioPartida->posicao = $i;
                if (!$fase->matamata) {
                    $usuarioPartida->pontuacao = $pontuacoes[$i];
                }
                $usuarioPartida->placar = $usuario['placar'];
                $usuarioPartida->save();
                $i++;
            }
        }
        $partida->usuario_placar = $dados['usuarioLogado'];
        $partida->data_placar = date('Y-m-d H:i:s');
        $partida->save();
        return '';
    }

    public function validarNumeroDeCompetidores($detalhes)
    {
        if (fmod($detalhes['quantidade_competidores'], $detalhes['quantidade_grupos']) != 0) {
            return 'messages.competidores_nao_mutiplo_grupos';
        }
        if (fmod($detalhes['classificados_proxima_fase'], $detalhes['quantidade_grupos']) != 0) {
            return 'messages.classificados_nao_mutiplo_grupos';
        }
        if (!filter_var(log($detalhes['classificados_proxima_fase'], 2), FILTER_VALIDATE_INT)) {
            return 'messages.classificados_nao_potencia_dois';
        }
        return "";
    }

    private function sorteioGrupos($grupos, $usuarios, $dadosFase)
    {
        /*
         * Objeto Fase deve conter os seguintes atributos:
         * - id : ID da fase
         * - data_encerramento: Data de encerramento da fase a ser iniciada (Para cada fase seguinte, atualizar as datas de início, baseadas nesta)
         * - tipo_sorteio mata-mata: Se for uma fase de mata mata, definir o tipo de sorteio (melhor geral x pior geral | melhor grupo x pior grupo | aleatório)
         *      No futuro, o tipo de sorteio vai poder ser manual
         */
        //TODO Verificar se o sorteio é influenciado pela posição do usuário na fase anterior
        //  (caso a fase anterior seja a de grupos, senão, serão pegos sempre os grupos adjacentes)
        $usuariosInseridos = array();
        $fase = CampeonatoFase::find($dadosFase['id']);
        if ($fase->matamata && $dadosFase['tipo_sorteio'] != 'aleatorio') {
            $maximaPosicao = 0;
            foreach ($usuarios as $posicao=>$user) {
                if ($posicao > $maximaPosicao) {
                    $maximaPosicao = $posicao;
                }
            }
            for ($i = 1; $i<=$maximaPosicao; $i++) {
                $lista{$i} = new Collection();
            }
            $maximoGrupoAnterior = 0;
            foreach ($usuarios as $posicao=>$usuario) {
                $grupoAnteriorDoUsuario = $this->getGrupoAnteriorUsuario($usuario->id, $fase);
                if ($grupoAnteriorDoUsuario > $maximoGrupoAnterior) {
                    $maximoGrupoAnterior = $grupoAnteriorDoUsuario;
                }
                $usuario->grupoAnterior = $grupoAnteriorDoUsuario;
                $lista{$posicao}->put($grupoAnteriorDoUsuario, $usuario->id);
            }

            if ($dadosFase['tipo_sorteio'] == 'geral') {
                // Precisa-se ordernar os usuários dentro de cada lista pelos critérios de classificação
                for ($i = 1; $i<=$maximaPosicao; $i++) {
                    $this->ordenaUsuariosCriteriosClassificacao($lista{$i}, $fase);
                }

                $indiceGrupoAtual = 0;
                $indicePosicaoInicial = 1;
                $indicePosicaoFinal = $maximaPosicao;

                while($indiceGrupoAtual < $grupos->count()) {
                    $grupo = $grupos->get($indiceGrupoAtual);

                    $usuario1 = $lista{$indicePosicaoInicial}->shift();
                    $usuario2 = $lista{$indicePosicaoFinal}->pop();
                    UsuarioGrupo::create(['users_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                    UsuarioGrupo::create(['users_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);

                    //TODO Como inverter a ordem de pegar os elementos (shift ou pop), pensar numa regra

                    $indicePosicaoInicial++;
                    $indicePosicaoFinal--;
                    if($indicePosicaoInicial > $indicePosicaoFinal) {
                        $indicePosicaoInicial = 1;
                        $indicePosicaoFinal = $maximaPosicao;
                    }
                    $indiceGrupoAtual++;
                }

                // Está errado, só vai percorrer uma vez a cada posição
                // ir para o papel e escrever as combinações, talvez seja melhor tentar fazer as coisas mais
                // manualmente mesmo, de forma mais detalhada e menos automatizada
                while ($indicePosicaoInicial <= $indicePosicaoFinal) {
                    $grupo = $grupos->get($indiceGrupoAtual);
                    $usuario1 = $lista{$indicePosicaoInicial}->random(1);
                    while (in_array($usuario1, $usuariosInseridos)) {
                        $usuario1 = $lista{$indicePosicaoInicial}->random(1);
                    }
                    UsuarioGrupo::create(['users_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                    array_push($usuariosInseridos, $usuario1);
                    $usuario2 = $lista{$indicePosicaoFinal}->random(1);
                    while (in_array($usuario2, $usuariosInseridos)) {
                        $usuario2 = $lista{$indicePosicaoFinal}->random(1);
                    }
                    UsuarioGrupo::create(['users_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);
                    array_push($usuariosInseridos, $usuario2);
                    $indiceGrupoAtual++;
                    $indicePosicaoInicial++;
                    $indicePosicaoFinal--;
                }
            } else if ($dadosFase['tipo_sorteio'] == 'grupo') {
                // Precisa ordenar os usuários dentro de cada lista pelo ordem dos grupos
                $indiceGrupoAtual = 0; // Grupos Atuais
                $indicePosicaoInicial = 1; // Primeira Posição
                $indicePosicaoFinal = $maximaPosicao; // Última Posição
                $u = 0; // Primeiro Grupo Anterior
                $v = $maximoGrupoAnterior; // Último Grupo Anterior
                while ($indicePosicaoInicial <= $indicePosicaoFinal) {
                    $grupo = $grupos->get($indiceGrupoAtual);
                    $usuario1 = $lista{$indicePosicaoInicial}->get(1);
                    while (in_array($usuario1, $usuariosInseridos)) {
                        $usuario1 = $lista{$indicePosicaoInicial}->random(1);
                    }
                    UsuarioGrupo::create(['users_id' => $usuario1->id, 'fase_grupos_id' => $grupo->id]);
                    array_push($usuariosInseridos, $usuario1);
                    $usuario2 = $lista{$indicePosicaoFinal}->random(1);
                    while (in_array($usuario2, $usuariosInseridos)) {
                        $usuario2 = $lista{$indicePosicaoFinal}->random(1);
                    }
                    UsuarioGrupo::create(['users_id' => $usuario2->id, 'fase_grupos_id' => $grupo->id]);
                    array_push($usuariosInseridos, $usuario2);
                    $indiceGrupoAtual++;
                    $indicePosicaoInicial++;
                    $indicePosicaoFinal--;
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

    private function sorteioJogosUmContraUm($grupo, $turnos)
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

    private function getGrupoAnteriorUsuario($id_usuario, $fase)
    {
        $faseAnterior = $fase->faseAnterior();
        if($faseAnterior != null) {
            $gruposDaFase = $faseAnterior->grupos();
            $gruposDoUsuario = UsuarioGrupo::where('users_id', '=', $id_usuario)->get(array('fase_grupos_id'));
            foreach ($gruposDaFase as $grupoFase) {
                if($gruposDoUsuario->search($grupoFase->id)) {
                    return $grupoFase;
                }
            }
        }
        return null;
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
        if($usuario1->{$valor} === $usuario2->{$valor}) {
            if($criteriosClassificacao->count() == 0) {
                return 0;
            }
            return $this->comparaUsuariosCriteriosClassificacao($usuario1, $usuario2, $criteriosClassificacao);
        }
        return $usuario1->{$valor} > $usuario2->{$valor} ? -1 : 1;
    }

}
