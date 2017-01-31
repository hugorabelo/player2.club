<?php
namespace App\Repository;

use Auth0\Login\Contract\Auth0UserRepository;
use User;

class MyCustomUserRepository implements Auth0UserRepository {

    /* This class is used on api authN to fetch the user based on the jwt.*/
    public function getUserByDecodedJWT($jwt) {
        /*
         * The `sub` claim in the token represents the subject of the token
         * and it is always the `user_id`
         */
        $jwt->user_id = $jwt->sub;

        return $this->upsertUser($jwt);
    }

    public function getUserByUserInfo($userInfo) {
        return $this->upsertUser($userInfo['profile']);
    }

    protected function upsertUser($profile) {
        $user = User::where("email", $profile->email)->first();
        if(!isset($user->auth0id) || !isset($user->imagem_perfil) || ($user->nome === 'username')) {
            if(!isset($user->auth0id)) {
                $user->auth0id = $profile->user_id;
            }
            if(!isset($user->imagem_perfil)) {
                $fileName = 'usuario_'.str_replace('.', '', microtime(true)).'.jpg';
                file_put_contents( "uploads/usuarios/$fileName", fopen( $profile->picture, "r" ), FILE_APPEND );
                $user->imagem_perfil = $fileName;
            }
            if($user->nome === 'username') {
                $user->nome = $profile->name;https://scontent.xx.fbcdn.net/v/t1.0-1/p50x50/10474254_747590795292744_6156217768799817805_n.jpg?oh=397bec29457fb279a99348fd9a073beb&oe=594B8E92
            }
            $user->save();
        }

        /*
        if ($user === null) {
            // If not, create one
            $user = new User();

            $user->email = $profile->email; // you should ask for the email scope
            $user->auth0id = $profile->user_id;
            $user->nome = $profile->name; // you should ask for the name scope

            $user->password = 'xxx';
            $user->usuario_tipos_id = 1;

            $user->save();
        }
        */

        return $user;
    }

    public function getUserByIdentifier($identifier) {
        //Get the user info of the user logged in (probably in session)

        $user = \App::make('auth0')->getUser();

        if ($user === null) return null;

        // build the user
        $user = $this->getUserByUserInfo($user);

        // it is not the same user as logged in, it is not valid
        if ($user && $user->auth0id == $identifier) {
            //return $auth0User;
            return null;
        }
    }

}