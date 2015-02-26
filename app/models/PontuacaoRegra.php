<?php

class PontuacaoRegra extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'posicao' => 'required',
		'qtde_pontos' => 'required',
		'campeonato_fases_id' => 'required'
	);
}
