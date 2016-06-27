<?php

class Plataforma extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'descricao' => 'required'
	);

	public function jogos() {
		$jogos = $this->belongsToMany('Jogo', 'jogos_plataforma', 'plataformas_id', 'jogos_id')->withPivot(array())->orderBy('descricao')->getResults();
		return $jogos->values()->all();
	}
}
