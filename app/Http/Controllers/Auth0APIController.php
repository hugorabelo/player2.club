<?php

use \Illuminate\Http\Request;

class Auth0APIController extends Controller {

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getProfile(Request $request) {
        $profile = $request->all();

        $user = User::where('email','=',$profile['email'])->where('password','=',Hash::make($profile['password']))->first();
        if(isset($user)) {
            return Response::json($user);
        }
        return $profile;
    }

}
