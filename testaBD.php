<?php
/**
 * Created by PhpStorm.
 * User: hugo
 * Date: 18/12/14
 * Time: 19:49
 */

echo 'Teste';

$con_string = "host=liga.cd9kihmpbeop.sa-east-1.rds.amazonaws.com port=5432 dbname=liga user=hugo password=hugo1505";

pg_connect($con_string) or die ("Não foi possivel conectar ao servidor PostGreSQL");


echo "Conexão efetuada com sucesso!!";
