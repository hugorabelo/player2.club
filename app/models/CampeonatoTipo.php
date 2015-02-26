<?php

class CampeonatoTipo extends Eloquent {
	protected $guarded = array();

	protected $table = 'campeonato_tipos';

	public static $rules = array(
		'descricao' => 'required',
		'maximo_jogadores_partida' => 'required'
	);
}
