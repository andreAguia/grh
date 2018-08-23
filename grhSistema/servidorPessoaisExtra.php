<?php

/*
 * Rotina Extra de Validação
 * 
 */

$ano = $campoValor[7];

# Valida se o valor é igual a zero e muda para nulo
if($ano == 0){
    $campoValor[7] = NULL;
    $ano = NULL;
}

# Força o ano a ter 4 digitos
if(strlen($ano) == 2){
    if($ano > 50){
        $ano = "19".$ano;
    }else{
        $ano = "20".$ano;
    }
    
    $campoValor[7] = $ano;
}            

# Ano com 3 números
if((strlen($ano) == 3) OR (strlen($ano) == 1)){
    $msgErro.='O ano de chegada está errado!\n';
    $erro = 1;
}

# Ano com 4 números
if(strlen($ano) == 4){
    # Ano futuro
    if($ano > date('Y')){
        $msgErro.='O ano de chegada nao pode ser no futuro!\n';
        $erro = 1;
    }

    # Ano muito antigo
    if($ano < '1920'){
        $msgErro.= 'Ano de chegada muito antigo\n';
        $erro = 1;
    }
}