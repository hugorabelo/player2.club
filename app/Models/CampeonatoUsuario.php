<?php

class CampeonatoUsuario extends Eloquent
{
	protected $guarded = array();

	protected $table = 'campeonato_usuarios';

	public static $rules = array(
		'campeonatos_id' => 'required'
	);

	public function usuario()	{
		return User::find($this->users_id);
	}

	public function campeonato() {
		return Campeonato::find($this->campeonatos_id);
	}

	public function getID($idUsuario, $idCampeonato) {
        $campeonatoUsuario = CampeonatoUsuario::where('users_id', '=', $idUsuario)->where('campeonatos_id', '=', $idCampeonato)->first();
        return $campeonatoUsuario;
    }

}
