<?php

class UsuarioPartida extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'partidas_id' => 'required'
	);
}
