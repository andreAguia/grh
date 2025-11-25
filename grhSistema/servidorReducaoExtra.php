<?php

/*
 * Rotina Extra de Validação
 * 
 */

$resultado = $campoValor[2];
$status = $campoValor[1];
$dtPublicacao = date_to_php($campoValor[3]);
$dtInicio = date_to_php($campoValor[5]);
$periodo = $campoValor[6];

# Preenche o status de acordo com o resultado
switch ($resultado) {

    # Resultado: nulo - Ainda não saiu o resultado
    # Status: 1 - Em aberto
    case null:
        $campoValor[1] = 1;
        break;

    # Se
    # Resultado:          1 - Deferido
    # Data de Publicação: not Null
    # Então
    # Status:    2 - Vigente até o término do benefício, após essa data passa para 
    #            3 - Arquivado
    case 1:

        # Verifica se a data de Publicação está preenchida
        if (!empty($dtPublicacao)) {

            # Verifica se já está cadastrada a data de início e o período
            if ((is_null($dtInicio)) OR (is_null($periodo))) {
                $campoValor[1] = 2;
            } else {
                $dtTermino = addMeses($dtInicio, $periodo);

                # Verifica se a data de término já passou
                if (jaPassou($dtTermino)) {
                    $campoValor[1] = 3; // Arquivado
                } else {
                    $campoValor[1] = 2; // Vigente
                }
            }
        }else{
            # Continua em aberto
            $campoValor[1] = 1;
        }
        break;

    # Resultado: 2 - Indeferido
    # Status:    3 - Arquivado
    case 2:
        $campoValor[1] = 3;
        break;

    # Resultado: 3 - Interrompido
    # Status:    5 - Interrompido
    case 3:
        $campoValor[1] = 5;
        break;
}