<?php

use Illuminate\Database\Seeder;

class CampeonatoUsuariosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $idCampeonato = 61;
        $campeonato = Campeonato::find($idCampeonato);
        $i = 1;
        while($campeonato->status() == 1) {
            $usuario = User::find($i);
            if($usuario !== null && $usuario->nome != "username") {
                if(!$campeonato->verificaUsuarioInscrito($i)) {
                    echo "Inserindo usuário: $i \n";
                    $userCampeonato = CampeonatoUsuario::create(array(
                        'users_id' => $i,
                        'campeonatos_id' => $idCampeonato
                    ));
                }
            }
            $i++;
        }
    }
}
