<?php

/*
 * Pega o próximo número de Ci de diária
 * 
 */

# pega o número da Ci para verificar se foi digitado
$numCi = $campoValor[6];

# pega a data da ci
$dataCi = $campoValor[5];

# pega o ano da ci
$anoCi = SubStr($dataCi, 0,4);

# Conecta
$pessoal = new Pessoal();

if(!is_null($dataCi)) // verifica se a data foi preenchida pois o calculo depende dela
{
    if (is_null($numCi))// se o usuário quer que o sistema escolha o número
    {
        # verifica no banco de dados o último número de ci do ano da ci    
        $numCi = $pessoal->select('SELECT numeroCI
                                          FROM tbdiaria
                                         WHERE year(dataCi) = '.$anoCi.'                                 
                                      ORDER BY numeroCI desc',FALSE);

        if(is_null($numCi[0]))
            $numCi[0] = 300;    // se for o primeiro inicia com 300
        elseif($numCi[0] < 300)
            $numCi[0] = 300;    // se a ci for menor que 300 inicia com 300
        else    
            $numCi[0]++;        // se não soma +1 ao número

        $campoValor[6] = $numCi[0];
    }
    elseif(!is_numeric($numCi))   // verifica se é número
    {
        $msgErro.='O Campo número de Ci somente aceita números!\n';
        $erro = 1;
    }
    else // quando o número for digitado ou quando for edit
    {
        # Verifica se já existe um número de ci para o ano  da ci quando ele for digitado
        $select = 'SELECT numeroCI
                     FROM tbdiaria
                    WHERE year(dataCi) = '.$anoCi.' 
                      AND numeroCi = '.$numCi;

        if ((isset($id)) and ($id <> NULL))
            $select .=' AND iddiaria <> '.$id; // retira o próprio registro quando for edit

        $select .=' ORDER BY numeroCI desc';

        $existeCi = $pessoal->count($select);

        if($existeCi > 0)   // verifica se já existe uma ci com esse número
        {
            $msgErro.='Já existe uma Ci com esse número\!\n';
            $erro = 1;
        }
    }
}