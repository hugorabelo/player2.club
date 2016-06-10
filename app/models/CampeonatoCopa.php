<?php

/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 07/06/16
 * Time: 21:11
 */
class CampeonatoCopa extends Campeonato
{

    public function salvar($campeonato) {

//      1. Salvar detalhes do campeonato
        $this->detalhesCampeonato = CampeonatoDetalhes::create($campeonato->detalhes);

//      2. Criar fases
//      3. Cria regras de pontuação para cada fase
        $this->criaFases();

//      4. Cria grupos da primeira fase
    }

    public function criaFases() {

    }

    public function iniciaFase() {

    }

    public function encerraFase() {

    }

    public function salvarPlacar() {

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
