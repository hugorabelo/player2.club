<?php

class CampeonatoUsuariosTableSeeder extends Seeder {

    public function run()
    {
        // Uncomment the below to wipe the table clean before populating
        // DB::table('campeonatoadmins')->truncate();

        for($i = 21; $i<31; $i++) {
            $userCampeonato = CampeonatoUsuario::create(array(
                'users_id' => $i,
                'campeonatos_id' => 39
            ));
        }

        // Uncomment the below to run the seeder
        // DB::table('campeonato_usuarios')->insert($campeonato_usuarios);
    }

}