<?php

/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 27/06/16
 * Time: 22:22
 */
class CampeonatoMataMata extends Campeonato
{

    public function salvar($input) {

        Log::info($input);

        return null;

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

    }

    public function criaFases() {
        $letras = array('#', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $faseAtual = new CampeonatoFase();
        $qtdeParticipantesFase = $this->detalhesCampeonato->quantidade_competidores;
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

}
