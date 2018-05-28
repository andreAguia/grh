<?php

/*
 * Rotina Extra de Validação
 * 
 */

$exercicio = $campoValor[0];    // Ano exercício
$dias = $campoValor[2];         // Dias solicitado
$servidor = $campoValor[4];     // idServidor
$dataInicial = $campoValor[1];   // dataInicial 

# Conecta ao banco de dados
$pessoal = new Pessoal();

# Muda o status para solicitada ou fruída de acordo com a data Inicial e a data de hoje
$timeZone = new DateTimeZone('UTC');
$data1 = DateTime::createFromFormat ('Y/m/d', $dataInicial, $timeZone);
$data2 = DateTime::createFromFormat ('Y/m/d', date("Y/m/d"), $timeZone);


if($data1 <= $data2){
    $campoValor[5] = "fruída";
}else{
    $campoValor[5] = "solicitada";
}

# Verifica quantos dias o servidor já pediu nesse exercicio
$diasFerias = $pessoal->get_feriasSomaDias($exercicio,$servidor,$id);

switch ($diasFerias){
    
    case 30 :       // Já pediu o limite
        $erro = 1;
        $msgErro .= 'O servidor não tem mais dias disponíveis para férias nesse período!\n';
        break;
    
    case 20:
        if($dias > 10){
            $erro = 1;
            $msgErro .= 'O servidor não pode tirar mais de 30 dias de férias!\n';
        }
        break;
        
    case 15:
        if($dias <> 15){
            $erro = 1;
            $msgErro .= 'O servidor só poderá tirar 15 dias!\n';
        }
        break;
        
    case 10:
        if(($dias <> 10) AND ($dias <> 20)){
            $erro = 1;
            $msgErro .= 'O servidor já tem 10 dias de férias, só poderá pedir mais 20 ou 10 dias\n';
        }
        break;
}