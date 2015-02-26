<?php

class UsuarioTipo extends Eloquent {
	protected $guarded = array();

	protected $table = 'usuario_tipos';

	public static $rules = array(
		'descricao' => 'required',
		'prioridade' => 'required'
	);

}
