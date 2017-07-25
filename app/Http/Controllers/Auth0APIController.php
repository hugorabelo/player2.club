<?php

class Auth0APIController extends Controller {

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getProfile($email, $password) {
        $user = User::where('email','=',$email)->where('password','=',Hash::make($password))->first();
        return Response::json($user);
    }

}
