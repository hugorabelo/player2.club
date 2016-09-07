<?php

class ModeloCampeonato extends Eloquent {

    protected $guarded = array();

    protected $table = 'modelo_campeonato';

    public function criteriosClassificacao() {
        $criterios = $this->hasMany('CriterioClassificacao', 'modelo_campeonato_id')->getResults()->sortBy('descricao');
        return $criterios->values()->all();
    }

}
