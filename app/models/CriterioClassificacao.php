<?php

/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 26/06/16
 * Time: 18:24
 */
class CriterioClassificacao extends Eloquent
{
    protected $guarded = array();

    protected $table = 'criterios_classificacao';

    public static $rules = array(
        'descricao' => 'required'
    );
}
