<?php

/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 06/04/17
 * Time: 16:49
 */
class Notificacao extends Eloquent {

    protected $guarded = array();

    protected $table = 'notificacao';

    public static $rules = array(
        'id_destinatario' => 'required',
        'evento_notificacao_id' => 'required'
    );


}
