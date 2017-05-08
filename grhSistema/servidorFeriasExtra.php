<?php

/*
 * Rotina Extra de Validação
 * 
 */

$exercicio = $campoValor[0];
$dias = $campoValor[3];
$servidor = $campoValor[7];
$periodo = NULL;

$pessoal = new Pessoal();
$diasFerias = $pessoal->get_feriasSomaDias($exercicio,$servidor);
$quantidadePeriodos = $pessoal->get_feriasQuantidadesPeriodos($exercicio,$servidor);

switch ($diasFerias){
    case 0 :        // Não pediu férias anteriores
        if($dias == 30){            // Pediu 30 de vez
            $periodo = 'Único';     // é único
        }else{
            $periodo = '1º';        // Se pediu menos de 30 o período é o primeiro
        }
        break;
    case 30 :       // Já pediu o limite
        $erro = 1;
        $msgErro .= 'O servidor não tem mais dias disponíveis para férias nesse período!\n';
        break;
    
    case 20:
    case 15:
    case 10:
        if($dias+$diasFerias > 30){
            $erro = 1;
            $msgErro .= 'O servidor não pode tirar mais de 30 dias de férias!\n';
        }else{
            $periodo = $quantidadePeriodos+1;
            $periodo .= "º";
        }
        break;
            
}

$campoValor[6] = $periodo;