<?php

/*
 * Rotina Extra de Validação
 * 
 */

$idTpLicenca = $campoValor[0];
$tipo = $campoValor[1];
$alta = $campoValor[2];
$dtInicioPeriodo = $campoValor[3];
$dtFimPeriodo = $campoValor[4];
$dtInicial = $campoValor[5];
$numDias = $campoValor[6];
$processo = $campoValor[7];
$dtPublicacao = $campoValor[8];
$pgPublicacao = $campoValor[9];
$dtPericia = $campoValor[10];
$num_Bim = $campoValor[11];
$obs = $campoValor[12];
$idServidor = $campoValor[13];

# Conecta ao Banco de Dados
$pessoal = new Pessoal();
    
echo $idTpLicenca;
# Verifica se o tipo de licença foi digitado
if($idTpLicenca == "Inicial"){
    $msgErro.='O tipo de licença tem que ser informado!\n';
    $erro = 1;
}else{
    
    # Apaga o periodo aquisitivo quando não precisa
    if($idTpLicenca <> 1){
        $campoValor[1] = NULL;
        $campoValor[2] = NULL;
    }
    
    # Apaga o periodo aquisitivo quando não precisa
    if($pessoal->get_licencaPeriodo($idTpLicenca) == "Não"){
        $campoValor[3] = NULL;
        $campoValor[4] = NULL;
    }
    
    # Apaga o processo quando não precisa
    if($pessoal->get_licencaProcesso($idTpLicenca) == "Não"){
        $campoValor[7] = NULL;
    }
    
    # Apaga a publicação quando não precisa
    if($pessoal->get_licencaPublicacao($idTpLicenca) == "Não"){
        $campoValor[8] = NULL;
        $campoValor[9] = NULL;
    }
    
    # Apaga a perícia quando não precisa
    if($pessoal->get_licencaPublicacao($idTpLicenca) == "Não"){
        $campoValor[10] = NULL;
        $campoValor[11] = NULL;
    }
}

