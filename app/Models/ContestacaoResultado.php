<?php

class ContestacaoResultado extends Eloquent
{
    protected $guarded = array();

    protected $table = 'contestacao_resultado';

    public static $rules = array(
        'comentarios' => 'required',
        'users_id' => 'required',
        'partidas_id' => 'required'
    );

}
