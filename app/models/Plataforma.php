<?php

class Plataforma extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'descricao' => 'required'
	);

	public function jogos() {
		return $this->belongsToMany('Jogo', 'jogos_plataforma', 'plataformas_id', 'jogos_id')->getResults();
	}
}
