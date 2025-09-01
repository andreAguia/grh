<?php

/*
 * Rotina Extra de Validação
 * 
 */


/*
 * Apaga as outras contas padrão, caso exista desse servidor
 */

$padrao = $campoValor[0];
$idServidor = $campoValor[5];

# Verifica se é padrao
if ($padrao == "s") {

    $banco = new Banco();
    $banco->zeraContaPadrao($idServidor);
}
