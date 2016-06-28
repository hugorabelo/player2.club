<?php

/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 27/06/16
 * Time: 22:19
 */
class CampeonatoPontos extends Campeonato
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
        $this->criaFases();

    }

    public function criaFases() {
        /*
        * 1. Criar primeira fase
        * 2. Criar critérios de classificação para o campeonato
        * 3. Criar regras de pontuação para a fase, baseado em vitoria, empate e derrota
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

        /**  3. Criar regras de pontuação para a fase **/
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
    }

}
