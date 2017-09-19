<?php

class UsuarioGrupo extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'fase_grupos_id' => 'required'
	);
}
