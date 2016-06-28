<?php

/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 07/06/16
 * Time: 21:11
 */

use Illuminate\Database\Eloquent\Collection;

class CampeonatoCopa extends Campeonato
{

    public function salvar($input) {

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
        $this->criaFasesGrupos();

    }

    public function criaFasesGrupos() {

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
        $letras = array('#', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        for($i = 1; $i <= $this->detalhesCampeonato->quantidade_grupos; $i++) {
            $grupo = array('campeonato_fases_id'=>$faseAtual->id, 'quantidade_usuarios'=>$quantidadeUsuarios);
            $grupo['descricao'] = $letras[$i];
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
            $faseCriada['descricao'] = 'messages.matamata'.$qtdeParticipantesFase;
            $faseCriada['permite_empate'] = false;
            $faseCriada['data_inicio'] = $this->detalhesFases['data_inicio'];
            $faseCriada['data_fim'] = $this->detalhesFases['data_fim'];
            $faseCriada['campeonatos_id'] = $this->campeonato->id;
            $faseCriada['fase_anterior_id'] = $faseAtual->id;
            $faseCriada['quantidade_usuarios'] = $qtdeParticipantesFase;
            if ($qtdeParticipantesFase == 2) {
                $faseCriada['final'] = true;
            }
            $faseAtual = CampeonatoFase::create($faseCriada);

            $gruposDaFase = $qtdeParticipantesFase/2;
            for($j = 1; $j <= $gruposDaFase; $j++) {
                $grupo = array('campeonato_fases_id'=>$faseAtual->id, 'quantidade_usuarios'=>2);
                $grupo['descricao'] = $letras[$j];
                FaseGrupo::create($grupo);
            }

            $qtdeParticipantesFase = $qtdeParticipantesFase/2;
        }
    }

    public function iniciaFase() {

    }

    public function encerraFase() {

    }

    public function salvarPlacar() {

    }

    /*
     * @return
     * 0: Se tudo está validado
     * 1: Se a quantidade de competidores não for múltipla da quantidade de grupos
     * 2: Se a quantidade de classificados para a próxima fase não form múltipla da quantidade de grupos
     * 3: Se a quantidade de classificados para a próxima fase não for potência de 2
     */
    public function validarNumeroDeCompetidores($detalhes) {
        //quantidade_competidores
        //quantidade_grupos
        //classificados_proxima_fase
        if(fmod($detalhes['quantidade_competidores'], $detalhes['quantidade_grupos']) != 0) {
            return 'messages.competidores_nao_mutiplo_grupos';
        }
        if(fmod($detalhes['classificados_proxima_fase'], $detalhes['quantidade_grupos']) != 0) {
            return 'messages.classificados_nao_mutiplo_grupos';
        }
        if(!filter_var(log($detalhes['classificados_proxima_fase'], 2), FILTER_VALIDATE_INT)) {
            return 'messages.classificados_nao_potencia_dois';
        }
        return "";
    }

    public function detalhes() {
        return $this->hasMany('CampeonatoDetalhe', 'campeonatos_id')->getResults();
    }

    private function sorteioGrupos($grupos, $usuarios) {
        $usuariosInseridos = array();
        foreach($grupos as $grupo) {
            for($i = 0; $i < $grupo->quantidade_usuarios; $i++) {
                $usuario = $usuarios->random(1);
                while(in_array($usuario, $usuariosInseridos)) {
                    $usuario = $usuarios->random(1);
                }
                //UsuarioGrupo::create(['users_id'=> $usuario->id,'fase_grupos_id' => $grupo->id]);
                array_push($usuariosInseridos, $usuario);
            }
        }
    }

    private function sorteioJogosUmContraUm($grupo, $turnos) {
        $usuarios = $grupo->usuarios();
        $n = $usuarios->count();
        $m = $n / 2;
        $numero_rodadas_por_turno = ($n - 1);
        $numero_rodada = 1;
        for($t = 0; $t < $turnos; $t++) {
            for($i = 0; $i < $numero_rodadas_por_turno; $i++) {
                for($j = 0; $j < $m; $j++) {
                    $partida = Partida::create(['fase_grupos_id'=>$grupo->id, 'rodada'=>$numero_rodada]);
                    if($t % 2 == 1) {
                        if($j % 2 == 1 || $i % 2 == 1 && $j == 0) {
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($n - $j - 1)->id]);
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($j)->id]);
                        } else {
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($j)->id]);
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($n - $j - 1)->id]);
                        }
                    } else {
                        if($j % 2 == 1 || $i % 2 == 1 && $j == 0) {
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($j)->id]);
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($n - $j - 1)->id]);
                        } else {
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($n - $j - 1)->id]);
                            UsuarioPartida::create(['partidas_id'=>$partida->id, 'users_id'=>$usuarios->get($j)->id]);
                        }
                    }
                }
                $numero_rodada++;
                $usuarios = $this->sorteioReordena($usuarios);
            }
        }
    }

    private function sorteioReordena($colecao) {
        $novaColecao = new Collection();
        $novaColecao->add($colecao->shift());
        $novaColecao->add($colecao->pop());
        foreach($colecao as $elemento) {
            $novaColecao->add($elemento);
        }
        return $novaColecao;
    }

}
