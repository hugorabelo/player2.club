<?php
/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 4/21/17
 * Time: 11:25 PM
 */

class Time extends Eloquent
{
    protected $table = 'time';

    protected $guarded = array();

    public static $rules = array(
        'descricao' => 'required',
        'modelo_campeonato_id' => 'required'
    );
}
