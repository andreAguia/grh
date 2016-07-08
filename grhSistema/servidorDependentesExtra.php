<?php

/*
 * Rotina Extra de Validação
 * 
 */

# verifica se dependente é filho
if($campoValor[3] == 2)
{
    # verifica se tem auxílio creche
    if($campoValor[6] == "Sim")
    {
        # verifica a data limite do auxílio creche (6 anos e 11 meses)
        $dataLimite = addAnos(date_to_php($campoValor[1],'/'),6); // acrescenta os 6 anos
        $dataLimite = addMeses($dataLimite,11);                   // acrescenta os 11 meses
        
        # verifica se a data de término está vazia e preenche
        if(is_null($campoValor[7]))             
            $campoValor[7] = date_to_bd($dataLimite); // passa a data para o formato do bd
        
        # verifica se data é posterior a data limite
        if ($campoValor[7] > date_to_bd($dataLimite))
        {
            $erro = 1;
            $msgErro .= 'A data de término está alem da data limite!\n';
        }
    }
}
else
{
    # passa o campo de aux creche para não
    $campoValor[6] = "Não";
    
    # passa os dados do aux para nulo
    $campoValor[7] = null;  # data
    $campoValor[8] = null;  # processo
    $campoValor[9] = null;  # documento
}