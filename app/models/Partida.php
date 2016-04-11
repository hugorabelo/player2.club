<?php

use Illuminate\Database\Eloquent\Collection;

class Partida extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'fase_grupos_id' => 'required',
		'rodada' => 'required'
	);

    public function salvarPlacar($partida) {
        $pontuacoes = FaseGrupo::find($partida['fase_grupos_id'])->fase()->pontuacoes();
        $usuarios = Collection::make($partida['usuarios']);
        $usuarios->sortByDesc('placar');
        $iterator = $usuarios->getIterator();
        /*
         * Partida com 2 usuários
         * Partida com mais de 2 usuários
         * Partida com empate -> Verificar se permite empate
         */
        for($i=0;$i<$usuarios->count();$i++) {
            $usuarios->pontuacao = $pontuacoes[$i];
        }
        Log::info($usuarios);
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
