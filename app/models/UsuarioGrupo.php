<?php

class UsuarioGrupo extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'users_id' => 'required',
		'fase_grupos_id' => 'required'
	);
}
