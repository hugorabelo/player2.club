<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;

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
		$partidas = Partida::findMany($usuarioPartidas);
		foreach($partidas as $partida) {
			$usuarios = $partida->usuarios();
			foreach($usuarios as $usuario) {
				$usuario->nome = $this->find($usuario->users_id)->nome;
			}
			$partida->usuarios = $usuarios;
			// TODO incluir dados a serem utilizados do usuário para exibição das partidas
		}

		return $partidas;
	}

}
