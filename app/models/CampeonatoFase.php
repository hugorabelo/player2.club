<?php

class CampeonatoFase extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'descricao' => 'required',
		'permite_empate' => 'required',
		'data_inicio' => 'required',
		'data_fim' => 'required',
		'campeonatos_id' => 'required',
		'quantidade_usuarios' => 'required'
	);

	public function getDates()
	{
		return array();
	}

    public function grupos() {
        return $this->hasMany('FaseGrupo', 'campeonato_fases_id')->getResults();
    }

    public function usuarios() {
        return $this->belongsToMany('User', 'usuario_fases', 'campeonato_fases_id', 'users_id')->getResults();
    }

    public function faseAnterior() {
        return $this->find($this->fase_anterior_id);
    }

	public function pontuacoes() {
		$pontuacoes = $this->hasMany('PontuacaoRegra', 'campeonato_fases_id')->getResults()->sortBy('posicao');
		$tabela_pontuacao = array();
		foreach($pontuacoes as $pontuacao) {
			$tabela_pontuacao[$pontuacao->posicao] = $pontuacao->qtde_pontos;
		}
		return $tabela_pontuacao;
	}
}
