<?php


class TutorialItem extends Eloquent
{
    protected $guarded = array();

    protected $table = 'tutorial';

    public static $rules = array(
        'mensagem' => 'required'
    );
}
