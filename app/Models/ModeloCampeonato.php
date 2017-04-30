<?php

class ModeloCampeonato extends Eloquent {

    protected $guarded = array();

    protected $table = 'modelo_campeonato';

    public static $rules = array(
        'descricao' => 'required',
        'maximo_jogadores_partida' => 'required'
    );

    public function criteriosClassificacao() {
        $criterios = $this->hasMany('CriterioClassificacao', 'modelo_campeonato_id')->getResults()->sortBy('descricao');
        return $criterios->values()->all();
    }

}
