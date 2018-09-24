<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Email da Uenf
if(!is_null($campoValor[3])){
   $campoValor[3] =  strtolower($campoValor[3]);
   
    # Verifica se email Uenf é válido
    if(!filter_var($campoValor[3], FILTER_VALIDATE_EMAIL)) {
        $msgErro.='Email Uenf inválido!\n';
        $erro = 1; 
    }
}

# Email da Uenf
if(!is_null($campoValor[4])){
   $campoValor[4] =  strtolower($campoValor[4]);
   
    # Verifica se email Pessoal é válido
    if(!filter_var($campoValor[4], FILTER_VALIDATE_EMAIL)) {
        $msgErro.='Email Pessoal inválido!\n';
        $erro = 1; 
    }
}





