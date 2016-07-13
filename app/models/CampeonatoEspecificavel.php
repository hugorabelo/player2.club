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

    public function iniciaFase($dadosFase);

    public function encerraFase($fase);

    public function validarNumeroDeCompetidores($detalhes);
}
