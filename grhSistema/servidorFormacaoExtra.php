<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Retira as letras das horas
$campoValor[3] = retiraLetras($campoValor[3]);

# Passa os minutos para zero quando nulo
if (is_null($campoValor[4])) {
    $campoValor[4] = 0;
}

# Verifica se o marcador for para a portaria Petec 518/26
# Exigir que o tema seja preenchido
// Pega os Valores
$tema = $campoValor[6];
$marcador1 = $campoValor[7];
$marcador2 = $campoValor[8];
$marcador3 = $campoValor[9];
$marcador4 = $campoValor[10];

// Verifica se algum marcador é igual a 8 (Petec 518/26)
if($marcador1 == 8 OR $marcador2 == 8 OR $marcador3 == 8 OR $marcador4 == 8){
    // Verifica se o tema está em branco
    if(empty($tema)){
        $msgErro .= 'O Tema tem que ser informado para cursos Petec 518/26!\n';
        $erro = 1;
    }
    
}

