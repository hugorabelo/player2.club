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
        if(isset($profile->email)) {
            //$user = User::where("email", $profile->email)->first();
            $email_verificar = explode('@', $profile->email);
            $email_verificar = str_replace('.','', $email_verificar[0]).'@'.$email_verificar[1];
            $user = User::whereRaw("'$email_verificar' = replace(split_part(email, '@', 1), '.', '') ||  '@' || split_part(email, '@', 2)")->first();
        } else {
            $user = User::where("auth0id", $profile->user_id)->first();
        }
        if(!isset($user->auth0id) || !isset($user->imagem_perfil) || ($user->imagem_perfil == 'perfil_padrao_homem.png') || ($user->nome === 'username')) {
            if(!isset($user->auth0id)) {
                $user->auth0id = $profile->user_id;
            }
            if(!isset($user->imagem_perfil) || ($user->imagem_perfil == 'perfil_padrao_homem.png')) {
                $fileName = 'usuario_'.str_replace('.', '', microtime(true)).'.jpg';
                if(isset($profile->picture_large)) {
                    file_put_contents( "uploads/usuarios/$fileName", fopen( $profile->picture_large, "r" ), FILE_APPEND );
                } else {
                    file_put_contents( "uploads/usuarios/$fileName", fopen( $profile->picture, "r" ), FILE_APPEND );
                }
                $user->imagem_perfil = $fileName;
            }
            if($user->nome === 'username') {
                $user->nome = $profile->name;
            }
        }
        if(!isset($user->imagem_large)) {
            $fileName = 'usuario_'.str_replace('.', '', microtime(true));
            $fileNameLarge = $fileName.'_lg';
            $fileName = $fileName.'.jpg';
            $fileNameLarge = $fileNameLarge.'.jpg';
            if(isset($profile->picture)) {
                file_put_contents( "uploads/usuarios/$fileName", fopen( $profile->picture, "r" ), FILE_APPEND );
            }
            if(isset($profile->picture_large)) {
                file_put_contents( "uploads/usuarios/$fileNameLarge", fopen( $profile->picture_large, "r" ), FILE_APPEND );
            }
            $user->imagem_perfil = $fileName;
            $user->imagem_large = $fileNameLarge;
        }
        $user->ultimo_login = date('Y-m-d H:i:s');;
        $user->save();

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
