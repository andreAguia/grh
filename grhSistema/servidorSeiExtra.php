<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Pega o tipo
$tipo = $campoValor[0];

# Se o tipo for SEI apaga o antigo
if($tipo == 1){
    $campoValor[2] = null;
}else{
    $campoValor[1] = null;
}