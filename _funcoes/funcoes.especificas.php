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

###########################################################
