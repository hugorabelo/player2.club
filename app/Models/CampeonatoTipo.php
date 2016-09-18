<?php

class CampeonatoTipo extends Eloquent {
	protected $guarded = array();

	protected $table = 'campeonato_tipos';

	public static $rules = array(
		'descricao' => 'required',
		'arquivo_detalhes' => 'required',
        'nome_classe_modelo' => 'required',
        'modelo_campeonato_id' => 'required'
	);
}
