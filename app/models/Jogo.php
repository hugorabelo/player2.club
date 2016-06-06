<?php

class Jogo extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'descricao' => 'required'
	);

	public function modeloCampeonato() {
		return $this->hasOne('ModeloCampeonato', 'modelo_campeonato_id')->getResults();
	}

	public function tiposCampeonato() {
		$modelo_campeonato = $this->modeloCampeonato();
		return $modelo_campeonato->belongsTo('CampeonatoTipo', 'modelo_campeonato_id')->getResults();
	}

}
