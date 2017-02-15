<?php

/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 27/06/16
 * Time: 22:22
 */

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CampeonatoMataMata extends Campeonato implements CampeonatoEspecificavel
{

    public function salvar($input) {

        $dadosCampeonato = array_except($input, array('criteriosClassificacaoSelecionados', 'detalhes', 'pontuacao', 'fases'));
        $detalhes = $input['detalhes'];
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
         * 1. Cadastrar cada uma das fases com o número respectivo de competidores
         * 2. Criar grupos para cada uma das fases, com apenas 2 competidores por grupo
         */

        $faseAtual = new CampeonatoFase();
        $qtdeParticipantesFase = $this->detalhesCampeonato->quantidade_competidores;
        while ($qtdeParticipantesFase >= 2) {
            $faseCriada = array();
            $faseCriada['descricao'] = 'messages.matamata'.$qtdeParticipantesFase;
            if($this->detalhesCampeonato['ida_volta']) {
                $faseCriada['permite_empate'] = true;
            } else {
                $faseCriada['permite_empate'] = false;
            }
            $dataInicio = substr($this->detalhesFases['data_inicio'], 0, 16);
            $dataFim = substr($this->detalhesFases['data_fim'], 0, 16);
            $faseCriada['data_inicio'] = Carbon::parse($dataInicio);
            $faseCriada['data_fim'] = Carbon::parse($dataFim);
            $faseCriada['campeonatos_id'] = $this->campeonato->id;
            $faseCriada['fase_anterior_id'] = $faseAtual->id;
            $faseCriada['quantidade_usuarios'] = $qtdeParticipantesFase;
            $faseCriada['matamata'] = true;
            if($faseAtual->id == null) {
                $faseCriada['inicial'] = true;
            }
            if ($qtdeParticipantesFase == 2) {
                $faseCriada['final'] = true;
            }
            $faseAtual = CampeonatoFase::create($faseCriada);

            $gruposDaFase = $qtdeParticipantesFase/2;
            for($j = 1; $j <= $gruposDaFase; $j++) {
                $grupo = array('campeonato_fases_id'=>$faseAtual->id, 'quantidade_usuarios'=>2);
                $grupo['descricao'] = $j;
                FaseGrupo::create($grupo);
            }

            $qtdeParticipantesFase = $qtdeParticipantesFase/2;
        }
    }

    public function validarNumeroDeCompetidores($detalhes) {
        if(!filter_var(log($detalhes['quantidade_competidores'], 2), FILTER_VALIDATE_INT)) {
            return 'messages.classificados_nao_potencia_dois';
        }
        return "";
    }

    static public function salvarPlacarPartida($dados)
    {
        $partida = Partida::find($dados['id']);
        $fase = $partida->grupo()->fase();
        $permite_empate = $fase->permite_empate;
        $usuarios = Collection::make($dados['usuarios']);
        $usuarios = $usuarios->sortByDesc('placar');
        $empate_computado = false;

        // Verificar se todos os usuários estão com o placar inserido
        foreach ($usuarios as $usuario) {
            if($usuario['placar'] == null) {
                return 'messages.placares_invalidos';
            }
        }

        if($usuarios->first()['placar'] == $usuarios->last()['placar']) {
            if($permite_empate) {
                foreach ($usuarios as $usuario) {
                    $usuarioPartida = UsuarioPartida::find($usuario['id']);
                    $usuarioPartida->posicao = 0;
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
        return null;
    }

}
