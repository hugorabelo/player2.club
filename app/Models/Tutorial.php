<?php

class Tutorial extends \Eloquent
{
    protected $guarded = array();

    protected $table = 'tutorial';

    public static $rules = array(
        'descricao' => 'required',
        'key' => 'required'
    );

    public function items() {
        $items = $this->hasMany('TutorialItem','tutorial_id');
        return $items;
    }
}
