<?php

class Plataforma extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'descricao' => 'required'
	);

	public function jogos($apenasCampeonato) {
	    if(isset($apenasCampeonato)) {
            $jogos = $this->belongsToMany('Jogo', 'jogos_plataforma', 'plataformas_id', 'jogos_id')->where('permite_campeonato','=',true)->withPivot(array())->orderBy('descricao')->getResults();
        } else {
            $jogos = $this->belongsToMany('Jogo', 'jogos_plataforma', 'plataformas_id', 'jogos_id')->withPivot(array())->orderBy('descricao')->getResults();
        }
		return $jogos->values()->all();
	}
}
