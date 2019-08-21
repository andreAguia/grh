<?php

/*
 * Rotina Extra de Validação
 * 
 */

# Verifica quantos campos tem no formulário. 
# Necessário pois a quantidade varia de acordo com o perfil do servidor
$quantidade = count($campoNome);

# Percorre o array e pega os valores
for ($i = 0; $i < $quantidade; $i++) {
    
    # Data admissão
    if($campoNome[$i] == "dtAdmissao"){
        $dtAdmissao = $campoValor[$i];
    }
    
    # Data de saída (dtDemissao)
    if($campoNome[$i] == "dtDemissao"){
        $dtSaida = $campoValor[$i];
    }
    
    # Motido de saída
    if($campoNome[$i] == "motivo"){
        $motivo = $campoValor[$i];
    }
    
    # Motido detalhado
    if($campoNome[$i] == "motivoDetalhe"){
        $motivoDetalhe = $campoValor[$i];
    }
    
    # Perfil
    if($campoNome[$i] == "idPerfil"){
        $perfil = $campoValor[$i];
    }
    
    # situação
    if($campoNome[$i] == "situacao"){
        $situacao = $campoValor[$i];
        $indiceSituacao = $i;
    }
}

# Verifica se a exoneração é posterior a admissão
if(($dtSaida < $dtAdmissao) AND (!is_null($dtSaida))){
    $msgErro.='O servidor não pode ser exonerado antes de ser admitido!\nA data está errada!\n';
    $erro = 1;
}

# Verifica se quando a data de saída estiver preenchida o motivo tb estará
if((is_null($dtSaida)) XOR (is_null($motivo))){
    $erro = 1;
    
    if(is_null($dtSaida)){
        $msgErro.='Se o motivo de saida está preenchido, a data de saída também deverá ser informada !\n';
    }
    
    if(is_null($motivo)){
        $msgErro.='Se a data de saída está preenchida, o motivo de saida também deverá ser informado !\n';
    }
}

# Verifica se quando a data de saída estiver preenchida o motivo detalhado tb estará
if($situacao<>1){
    if((is_null($dtSaida)) AND (!is_null($motivoDetalhe))){
        $erro = 1;

        if(is_null($dtSaida)){
            $msgErro.='Se o motivo detalhado de saida está preenchido, a data de saída também deverá ser informada !\n';
        }
    }
}

# Verifica se um servidor ativo data de saida ou motivo preenchido
if(($situacao == 1) AND ((!is_null($dtSaida)) OR (!is_null($motivo)))){
    $erro = 1;
    $msgErro.='Esse servidor está ativo no sistema. Deverá ter a data de saída e o motivo em branco!\n';
}

# Verifica se um servidor com situacao <> 1 tiver a data de saida ou motivo em branco
if(($situacao <> 1) AND ((is_null($dtSaida)) OR (is_null($motivo)))){
    $erro = 1;
    $msgErro.='Esse servidor não está ativo no sistema. Deverá ter a data de saída e o motivo de saída preenchidos!\n';
}

# Verifica se o motivo pode para esse perfil
if(!is_null($motivo)){
    switch ($perfil){
        case 1 :    // Estatutários
            if(($motivo == 7) OR ($motivo == 8) OR ($motivo == 12)){
                $erro = 1;   
                $msgErro.='Um servidor estatutário não pode sair da instituição por esse motivo!\n';
            }
            break;
        
        case 2 :    // Cedidos
            if(($motivo <> 2) AND ($motivo <> 11) AND ($motivo <> 12) AND ($motivo <> 13)){
                $erro = 1;   
                $msgErro.='Um servidor cedido não pode sair da instituição por esse motivo!\n';
            }
            break;
            
        case 3 :    // Convidado
            if(($motivo <> 1) AND ($motivo <> 2) AND ($motivo <> 11) AND ($motivo <> 13) AND ($motivo <> 14)){
                $erro = 1;   
                $msgErro.='Um servidor convidado não pode sair da instituição por esse motivo!\n';
            }
            break; 
            
        case 4 :    // Celetista
            if(($motivo == 1) OR ($motivo == 4) OR ($motivo == 7) OR ($motivo == 8) OR ($motivo == 12)){
                $erro = 1;   
                $msgErro.='Um servidor celetista não pode sair da instituição por esse motivo!\n';
            }
            break;
            
        case 5 :    // Contrato Nulo
        case 6 :    // Contrato Administrativo
        case 7 :    // Professor Visitante    
            if(($motivo <> 2) AND ($motivo <> 7) AND ($motivo <> 8) AND ($motivo <> 11) AND ($motivo <> 13)){
                $erro = 1;   
                $msgErro.='Um servidor contratado não pode sair da instituição por esse motivo!\n';
            }
            break;        
    }
}

# Retira os zeros à esquerda do idFuncional passando para inteiro
$campoValor[0] = intval($campoValor[0]);