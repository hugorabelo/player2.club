<?php

class TipoCompetidor extends Eloquent {
	protected $guarded = array();

	protected $table = 'tipo_competidor';

	public static $rules = array(
		'descricao' => 'required'
	);
}
