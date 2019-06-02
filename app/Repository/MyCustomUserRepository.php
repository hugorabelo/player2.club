<?php
namespace App\Repository;

use Auth0\Login\Contract\Auth0UserRepository;
use GuzzleHttp\Client;
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
            $user = User::whereRaw("lower('$email_verificar') = lower(replace(split_part(email, '@', 1), '.', '') ||  '@' || split_part(email, '@', 2))")->first();
        } else {
            $user = User::where("auth0id", $profile->user_id)->first();
        }
        if(!isset($user)) {
            return null;
        }
        if(!isset($user->auth0id) || !isset($user->imagem_perfil) || ($user->imagem_perfil == 'perfil_padrao_homem.png') || ($user->nome === 'username')) {
            if(!isset($user->auth0id)) {
                $user->auth0id = $profile->user_id;
            }
            if(!isset($user->imagem_perfil) || ($user->imagem_perfil == 'perfil_padrao_homem.png')) {
                if(isset($profile->picture)) {
                    try {
                        $curl_handle=curl_init();
                        curl_setopt($curl_handle, CURLOPT_URL, $profile->picture);
                        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
                        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'player2.club');
                        $arquivo = curl_exec($curl_handle);
                        curl_close($curl_handle);

                        if(stripos($arquivo, 'error'))  {
                            $user->imagem_perfil = 'perfil_padrao_homem.png';
                        } else {
                            $fileName = 'usuario_'.str_replace('.', '', microtime(true)).'.jpg';
                            file_put_contents( "uploads/usuarios/$fileName", $arquivo, FILE_APPEND );
                            $user->imagem_perfil = $fileName;
                        }
                    } catch (ErrorException $e) {
                        $user->imagem_perfil = 'perfil_padrao_homem.png';
                    }
                }
            }
            if($user->nome === 'username') {
                $user->nome = $profile->name;
            }
        }

        // Recuperando IP do Usuário e Inserindo dados de Localização
        if(!isset($user->pais)) {
            $ip = \Request::getClientIp();
            $cliente = new Client(['base_uri' => 'http://ip-api.com/json/'.$ip]);
            $response = $cliente->request('GET');
            $objeto = json_decode($response->getBody(), true);
            if($objeto['status'] == 'success') {
                $user->localizacao = $objeto['city'];
                $user->uf = $objeto['region'];
                $user->pais = $objeto['countryCode'];
            }
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
