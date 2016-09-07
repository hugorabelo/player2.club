<?php

class Menu extends Eloquent {
	protected $table = 'menu';

	protected $guarded = array();

	public static $rules = array(
		'descricao' => 'required',
		'ordem' => 'required'
	);

	public function pai() {
		return Menu::find($this->menu_pai);
	}

}
