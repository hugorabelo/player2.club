<?php

class Jogo extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'descricao' => 'required'
	);

}
