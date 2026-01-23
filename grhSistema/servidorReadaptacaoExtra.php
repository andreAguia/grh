<?php

/*
 * Rotina Extra de Validação
 * 
 */

/*
 * Pega os valores
 */
$origem = $campoValor[0];
$status = $campoValor[3];
$resultado = $campoValor[4];
$dtInicio = date_to_php($campoValor[7]);
$periodo = $campoValor[8];

/*
 *  Passa o parecer para caixa baixa
 */
$campoValor[9] = mb_strtolower($campoValor[9]);

/*
 *  Somente se for origem solicitado
 */
if ($origem == 2) {
    # Preenche o status de acordo com o resultado
    switch ($resultado) {
        # Resultado: nulo - Ainda não saiu o resultado
        # Status: 1 - Em aberto
        case null:
            $campoValor[3] = 1;
            break;

        # Resultado: 1 - Deferido
        # Status:    2 - Vigente até o término do benefício, após essa data passa para 3 - Arquivado
        case 1:

            # Verifica se já está cadastrada a data de início e o período
            if ((is_null($dtInicio)) OR (is_null($periodo))) {
                $campoValor[3] = 2;
            } else {
                $dtTermino = addMeses($dtInicio, $periodo);

                # Verifica se a data de término já passou
                if (jaPassou($dtTermino)) {
                    $campoValor[3] = 3; // Arquivado
                } else {
                    $campoValor[3] = 2; // Vigente
                }
            }
            break;

        # Resultado: 2 - Indeferido
        # Status:    3 - Arquivado
        case 2:
            $campoValor[3] = 3;
            break;

        # Resultado: 3 - Interrompido
        # Status:    3 - Arquivado
        case 3:
            $campoValor[3] = 3;
            break;
    }
} else {
    /*
     *  origem EX-ofício
     */

    # Primeiro verifica se o resultado é interrompido
    # Resultado: 3 - Interrompido
    # Status:    3 - Arquivado
    if ($resultado == 3) {
        $campoValor[3] = 3; // Status -> 3 - Arquivado
    } else {
        # Senão for interrompido faz o resto
        # Verifica se já está cadastrada a data de início e o período
        if ((is_null($dtInicio)) OR (is_null($periodo))) {
            $campoValor[3] = 1;
            $campoValor[4] = null;
        } else {
            $dtTermino = addMeses($dtInicio, $periodo);
            $campoValor[4] = 1;
            # Verifica se a data de término já passou
            if (jaPassou($dtTermino)) {
                $campoValor[3] = 3; // Arquivado
            } else {
                $campoValor[3] = 2; // Vigente
            }
        }
    }
}


