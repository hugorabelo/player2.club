<?php

class Plataforma extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'descricao' => 'required'
	);
}
