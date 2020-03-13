<?php
Use Illuminate\Database\Seeder;

class CampeonatoUsuariosTableSeeder extends Seeder {

    public function run()
    {
        // Uncomment the below to wipe the table clean before populating
        // DB::table('campeonatoadmins')->truncate();

        $i = 2;
        $usuarios_inscritos = 1;
        $ultimo_id = 335;

        $quantidade_usuarios_campeonato = 64;
        $id_campeonato = 80;
        
        while($usuarios_inscritos<$quantidade_usuarios_campeonato && $i<=$ultimo_id) {
            $usuario = User::find($i);
            if($usuario !== null) {
                $userCampeonato = CampeonatoUsuario::create(array(
                    'users_id' => $i,
                    'campeonatos_id' => $id_campeonato
                ));
                $usuarios_inscritos++;
            }
            $i++;
        }

        // Uncomment the below to run the seeder
        // DB::table('campeonato_usuarios')->insert($campeonato_usuarios);
    }

}
