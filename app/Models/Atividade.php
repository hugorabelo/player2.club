<?php

use Illuminate\Database\Eloquent\Model;

class Atividade extends Eloquent
{
    protected $guarded = array();

    protected $table = 'atividade';

    public static $rules = array(
        'users_id' => 'required'
    );

    public function curtidas() {
        return $this->belongsToMany('User', 'curtida', 'atividade_id', 'users_id')->withTimestamps();
    }

    public function curtir($idUsuario) {
        if($this->curtiu($idUsuario)) {
            $this->curtidas()->detach($idUsuario);
        } else {
            $this->curtidas()->attach($idUsuario);
        }
    }

    public function quantidadeCurtidas() {
        $curtidas = $this->curtidas()->get();
        return $curtidas->count();
    }


    public function curtiu($idUsuario) {
        return $this->curtidas()->wherePivot('users_id', '=', $idUsuario)->get()->count() > 0;
    }

    public function comentarios($idUsuarioLeitor) {
        $comentarios = $this->hasMany('Comentario', 'atividade_id')->orderBy('created_at')->get();
        foreach ($comentarios as $comentario) {
            $comentario->usuario = User::find($comentario->users_id);
            $comentario->atividade = $comentario->getAtividade();
            //$comentario->quantidade_curtidas = $comentario->quantidadeCurtidas();
            //$comentario->curtiu = $comentario->curtiu($idUsuarioLeitor);
        }
        return $comentarios;
    }

    public function getItensPesquisa($textoPesquisa) {
        $selectUsuario = DB::table('users')->select("id", "nome as descricao", "imagem_perfil as imagem")->addSelect(DB::raw("'profile' as tipo"))->whereRaw("lower(nome) like lower('%$textoPesquisa%')");
        $selectJogo = DB::table('jogos')->select("id", "descricao", "imagem_capa as imagem")->addSelect(DB::raw("'jogo' as tipo"))->whereRaw("lower(descricao) like lower('%$textoPesquisa%')");
        $selectCampeonato = DB::table('campeonatos')->select("id", "descricao", "imagem_logo as imagem")->addSelect(DB::raw("'campeonato' as tipo"))->whereRaw("lower(descricao) like lower('%$textoPesquisa%')");

        $listaFinal = $selectUsuario->union($selectJogo)->union($selectCampeonato);
        return $listaFinal->get();
    }
}
