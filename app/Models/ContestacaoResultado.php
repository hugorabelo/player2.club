<?php

class ContestacaoResultado extends Eloquent
{
    protected $guarded = array();

    protected $table = 'contestacao_resultado';

    public static $rules = array(
        'comentarios' => 'required',
        'usuario_partidas_id' => 'required',
        'partidas_id' => 'required'
    );

}
