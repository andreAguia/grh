<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Conecta ao Banco de Dados
$pessoal = new Pessoal();

$idTpLicenca = $campoValor[0];
$alta = $campoValor[1];
$dtInicioPeriodo = $campoValor[2];
$dtFimPeriodo = $campoValor[3];
$dtInicial = $campoValor[4];
$numDias = $campoValor[5];
$processo = $campoValor[6];
$dtPublicacao = $campoValor[7];
$dtPericia = $campoValor[8];
$num_Bim = $campoValor[9];
$obs = $campoValor[10];
$idServidor = $campoValor[11];

# Pega o sexo do servidor
$sexo = $pessoal->get_sexo($idServidor);

# Verifica restrição de genero para esse afastamento
$restricao = $pessoal->get_licencaSexo($idTpLicenca);

# Feminino
if($restricao == "Feminino"){
    if($sexo <> "Feminino"){
        $msgErro.='Esse tipo de licença só é permitido para servidores do sexo feminino!\n';
        $erro = 1;
    }
}

# Masculino
if($restricao == "Masculino"){
    if($sexo <> "Masculino"){
        $msgErro.='Esse tipo de licença só é permitido para servidores do sexo masculino!\n';
        $erro = 1;
    }
}

# Verifica se nas licenças 110 e 111 tem a alta digitada
if(($idTpLicenca == 1) OR ($idTpLicenca == 30)){
    if(is_null($alta)){
        $msgErro.='E necessario informar se teve ou não alta!\n';
        $erro = 1;
    }
}
    
# Apaga a alta se nao for licenca medica
if(($idTpLicenca <> 1) AND ($idTpLicenca <> 30) AND ($idTpLicenca <> 2)){
    $campoValor[1] = NULL;
    $campoValor[2] = NULL;
}

# Apaga o periodo aquisitivo quando não precisa
if($pessoal->get_licencaPeriodo($idTpLicenca) == "Não"){
    $campoValor[2] = NULL;
    $campoValor[3] = NULL;
}

# Apaga o processo quando não precisa
if($pessoal->get_licencaProcesso($idTpLicenca) == "Não"){
    $campoValor[6] = NULL;
}

# Apaga a publicação quando não precisa
if($pessoal->get_licencaPublicacao($idTpLicenca) == "Não"){
    $campoValor[7] = NULL;
}

# Apaga a perícia quando não precisa
if($pessoal->get_licencaPericia($idTpLicenca) == "Não"){
    $campoValor[8] = NULL;
    $campoValor[9] = NULL;
}
