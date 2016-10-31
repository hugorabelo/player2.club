<?php

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = array();

    protected $table = 'post';

    public static $rules = array();

    public function comentarios($idUsuarioLeitor) {
        $comentarios = $this->hasMany('Comentario', 'post_id')->orderBy('created_at')->get();
        foreach ($comentarios as $comentario) {
            $comentario->usuario = User::find($comentario->users_id);
            $comentario->quantidade_curtidas = $comentario->quantidadeCurtidas();
            $comentario->curtiu = $comentario->curtiu($idUsuarioLeitor);
        }
        return $comentarios;
    }

    public function curtidas() {
        return $this->belongsToMany('User', 'curtida', 'post_id', 'users_id')->withTimestamps();
    }

    public function quantidadeCurtidas() {
        $curtidas = $this->curtidas()->get();
        return $curtidas->count();
    }

    public function curtir($idUsuario) {
        if($this->curtiu($idUsuario)) {
            $this->curtidas()->detach($idUsuario);
        } else {
            $this->curtidas()->attach($idUsuario);
            $curtida_id = $this->curtidas()->withPivot('id')->first()->pivot->id;

            $atividade = new Atividade();
            $atividade->users_id = $idUsuario;
            $atividade->curtida_id = $curtida_id;
            $atividade->save();
        }
    }

    public function curtiu($idUsuario) {
        return $this->curtidas()->wherePivot('users_id', '=', $idUsuario)->get()->count() > 0;
    }
}
