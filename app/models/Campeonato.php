<?php

class Campeonato extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'descricao' => 'required',
		'regras' => 'required',
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

	public function maximoUsuarios() {
		/*
		 * Alterar para quantidade maxima de usuarios da fase inicial
		 */
        $quantidade_maxima = 0;
        foreach($this->faseInicial() as $fase) {
            $quantidade_maxima = $fase->quantidade_usuarios;
        }
		return $quantidade_maxima;
	}

	public function fases() {
		return $this->hasMany('CampeonatoFase', 'campeonatos_id')->getResults();
	}

	public function faseInicial() {
		return $this->hasMany('CampeonatoFase', 'campeonatos_id')->where('inicial', '=', 'true')->get()->first();
	}

	public function faseFinal() {
		return $this->hasMany('CampeonatoFase', 'campeonatos_id')->where('final', '=', 'true')->get()->first();
	}

	public function validarNumeroDeCompetidores($detalhes) {
		if($detalhes['quantidade_competidores'] > 0) {
			return '';
		}
		return 'messages.numero_competidores_maior_zero';
	}

	public function detalhes() {
		return $this->hasMany('CampeonatoDetalhe', 'campeonatos_id')->getResults();
	}

	public function salvarPlacar($partida) {
		$nomeClasse = $this->campeonatoTipo()->nome_classe_modelo;
		return $nomeClasse::salvarPlacarPartida($partida);
	}

	/**
	 * Atualizar as datas de cada fase, de acordo com a data de encerramento da fase atual, atualizar para as próximas fases
	 *
	 * @param fase Fase atual com as informações
	 * @param novaData Data final da fase atual
	 *
	 */
	protected function atualizarDatasFases($fase, $novaData) {

	}
}
