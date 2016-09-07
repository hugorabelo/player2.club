<?php

class UsuarioFase extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'users_id' => 'required',
		'campeonato_fases_id' => 'required'
	);

    public static function encontraUsuarioFase($idUsuario, $idFase) {
        $usuarioFase = UsuarioFase::where('users_id', '=', $idUsuario)->where('campeonato_fases_id', '=', $idFase)->get()->first();
        return $usuarioFase;
    }
}
