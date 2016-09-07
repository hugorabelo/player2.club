<?php

class CampeonatoUsuario extends Eloquent
{
	protected $guarded = array();

	protected $table = 'campeonato_usuarios';

	public static $rules = array(
		'users_id' => 'required',
		'campeonatos_id' => 'required'
	);

	public function usuario()	{
		return User::find($this->users_id);
	}

	public function campeonato() {
		return Campeonato::find($this->campeonatos_id);
	}

}
