<?php

class UsuarioFase extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'campeonato_fases_id' => 'required'
	);

    public static function encontraUsuarioFase($idUsuario, $idFase, $tipo_competidor = null, $anonimo = null) {
        if($tipo_competidor == 'equipe') {
            $usuarioFase = UsuarioFase::where('equipe_id', '=', $idUsuario)->where('campeonato_fases_id', '=', $idFase)->get()->first();
        } else {
            if($anonimo) {
                $usuarioFase = UsuarioFase::where('anonimo_id', '=', $idUsuario)->where('campeonato_fases_id', '=', $idFase)->get()->first();
            } else {
                $usuarioFase = UsuarioFase::where('users_id', '=', $idUsuario)->where('campeonato_fases_id', '=', $idFase)->get()->first();
            }
        }
        return $usuarioFase;
    }
}
