<?php

/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 2/27/19
 * Time: 12:31 PM
 */
class AgendamentoPartidaNaoRealizada extends Eloquent {
    protected $guarded = array();

    protected $table = 'agendamento_partida_nao_realizada';

    public static $rules = array(
        'motivo' => 'required',
        'users_id' => 'required',
        'partidas_id' => 'required'
    );
}
