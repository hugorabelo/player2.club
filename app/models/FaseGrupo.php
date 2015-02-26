<?php

class FaseGrupo extends Eloquent {
    protected $guarded = array();

    public static $rules = array(
        'descricao' => 'required',
        'quantidade_usuarios' => 'required',
        'campeonato_fases_id' => 'required'
    );


}
