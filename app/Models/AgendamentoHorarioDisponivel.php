<?php

/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 2/27/19
 * Time: 12:31 PM
 */
class AgendamentoHorarioDisponivel extends Eloquent {
    protected $guarded = array();

    protected $table = 'agendamento_horario_disponivel';

    public static $rules = array(
        'hora_inicio' => 'required',
        'hora_fim' => 'required',
        'data' => 'required',
        'campeonato_usuarios_id' => 'required'
    );
}
