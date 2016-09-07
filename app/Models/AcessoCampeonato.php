<?php

class AcessoCampeonato extends Eloquent {
	protected $guarded = array();

	protected $table = 'acesso_campeonato';

	public static $rules = array(
		'descricao' => 'required'
	);
}
