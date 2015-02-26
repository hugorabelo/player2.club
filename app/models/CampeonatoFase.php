<?php

class CampeonatoFase extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'descricao' => 'required',
		'permite_empate' => 'required',
		'data_inicio' => 'required',
		'data_fim' => 'required',
		'campeonatos_id' => 'required',
		'quantidade_usuarios' => 'required'
	);

	public function getDates()
	{
		return array();
	}
}
