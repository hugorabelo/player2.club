<?php

use Illuminate\Database\Eloquent\Model;

class Atividade extends Eloquent
{
    protected $guarded = array();

    protected $table = 'atividade';

    public static $rules = array(
        'users_id' => 'required'
    );

    public function getItensPesquisa($textoPesquisa) {
        $selectUsuario = DB::table('users')->select("id", "nome as descricao", "imagem_perfil as imagem")->addSelect(DB::raw("'profile' as tipo"))->whereRaw("lower(nome) like lower('%$textoPesquisa%')");
        $selectJogo = DB::table('jogos')->select("id", "descricao", "imagem_capa as imagem")->addSelect(DB::raw("'jogo' as tipo"))->whereRaw("lower(descricao) like lower('%$textoPesquisa%')");
        $selectCampeonato = DB::table('campeonatos')->select("id", "descricao", "imagem_logo as imagem")->addSelect(DB::raw("'campeonato' as tipo"))->whereRaw("lower(descricao) like lower('%$textoPesquisa%')");

        $listaFinal = $selectUsuario->union($selectJogo)->union($selectCampeonato);
        return $listaFinal->get();
    }
}
