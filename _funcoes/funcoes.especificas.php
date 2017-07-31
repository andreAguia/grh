<?php

/* 
 * Funções Específicas dos sistema
 * 
 */

###########################################################

# Função que gera o dígito verificador de uma matrícula
#
# Parâmetro: a matrícula
# Retorno: a matrícula mais o dígito

Function dv($matricula)
{
    if(vazio($matricula)){
        return $matricula;
    }else{
        $ndig = 0;
        $npos = 0;

        switch (strlen($matricula))
        {
            case 4:
                $matricula = "0".$matricula;
                break;
            case 3:
                $matricula = "00".$matricula;
                break;
            case 2:
                $matricula = "000".$matricula;
                break;
        }

        # 5º Dígito

        $npos = substr($matricula,4,1);
        $npos = $npos * 2;
        if ($npos < 10) 
           $ndig = $ndig + $npos;
        else
           $ndig = $ndig + 1 + ($npos - 10);

        # 4º Dígito

        $npos = substr($matricula,3,1);
        $ndig = $ndig + $npos;

        # 3º Dígito

        $npos = substr($matricula,2,1);
        $npos = $npos * 2;
        if ($npos < 10)
           $ndig = $ndig + $npos;
        else
           $ndig = $ndig + 1 + ($npos - 10);

        # 2º Dígito

        $npos = substr($matricula,1,1);
        $ndig = $ndig + $npos;

        # 1º Dígito

        $npos = substr($matricula,0,1);
        $npos = $npos * 2;
        if ($npos < 10)
           $ndig = $ndig + $npos;
        else
           $ndig = $ndig + 1 + ($npos - 10);

        # Finalmente o resultado
        $divisao = $ndig/10;
        $int_div = intval($divisao);
        $fra_div = $divisao - $int_div;
        $mod = $fra_div * 10;

        if ($mod == 0)
            $ndig = 0;
        else
            $ndig = 10 - $mod;

        return $matricula.'-'.$ndig;
    }
}

###########################################################
/**
 * Função que retorna uma tabela com os dados do servidor
 * 
 * Obs esta função só existe para ser usada na classe modelo
 */

function get_DadosServidor($idServidor){
    Grh::listaDadosServidor($idServidor);
}

##########################################################
/**
 * Função que formata as atribuições de um cargo
 * 
 */

function formataAtribuicao($texto){
    $novoTexto = str_replace(";", ";<br/>", $texto);
    return $novoTexto;
}

##########################################################
/**
 * Função que formata a exibição da lei no servidorLicenca.php
 * 
 */

function exibeLeiLicenca($lei){
    $texto = explode("@",$lei);
    $novoTexto = str_replace("@", "<br/>", $lei);
    return $novoTexto;
}

###########################################################

function exibeDescricaoStatus($status){
/**
 * Retorna a descrição de um status de férias no on mouse over
 * 
 * @note Usado na rotina de férias e área de férias 
 * 
 * @syntax exibeDescricaoStatus($status);
 * 
 * @param $status string NULL o status das férias
 */
    
    $texto = "";
    switch ($status)
    {
        case "solicitada" :
            $texto = "Férias solicitadas pelo setor que ainda não foram recebidas oficialmente pelo GRH";
            break;
        
        case "confirmada" :
            $texto = "Férias Recebidas e verificadas oficialmente pelo GRH";
            break;
        
        case "fruida" :
            $texto = "Férias Confirmadas em que a data de início já passou. O sistema habilita essas férias automáticamente";
            break;
        
        case "cancelada" :
            $texto = "Férias canceladas pelo servidor.";
            break;
    }
    echo "<abbr title='$texto'>$status</abbr>";
}

##################################################################
    
    function descricaoComissao($idComissao){
     /**
     * Exibe informações sobre a descrição do Cargo em comissão
     * 
     * @note Quando o cargo tiver referència a lotação será exibida a lotação, quando for uma descrição será exibida uma descrição
     * 
     * @syntax descricaoComissao($idComissao);
     * 
     * @param $idComissao integer NULL o id do cargo em comissão
     */
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        #########################################
    
        # Pega os dados da tabela tbcomissao
        $select = 'SELECT idTipoComissao,
                          descricao,
                          protempore                          
                     FROM tbcomissao
                    WHERE idComissao = '.$idComissao;

        $comissao = $pessoal->select($select,FALSE);

        $idTipoComissao = $comissao[0];
        $descricao = $comissao[1];
        #$idLotacao = $comissao[2];
        $protempore = $comissao[2];
        
        #########################################
        
        #if(!is_null($idLotacao)){
        #    # Pega os dados da lotação
        #    $select = 'SELECT UADM,
        #                      DIR,
        #                      GER,
        #                      nome
        #                 FROM tblotacao
        #                WHERE idLotacao = '.$idLotacao;
        #
        #    $lotacao = $pessoal->select($select,FALSE);
        #    
        #    $uadm = $lotacao[0];
        #    $dir = $lotacao[1];
        #    $ger = $lotacao[2];
        #    $nome = $lotacao[3];
        #}
        #
        #########################################
        
        # Pega os dados da tabela de tipo de cargo em comissão
        #$select ='SELECT tbtipocomissao.descricao 
        #            FROM tbtipocomissao 
        #           WHERE idTipoComissao = '.$idTipoComissao;
        #
        #$tipoCargo = $pessoal->select($select,FALSE);
        #
        #$tipoComissao = $tipoCargo[0];
        #
        #########################################
        
        # Lotação ou descrição
        #if(is_null($idLotacao)){
        #    $retorno = $descricao;
        #}else{
        #    if($uadm == "UENF"){
        #        $retorno = $dir.'/'.$ger.' - '.$nome;
        #    }else{
        #        $retorno = $uadm.' - '.$dir.'/'.$ger.' - '.$nome;
        #    }
        #}
        $retorno = $descricao;
        
        # Informa se é protempore
        if($protempore){
            $retorno .= " (pro tempore)";
        }
        
        return $retorno;
    }