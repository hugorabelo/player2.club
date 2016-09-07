<?php

class Jogo extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'descricao' => 'required'
	);

	public function modeloCampeonato() {
		$modelo = $this->belongsTo('ModeloCampeonato', 'modelo_campeonato_id')->getResults();
        return $modelo;
	}

	public function tiposCampeonato() {
		$modelo_campeonato = $this->modeloCampeonato();
        if(isset($modelo_campeonato)) {
			$tiposCampeonato = $modelo_campeonato->hasMany('CampeonatoTipo', 'modelo_campeonato_id')->getResults()->sortBy('descricao');
			return $tiposCampeonato->values()->all();
        }
        return null;
	}

}
