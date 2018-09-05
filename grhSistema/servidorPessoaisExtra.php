<?php

/*
 * Rotina Extra de Validação
 * 
 */

$ano = $campoValor[8];

# Valida se o valor é igual a zero e muda para nulo
if($ano == 0){
    $campoValor[8] = NULL;
    $ano = NULL;
}

# Força o ano a ter 4 digitos
if(strlen($ano) <> 4){
    $msgErro.='O ano tem que ter 4 dígitos!\n';
    $erro = 1;
}

# Ano com 4 números
if(strlen($ano) == 4){
    # Ano futuro
    if($ano > date('Y')){
        $msgErro.='O ano de chegada nao pode ser no futuro!\n';
        $erro = 1;
    }
}