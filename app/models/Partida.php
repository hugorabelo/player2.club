<?php

use Illuminate\Database\Eloquent\Collection;

class Partida extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'fase_grupos_id' => 'required',
		'rodada' => 'required'
	);

    public function grupo() {
        return FaseGrupo::find($this->fase_grupos_id);
    }

    /*
     * return
     * 0 - tudo ok
     * 1 - empate não é permitido na fase
     * 2 - pontuação não cadastrada
     */
    public function salvarPlacar($partida) {
        $partidaSelecionada = Partida::find($partida['id']);
        $pontuacoes = FaseGrupo::find($partida['fase_grupos_id'])->fase()->pontuacoes();
        $usuarios = Collection::make($partida['usuarios']);
        $usuarios->sortByDesc('placar');
        $empate_computado = false;
        if($usuarios->count() == 2) {
            if($usuarios->first()['placar'] == $usuarios->last()['placar']) {
                if($partidaSelecionada->grupo()->fase()->permite_empate) {
                    foreach ($usuarios as $usuario) {
                        $usuarioPartida = UsuarioPartida::find($usuario['id']);
                        $usuarioPartida->posicao = 0;
                        $usuarioPartida->pontuacao = $pontuacoes[0];
                        $usuarioPartida->placar = $usuario['placar'];
                        $usuarioPartida->save();
                    }
                    $empate_computado = true;
                } else {
                    //TODO retornar mensagem de erro
                }
            }
        }
        if(!$empate_computado) {
            $i = 1;
            foreach ($usuarios as $usuario) {
                $usuarioPartida = UsuarioPartida::find($usuario['id']);
                $usuarioPartida->posicao = $i;
                $usuarioPartida->pontuacao = $pontuacoes[$i];
                $usuarioPartida->placar = $usuario['placar'];
                $usuarioPartida->save();
                $i++;
            }
        }
        $partidaSelecionada->usuario_placar = $partida['usuarioLogado'];
        $partidaSelecionada->data_placar = date('Y-m-d H:i:s');;
        $partidaSelecionada->save();
    }

    public function confirmarPlacar($id_usuario) {
        // Computar Pontuação e posição do usuario_partidas
    }

    public function placarSalvo() {

    }

    public function placarConfirmado() {

    }

    public function usuarios() {
        $usuarios = $this->hasMany('UsuarioPartida', 'partidas_id')->getResults()->sortBy('id');
        $usuarios->values()->all();
        return $usuarios;
    }
}
