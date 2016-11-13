<?php

/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 27/06/16
 * Time: 22:19
 */

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CampeonatoPontos extends Campeonato implements CampeonatoEspecificavel
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
        return $this->campeonato;
    }

    public function criaFases() {
        /*
        * 1. Criar primeira fase
        * 2. Criar critérios de classificação para o campeonato
        * 3. Criar um grupo para a fase principal
        * 4. Criar regras de pontuação para a fase, baseado em vitoria, empate e derrota
        */

        /**  1. Criar primeira fase **/
        $primeiraFase = array();
        $primeiraFase['descricao'] = 'messages.primeira_fase';
        $primeiraFase['permite_empate'] = true;
        $primeiraFase['data_inicio'] = Carbon::parse($this->detalhesFases['data_inicio']);
        $primeiraFase['data_fim'] = Carbon::parse($this->detalhesFases['data_fim']);
        $primeiraFase['campeonatos_id'] = $this->campeonato->id;
        $primeiraFase['quantidade_usuarios'] = $this->detalhesCampeonato->quantidade_competidores;
        $primeiraFase['inicial'] = true;
        $primeiraFase['final'] = true;
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

        /** 3. Criar um grupo para a fase principal **/
        $grupo = array('campeonato_fases_id'=>$faseAtual->id, 'quantidade_usuarios'=>$this->detalhesCampeonato->quantidade_competidores, 'descricao'=> 'A');
        FaseGrupo::create($grupo);

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
    }

    static public function salvarPlacarPartida($dados)
    {
        $partida = Partida::find($dados['id']);
        $fase = $partida->grupo()->fase();
        $permite_empate = $fase->permite_empate;
        $pontuacoes = $fase->pontuacoes();
        $usuarios = Collection::make($dados['usuarios']);
        $usuarios = $usuarios->sortByDesc('placar');
        $empate_computado = false;

        // Verificar se todos os usuários estão com o placar inserido
        foreach ($usuarios as $usuario) {
            if($usuario['placar'] == null) {
                return 'messages.placares_invalidos';
            }
        }

        // Verificar se a pontuação está toda cadastrada corretamente
        for($i = $permite_empate ? 0 : 1;$i<$usuarios->count();$i++) {
            if(!isset($pontuacoes[$i])) {
                return 'messages.pontuacao_nao_cadastrada';
            }
        }

        if($usuarios->first()['placar'] == $usuarios->last()['placar']) {
            if($permite_empate) {
                foreach ($usuarios as $usuario) {
                    $usuarioPartida = UsuarioPartida::find($usuario['id']);
                    $usuarioPartida->posicao = 0;
                    if(!$fase->matamata) {
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

        if(!$empate_computado) {
            $i = 1;
            foreach ($usuarios as $usuario) {
                $usuarioPartida = UsuarioPartida::find($usuario['id']);
                $usuarioPartida->posicao = $i;
                if(!$fase->matamata) {
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

    public function pontuacoes($idFase = null) {
        if(isset($idFase)) {
            $fase = CampeonatoFase::find($idFase);
        } else {
            $fase = $this->faseInicial();
        }
        $pontuacoes = $fase->hasMany('PontuacaoRegra', 'campeonato_fases_id')->getResults();
        $pontuacaoRetorno = new stdClass();
        foreach ($pontuacoes as $pontuacao) {
            switch ($pontuacao->posicao) {
                case 1:
                    $pontuacaoRetorno->vitoria = $pontuacao->qtde_pontos;
                    break;
                case 2:
                    $pontuacaoRetorno->derrota = $pontuacao->qtde_pontos;
                    break;
                case 0:
                    $pontuacaoRetorno->empate = $pontuacao->qtde_pontos;
                    break;
            }
        }
        return $pontuacaoRetorno;
    }

}
