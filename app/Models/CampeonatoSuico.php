<?php

/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 27/06/16
 * Time: 22:19
 */

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CampeonatoSuico extends Campeonato implements CampeonatoEspecificavel
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
        $primeiraFase['permite_empate'] = false;
        $dataInicio = substr($this->detalhesFases['data_inicio'], 0, 16);
        $dataFim = substr($this->detalhesFases['data_fim'], 0, 16);
        $dataInicio = strstr($dataInicio, " (", true);
        $primeiraFase['data_inicio'] = Carbon::parse($dataInicio);
        $dataFim = strstr($dataFim, " (", true);
        $primeiraFase['data_fim'] = Carbon::parse($dataFim);
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
            if($usuario['placar'] === null) {
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
        $partida->usuario_placar = Auth::getUser()->id;
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

    public function iniciaFase($dadosFase, $faseAtual, $campeonato)
    {
        if($faseAtual->aberta) {
            return true;
        }

        UsuarioFase::where('campeonato_fases_id','=',$faseAtual->id)->delete();
        $usuariosDaFase = $campeonato->usuariosInscritos();
        foreach ($usuariosDaFase as $posicao => $usuario) {
            if($this->tipo_competidor == 'equipe') {
                UsuarioFase::create(['equipe_id' => $usuario->id, 'campeonato_fases_id' => $faseAtual->id]);
            } else {
                UsuarioFase::create(['users_id' => $usuario->id, 'campeonato_fases_id' => $faseAtual->id]);
            }
        }

        $gruposDaFase = $faseAtual->grupos();

        $this->sorteioGrupos($gruposDaFase, $usuariosDaFase, $dadosFase);

        foreach ($gruposDaFase as $grupo) {
            $this->gerarRodada($grupo, 1);
            break;
        }

        $campeonato->atualizarDatasFases($faseAtual, $dadosFase['data_fim']);

        $faseAtual->aberta = true;
        $faseAtual->update();

        //TODO Enviar notificação para todos os membros das equipes (ou pelo menos para os administradores)
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

    public function gerarRodada($grupo, $numero_rodada) {
        $usuarios = $grupo->usuariosComClassificacao();
        if($usuarios->count() % 2 == 1) {
            return false;
        }
        $n = $usuarios->count();
        $m = $n / 2; // Quantidade de jogos por rodada

        $colecao = collect($usuarios);

        $colecaoChave = $colecao->groupBy('vitorias');

        for($i = $numero_rodada-1; $i >= 0; $i--) {
            echo $colecaoChave->get($i)->first()->nome;
        }

        return null;


        // Ordenar participantes pelos critérios
        // Pegar sempre o par, 1x2, 3x4, 5x6, etc
        $indiceUsuario = 0;
        for($i=0;$i<$m;$i++) {
            $partida = Partida::create(['fase_grupos_id' => $grupo->id, 'rodada' => $numero_rodada]);
            $usuario1 = $usuarios->get($indiceUsuario)->id;
            UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuario1]);
            $indiceUsuario++;
            $usuario2 = $usuarios->get($indiceUsuario)->id;
            if($this->verificaJogoExistente($usuario1, $usuario2, $grupo->id)) {
                Log::warning("$usuario1 - $usuario2");
            }
            UsuarioPartida::create(['partidas_id' => $partida->id, 'users_id' => $usuario2]);
            $indiceUsuario++;
        }


        /*
         * 1. Dividir grupos por números de vitórias
         *    1.1 Caso a quantidade seja ímpar, pegar o primeiro competidor do grupo seguinte
         *    1.2 Caso todos os participantes desse grupo ímpar já tiverem jogado contra o competidor do grupo seguinte, deve-se pegar o próximo
         * 2. Dentro do grupo, pegar o primeiro usuário e sortear alguém do seu grupo
         *    2.1 Verificar se esses adversários já se enfrentaram, caso sim, realizar novo sorteio
         *      2.1.1 Caso a dupla que já jogou seja a última dupla do grupo, refazer todos os sorteios, em ordem inversa
         * 3. Salvar os emparceiramentos do item anterior em arrays
         * 4. Varrer os arrays com os emparceiramentos e criar as partidas
         */
    }

    public function verificaJogoExistente($idUser1, $idUser2, $idGrupo) {
        $retorno = DB::table('usuario_partidas')->whereRaw("partidas_id IN (select partidas_id FROM usuario_partidas where users_id = $idUser1 and partidas_id IN ".
            "(select id from partidas where fase_grupos_id = $idGrupo)) and users_id = $idUser2")->count();
        return $retorno;
    }

}
