<?php


class TutorialItem extends Eloquent
{
    protected $guarded = array();

    protected $table = 'tutorial_item';

    public static $rules = array(
        'mensagem' => 'required'
    );
}
