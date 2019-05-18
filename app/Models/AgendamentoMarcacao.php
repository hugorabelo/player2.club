<?php

/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 2/27/19
 * Time: 12:31 PM
 */
class AgendamentoMarcacao extends Eloquent {
    protected $guarded = array();

    protected $table = 'agendamento_marcacao';

    public static $rules = array(
        'status' => 'required',
        'horario_agendamento' => 'required',
        'partidas_id' => 'required',
        'usuario_host' => 'required',
        'usuario_convidado' => 'required'
    );
}
