<?php

class Jogo extends Eloquent {
	protected $guarded = array();

	public static $rules = array(
		'descricao' => 'required'
	);

	public function modeloCampeonato() {
		$modelo = $this->belongsTo('ModeloCampeonato', 'modelo_campeonato_id')->getResults();
        return $modelo;
	}

	public function tiposCampeonato() {
		$modelo_campeonato = $this->modeloCampeonato();
        if(isset($modelo_campeonato)) {
			$tiposCampeonato = $modelo_campeonato->hasMany('CampeonatoTipo', 'modelo_campeonato_id')->getResults()->sortBy('descricao');
			return $tiposCampeonato->values()->all();
        }
        return null;
	}

	public function seguidores() {
        return $this->belongsToMany('User', 'seguidor_jogo', 'jogos_id', 'users_id');
    }

	public function produtora() {
		$produtora = Produtora::find($this->produtora_id);
		return $produtora;
	}

	public function genero() {
		$genero = Genero::find($this->genero_id);
		return $genero;
	}

	public function getAtividades($offset, $quantidade) {
		$campeonatos = Campeonato::where('jogos_id','=',$this->id)->get(array('id'));
		$fases = CampeonatoFase::whereIn('campeonatos_id', $campeonatos)->get(array('id'));
		$grupos = FaseGrupo::whereIn('campeonato_fases_id',$fases)->get(array('id'));
		$partidasDisputadas = Partida::whereIn('fase_grupos_id', $grupos)->get(array('id'));

		$postsDestinatarios = Post::where('jogos_id','=', $this->id)->get(array('id'));
		$atividades = Atividade::WhereIn('post_id', $postsDestinatarios)->orWhereIn('partidas_id',$partidasDisputadas)->take($quantidade)->skip($offset)->orderBy('created_at', 'desc')->get();
		return $atividades;
	}

	public function plataformas() {
        $jogos = $this->belongsToMany('Plataforma', 'jogos_plataforma', 'jogos_id', 'plataformas_id')->withPivot(array())->orderBy('descricao')->getResults();
        return $jogos->values()->all();
    }

}
