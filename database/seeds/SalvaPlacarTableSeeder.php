<?php

use Illuminate\Database\Seeder;

class SalvaPlacarTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $id_fase = 672;
        $grupo = FaseGrupo::find($id_fase);
        
        $partidas = $grupo->partidas();
        foreach ($partidas as $partida) {
            $i = 1;
            $usuarios = $partida->usuarios();
            $fase = $partida->grupo()->fase();
            $pontuacoes = $fase->pontuacoes();
            foreach ($usuarios as $usuario) {
                $usuarioPartida = UsuarioPartida::find($usuario['id']);
                $usuarioPartida->posicao = $i;
                if(!$fase->matamata) {
                    $usuarioPartida->pontuacao = $pontuacoes[$i];
                }
                $usuarioPartida->placar = $i%2;
                $usuarioPartida->save();
                $i++;
            }
            $partida->usuario_placar = 1;
            $partida->data_placar = date('Y-m-d H:i:s');
            $partida->save();
        }
    }
}
