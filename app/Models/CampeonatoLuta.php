<?php

/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 27/06/16
 * Time: 22:22
 */

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CampeonatoLuta extends Campeonato implements CampeonatoEspecificavel
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

    public function criaFases($input = null) {
        /*
         * 1. Cadastrar cada uma das fases com o número respectivo de competidores
         * 2. Criar grupos para cada uma das fases, com apenas 2 competidores por grupo
         */

        $faseAtual = new CampeonatoFase();
        $qtdeParticipantesFase = $this->detalhesCampeonato->quantidade_competidores;
        while ($qtdeParticipantesFase >= 2) {
            $faseCriada = array();
            $faseCriada['descricao'] = 'messages.matamata'.$qtdeParticipantesFase;
            $faseCriada['permite_empate'] = false;

            $dataInicio = substr($this->detalhesFases['data_inicio'], 0, 16);
            $dataFim = substr($this->detalhesFases['data_fim'], 0, 16);
            $dataInicio = strstr($dataInicio, " (", true);
            $faseCriada['data_inicio'] = Carbon::parse($dataInicio);
            $dataFim = strstr($dataFim, " (", true);
            $faseCriada['data_fim'] = Carbon::parse($dataFim);
            $faseCriada['campeonatos_id'] = $this->detalhesCampeonato->campeonatos_id;
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
        $usuarios = Collection::make($dados['usuarios']);
        $usuarios = $usuarios->sortByDesc('placar');

        // Verificar se todos os usuários estão com o placar inserido
        foreach ($usuarios as $usuario) {
            if($usuario['placar'] === null) {
                return 'messages.placares_invalidos';
            }
        }

        if($usuarios->first()['placar'] == $usuarios->last()['placar']) {
            return 'messages.empate_nao_permitido';
        }

        $i = 1;
        foreach ($usuarios as $usuario) {
            $usuarioPartida = UsuarioPartida::find($usuario['id']);
            $usuarioPartida->posicao = $i;
            $usuarioPartida->placar = $usuario['placar'];
            $usuarioPartida->save();
            $i++;
        }

        $partida->usuario_placar = Auth::user()->id;
        $partida->data_placar = date('Y-m-d H:i:s');
        $partida->save();
        return '';
    }

    public function pontuacoes($idFase = null) {
        return null;
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
            $this->sorteioJogosUmContraUm($grupo, $detalhesCampeonato->numero_rounds);
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

}
