<?php

class Permissao extends Eloquent {
	protected $table = 'permissao';

	protected $guarded = array();

	public static $rules = array(
		'menu_id' => 'required',
		'usuario_tipos_id' => 'required'
	);

}
