<?php

/**
 * Created by Hugo Rabelo
 * Date: 08/01/2020
 * Time: 05:43
 */
class FinanceiroDetalhesPremiacao extends Eloquent
{

    protected $table = 'financeiro_detalhes_premiacao';

    protected $guarded = array();

    protected $fillable = ['valor_inscricao', 'taxa_sistema', 'taxa_administracao', 'campeonatos_id'];

    public static $rules = array(
      'valor_inscricao' => 'required'
    );

    public function divisaoPremiacao() {
      return $this->hasMany('FinanceiroDivisaoPremiacao', 'detalhes_premiacao_id')->orderBy('posicao')->getResults();
    }

}
