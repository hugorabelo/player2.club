<?php

class Campeonato extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'descricao' => 'required',
		'detalhes' => 'required',
		'jogos_id' => 'required',
		'campeonato_tipos_id' => 'required',
		'plataformas_id' => 'required'
	);

	public function jogo() {
		return Jogo::find($this->jogos_id);
	}

	public function campeonatoTipo() {
		return CampeonatoTipo::find($this->campeonato_tipos_id);
	}

	public function plataforma() {
		return Plataforma::find($this->plataformas_id);
	}

    public function administradores() {
		return $this->belongsToMany('User', 'campeonato_admins', 'campeonatos_id', 'users_id')->getResults();
    }

	public function usuariosInscritos() {
		return $this->belongsToMany('User', 'campeonato_usuarios', 'campeonatos_id', 'users_id')->getResults();
	}

	public function fases() {
		//return Fase
	}
}
