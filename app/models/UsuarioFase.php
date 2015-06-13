<?php

class UsuarioFase extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'users_id' => 'required',
		'campeonato_fases_id' => 'required'
	);
}
