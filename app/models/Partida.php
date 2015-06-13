<?php

class Partida extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'fase_grupos_id' => 'required',
		'rodada' => 'required'
	);

    public function salvarPlacar($id_usuario) {

    }

    public function confirmarPlacar($id_usuario) {
        // Computar Pontuação e posição do usuario_partidas
    }

    public function placarSalvo() {

    }

    public function placarConfirmado() {

    }
}
