<?php

class Auth0APIController extends Controller {

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getProfile($email = null, $password = null) {
        $profile = Input::all();
        Log::warning($profile);
        $user = User::where('email','=',$email)->where('password','=',Hash::make($password))->first();
        if(isset($user)) {
            return Response::json($user);
        }
        return $profile;
    }

}
