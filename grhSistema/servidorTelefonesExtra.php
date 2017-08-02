<?php

/*
 * Rotina Extra de Validação
 * 
 */

$tipo = $campoValor[0];
$email = $campoValor[1];

# Verifica se o tipo do contato é email
if(($tipo == "E-mail Principal") OR ($tipo == "E-mail")){
    
    # Passa para minúsculas
    $novoEmail = strtolower($campoValor[1]);
    
    # Verifica se email é válido
    if(!filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) {
        $msgErro.='Email inválido!\n';
        $erro = 1; 
    }
    
    # Passa o novo valor para o email
    $campoValor[1] = $novoEmail;
    
}
