<?php

class LoginController extends BaseController {

    function getLogar() {
		return File::get(public_path().'/app/views/comum/login.html');
    }

    function postLogar() {
    	$validacao = Validator::make(Input::all(), User::$regrasLogin);

    	if($validacao->fails()) {
    		return Redirect::to('login')->withErrors($validacao);
    	}

    	if(Auth::attempt(array('email'=>Input::get('email'),'password'=>Input::get('password')))) {
    		return Redirect::to('/');
    	} else {
    		return Redirect::to('login')->withErrors(Lang::get('messages.invalid_user'));
    	}
    }

	function logout() {
		Auth::logout();
		return Redirect::to('login');
	}

}

?>
