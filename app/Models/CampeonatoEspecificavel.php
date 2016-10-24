<?php

/**
 * Created by PhpStorm.
 * User: hugorabelo
 * Date: 04/07/16
 * Time: 09:24
 */
interface CampeonatoEspecificavel
{
    public function salvar($input);

    static public function salvarPlacarPartida($dados);

    public function iniciaFase($dadosFase, $faseAtual, $campeonato);

    public function encerraFase($dadosFase);

    public function validarNumeroDeCompetidores($detalhes);
}
