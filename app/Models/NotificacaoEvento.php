<?php

/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 06/04/17
 * Time: 16:50
 */
class NotificacaoEvento extends Eloquent {

    protected $guarded = array();

    protected $table = 'notificacao_evento';

    public static $rules = array(
        'descricao' => 'required',
        'mensagem' => 'required'
    );

}
