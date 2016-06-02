<?php

/*
 * Rotina Extra de Validação
 * 
 */

# pega o tipo da licença
$idTpLicenca = $campoValor[0];

# se for licença prêmio ...
if($idTpLicenca == 6)
{
    # pega o id da publicação
    $idpublicacaopremio = $campoValor[6];
            
    # pega os dados da publicação
    $pessoal = new Pessoal();
    $dados = $pessoal->get_licencaPremioDadosPublicacao($idpublicacaopremio);    
    
    # passa os dados colhidos da publicação e preenche os campos
    $campoValor[1] = $dados[0]; // dtInicioPeriodo
    $campoValor[2] = $dados[1]; // dtFimPeriodo
    $campoValor[5] = $dados[2]; // processo
    $campoValor[7] = $dados[3]; // dtPublicacao
    $campoValor[8] = $dados[4]; // pgPublicacao
} 