<?php

/*
 * Rotina Extra de Validação
 * 
 */

/*
 * Pega os valores
 */
$origem = $campoValor[0];
$status = $campoValor[2];
$resultado = $campoValor[9];
$dtInicio = date_to_php($campoValor[15]);
$periodo = $campoValor[16];

/*
 *  Passa o parecer para caixa baixa
 */
$campoValor[17] = mb_strtolower($campoValor[17]);

/*
 *  Somente se for origem solicitado
 */
if ($origem == 2) {
    # Preenche o status de acordo com o resultado
    switch ($resultado)
    {
        # Resultado: nulo - Ainda não saiu o resultado
        # Status: 1 - Em aberto
        case null:
            $campoValor[2] = 1;
            break;

        # Resultado: 1 - Deferido
        # Status:    2 - Vigente até o término do benefício, após essa data passa para 3 - Arquivado
        case 1:

            # Verifica se já está cadastrada a data de início e o período
            if ((is_null($dtInicio)) OR (is_null($periodo))) {
                $campoValor[2] = 2;
            } else {
                $dtTermino = addMeses($dtInicio, $periodo);

                # Verifica se a data de término já passou
                if (jaPassou($dtTermino)) {
                    $campoValor[2] = 3; // Arquivado
                } else {
                    $campoValor[2] = 2; // Vigente
                }
            }
            break;

        # Resultado: 2 - Indeferido
        # Status:    3 - Arquivado
        case 2:
            $campoValor[2] = 3;
            break;
    }
} else {
    /*
     *  origem EX-ofício
     */
    # Verifica se já está cadastrada a data de início e o período
    if ((is_null($dtInicio)) OR (is_null($periodo))) {
        $campoValor[2] = 1;
        $campoValor[9] = null;
    } else {
        $dtTermino = addMeses($dtInicio, $periodo);
        $campoValor[9] = 1;
        # Verifica se a data de término já passou
        if (jaPassou($dtTermino)) {
            $campoValor[2] = 3; // Arquivado
        } else {
            $campoValor[2] = 2; // Vigente
        }
    }
}


