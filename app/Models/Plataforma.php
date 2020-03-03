<?php

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Plataforma.
 *
 * @package namespace App\Models;
 */
class Plataforma extends Model implements Transformable
{
	use TransformableTrait;
	
	protected $guarded = array();

	public static $rules = array(
		'descricao' => 'required'
	);

	public function jogos($apenasCampeonato) {
	    if(isset($apenasCampeonato)) {
            $jogos = $this->belongsToMany('Jogo', 'jogos_plataforma', 'plataformas_id', 'jogos_id')->where('permite_campeonato','=',true)->withPivot(array())->orderBy('descricao')->getResults();
        } else {
            $jogos = $this->belongsToMany('Jogo', 'jogos_plataforma', 'plataformas_id', 'jogos_id')->withPivot(array())->orderBy('descricao')->getResults();
        }
		return $jogos->values()->all();
	}
}
