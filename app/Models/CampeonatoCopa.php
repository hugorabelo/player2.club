<?php

/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 07/06/16
 * Time: 21:11
 */

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CampeonatoCopa extends Campeonato implements CampeonatoEspecificavel
{

    function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

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

//      Adicionar Administrador do Campeonato
        $dadosAdministrador = array("users_id"=>$dadosCampeonato["criador"], "campeonatos_id"=>$this->campeonato->id);
        CampeonatoAdmin::create($dadosAdministrador);

//      2. Criar fases
//      3. Cria regras de pontuação para cada fase
//      4. Cria grupos da primeira fase
        $this->criaFases();
        return $this->campeonato;
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
        $dataInicio = substr($this->detalhesFases['data_inicio'], 0, 16);
        $dataFim = substr($this->detalhesFases['data_fim'], 0, 16);
        $dataInicio = strstr($dataInicio, " (", true);
        $primeiraFase['data_inicio'] = Carbon::parse($dataInicio);
        $dataFim = strstr($dataFim, " (", true);
        $primeiraFase['data_fim'] = Carbon::parse($dataFim);
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
            if (json_decode($this->detalhesCampeonato['ida_volta'])) {
                $faseCriada['permite_empate'] = true;
            } else {
                $faseCriada['permite_empate'] = false;
            }
            $dataInicio = substr($this->detalhesFases['data_inicio'], 0, 16);
            $dataFim = substr($this->detalhesFases['data_fim'], 0, 16);
            $dataInicio = strstr($dataInicio, " (", true);
            $faseCriada['data_inicio'] = Carbon::parse($dataInicio);
            $dataFim = strstr($dataFim, " (", true);
            $faseCriada['data_fim'] = Carbon::parse($dataFim);
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
            if ($usuario['placar'] === null) {
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
                    if(!empty($usuario['placar_extra'])) {
                        $usuarioPartida->placar_extra = $usuario['placar_extra'];
                    }
                    $usuarioPartida->save();
                }
                $empate_computado = true;
            } else {
                if(Campeonato::precisaPlacarExtra($partida, $usuarios)) {
                    foreach ($usuarios as $usuario) {
                        $usuarioPartida = UsuarioPartida::find($usuario['id']);
                        $usuarioPartida->posicao = null;
                        $usuarioPartida->pontuacao = null;
                        $usuarioPartida->placar = null;
                        $usuarioPartida->placar_extra = null;
                        $usuarioPartida->save();
                    }
                    return 'messages.precisa_placar_extra';
                }
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
                if(!empty($usuario['placar_extra'])) {
                    $usuarioPartida->placar_extra = $usuario['placar_extra'];
                }
                $usuarioPartida->save();
                $i++;
            }
        }
        if(Campeonato::precisaPlacarExtra($partida, $usuarios)) {
            foreach ($usuarios as $usuario) {
                $usuarioPartida = UsuarioPartida::find($usuario['id']);
                $usuarioPartida->posicao = null;
                $usuarioPartida->pontuacao = null;
                $usuarioPartida->placar = null;
                $usuarioPartida->placar_extra = null;
                $usuarioPartida->save();
            }
            return 'messages.precisa_placar_extra';
        }
        $partida->usuario_placar = Auth::getUser()->id;
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
