<?php

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Eloquent implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	protected $guarded = array();

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');

    /**
     * Atributo para definir as regras de validação para este objeto
     *
     */
    public static $rules = array('nome'=>'required',
                                 'email'=>'required|email|unique:users',
                                 'password'=>'required|min:6');

	public static $regrasLogin = array('email'=>'required|email',
								 'password'=>'required');

    public function usuarioTipo() {
    	return UsuarioTipo::find($this->usuario_tipos_id);
    }

	public function partidas() {
		$usuarioPartidas = UsuarioPartida::where("users_id", "=", $this->id)->get(array("partidas_id"))->toArray();
		$partidas = Partida::findMany($usuarioPartidas)->sortBy('id');
		foreach($partidas as $partida) {
			$partida->confirmarPlacarAutomaticamente();
			if($partida->contestada()) {
				$partida->contestada = true;
			}
			$usuarios = $partida->usuarios();
			$partida->usuarios = $usuarios;
			// TODO incluir dados a serem utilizados do usuário para exibição das partidas
			if($partida->data_placar != null) {
				$partida->data_placar_limite = $partida->getDataLimitePlacar();
			}
		}

		$partidas->values()->all();
		return $partidas;
	}

	public function campeonatos() {
		// Exibir campeonatos do usuário
		// Cada campeonato, possuirá uma coleção das partidas do usuário
		// Fases?
	}

	public function seguidores() {
		return $this->belongsToMany('User', 'seguidor', 'users_id_mestre', 'users_id_seguidor');
	}

	public function seguindo() {
		return $this->belongsToMany('User', 'seguidor', 'users_id_seguidor', 'users_id_mestre');
	}

	public function seguir($idUsuario) {
	    $this->seguindo()->attach($idUsuario);
    }
    public function deixarDeSeguir($idUsuario) {
        $this->seguindo()->detach($idUsuario);
    }


    public function getPosts($idUsuarioLeitor, $quantidade = 5) {
        $posts = $this->hasMany('Post', 'users_id')->orderBy('created_at', 'desc')->get();
        foreach ($posts as $post) {
            $post->comentarios = $post->comentarios($idUsuarioLeitor);
			$post->usuario = $this;
            $post->curtiu = $post->curtiu($idUsuarioLeitor);
			$post->quantidade_curtidas = $post->quantidadeCurtidas();
        }
        return $posts;
    }

	public function segue($idUsuario) {
		$segue = $this->seguindo()->wherePivot('users_id_mestre', '=', $idUsuario)->get();
		if($segue->count() > 0) {
			return true;
		}
		return false;
	}

}
