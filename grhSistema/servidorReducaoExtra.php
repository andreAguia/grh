<?php

/*
 * Rotina Extra de Validação
 * 
 */

$resultado = $campoValor[6];
$status = $campoValor[1];

# Preenche o status de acordo com o resultado
switch ($resultado){
    case NULL:
        $campoValor[1] = 1;
        break;
    
    case 1:
        $campoValor[1] = 2;
        break;
    
    case 2:
        $campoValor[1] = 3;
        break;
}