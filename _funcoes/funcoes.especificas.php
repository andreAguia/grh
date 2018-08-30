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

Function dv($matricula){
    if(vazio($matricula)){
        return $matricula;
    }else{
        $ndig = 0;
        $npos = 0;

        switch (strlen($matricula)){
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
        if ($npos < 10){
           $ndig = $ndig + $npos;
        }else{
           $ndig = $ndig + 1 + ($npos - 10);
        }

        # 4º Dígito

        $npos = substr($matricula,3,1);
        $ndig = $ndig + $npos;

        # 3º Dígito

        $npos = substr($matricula,2,1);
        $npos = $npos * 2;
        if ($npos < 10){
           $ndig = $ndig + $npos;
        }else{
           $ndig = $ndig + 1 + ($npos - 10);
        }

        # 2º Dígito

        $npos = substr($matricula,1,1);
        $ndig = $ndig + $npos;

        # 1º Dígito

        $npos = substr($matricula,0,1);
        $npos = $npos * 2;
        if ($npos < 10){
           $ndig = $ndig + $npos;
        }else{
           $ndig = $ndig + 1 + ($npos - 10);
        }

        # Finalmente o resultado
        $divisao = $ndig/10;
        $int_div = intval($divisao);
        $fra_div = $divisao - $int_div;
        $mod = $fra_div * 10;

        if ($mod == 0){
            $ndig = 0;
        }else{
            $ndig = 10 - $mod;
        }

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
    switch ($status){
        case "solicitada" :
            $texto = "Férias solicitadas pelo Servidor";
            break;
        
        case "fruída" :
            $texto = "Férias já gozadas, desfrutadas. O sistema altera para fruídas todas as férias solicitadas em que a data de início já passou.";
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

##########################################################
/**
 * Função que exibe um subtitulo na ficha cadastral
 * 
 */

function tituloRelatorio($texto){
    $div = new Div("tituloFichaCadastral");
    $div->abre();
        echo $texto;
    $div->fecha();
   
}

##########################################################
/**
 * Função que exibe um texto no final da escala de férias
 * 
 */

function textoEscalaFerias(){
    br();
    $grid = new Grid();
    $grid->abreColuna(4);
        p("01- De acordo com o Decreto 2479 Art. 91 §2., é  proibida a acumulação de férias, salvo imperiosa necessidade de serviço, não podendo a acumulação, neste caso, abranger mais de dois períodos.","justify","f14");
    $grid->fechaColuna();
    $grid->abreColuna(4);    
        p("02- Esta Escala deverá ser devolvida à Gerência de Recursos Humanos - GRH até o dia 01/11/2018. É imprescindível a assinatura do chefe imediato, e na impossibilidade do mesmo, deverá ser assinada pelo chefe superior.","justify","f14");
    $grid->fechaColuna();
    $grid->abreColuna(4);    
        p("03- Eventuais alterações deverão ser comunicadas a esta gerência com antecedência mínima de 60 (sessenta dias) a contar da data de início das férias","justify","f14");
    $grid->fechaColuna();
    $grid->abreColuna(12); 
        p("Data: _____/_____/_____&ensp;&ensp;&ensp;&ensp;Local: _____________________________&ensp;&ensp;&ensp;&ensp;Assinatura: ___________________________________","f14");
    $grid->fechaColuna();
    $grid->fechaGrid();
}

##########################################################

function exibeProcessoPremio($texto){
/**
 * Função que exibe o processo de licença Premio
 * 
 * A tabela de licença de um servidor recebe informação de 2
 * tabelas unidas. Dessa forma foi criado uma codificação para
 * exibir o processo ora da tabela tbLicenca ora da tbservidor.
 * Essa função identifica e retorna o processo correto
 * 
 */
    
    # Divide o texto TIPO&ID
    $pedaco = explode("&", $texto);
    
    if($pedaco[0] == 6){ 
        ## Licença Prêmio
        
        # Inicia a classe
        $licenca = new LicencaPremio();
        
        # Pega o processo 
        $processo = $licenca->get_numProcesso($pedaco[1]);        
    }else{
        ## Outras Licenças
        
        # Inicia a classe
        $pessoal = new Pessoal();
        
        # Pega o processo 
        $processo = $pessoal->get_licencaNumeroProcesso($pedaco[1]);
    }
    return $processo;
}

##########################################################

function acertaDataFerias($texto){
/**
 * Função que acerta o nome do mês e exibe junto do ano
 * 
 * Usado no relatório que lista solicitações de férias de um ano exercício
 * 
 */
    
    # Divide o texto mes/ano
    $pedaco = explode("/", $texto);
    
    $mes = get_nomeMes($pedaco[0]);
    
    $retorno = $mes." / ".$pedaco[1];
    
    return $retorno;
}

##########################################################

function exibeDiasLicencaPremio($idServidor){
/**
 * Função exibe os dias Publicados, Fruídos e Disponíveis de licença premio
 * 
 * Usado na tabela da área de licença premio
 */
    
    # Pega os dados
    $licenca = new LicencaPremio;
    $diasPublicados = $licenca->get_numDiasPublicados($idServidor);
    $diasFruidos = $licenca->get_numDiasFruidos($idServidor);
    $diasDisponíveis = $diasPublicados - $diasFruidos;
    
    $retorno = $diasPublicados." / ".$diasFruidos." / ";
    
    if($diasDisponíveis < 0){
        $retorno .= "<span id='negativo'><B>".$diasDisponíveis."</B></span>";
    }else{
        $retorno .= $diasDisponíveis;
    }
    
    return $retorno;
}

##########################################################

function exibeNumPublicacoesLicencaPremio($idServidor){
/**
 * Função exibe o número de publicações de licença premio Reais, Possiveis e Faltantes
 * 
 * Usado na tabela da área de licença premio
 */
    
    # Pega os dados
    $licenca = new LicencaPremio;
    $numPublicacao = $licenca->get_numPublicacoes($idServidor);
    $numPublicacaoPossivel = $licenca->get_numPublicacoesPossiveis($idServidor);
    $numPublicacaoFaltante = $numPublicacaoPossivel - $numPublicacao;
    
    $retorno = $numPublicacao." / ".$numPublicacaoPossivel." / ".$numPublicacaoFaltante;
    return $retorno;
}

##########################################################

function exibePrazoParaGozoEscalaFerias($texto){
    /**
     * Função que exibe o prazo para gozo ou fruição de uma determinada férias do servidor
     * 
     * Função criada pois quando o servidor está em sua primeira férias a data do início do gozo é diferente
     */
        
        # Divide o texto idServidor&Ano
        $pedaco = explode("&", $texto);

        # Pega os valores
        $idServidor = $pedaco[0];
        $anoPesquisado = $pedaco[1];

        # Pega o ano de admissão do servidor
        $pessoal = new Pessoal;
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);
        $anoAdmissao = year($dtAdmissao);

        # Define a variável de retorno
        $retorno = NULL;
        
        # Se o ano pesquisado for o mesmo da admissão
        if($anoPesquisado == $anoAdmissao){
            $retorno = "Ainda não fez um ano !";
        }

        # Se o ano pesquisado for anterior da admissão
        if( $anoPesquisado < $anoAdmissao){
            $retorno = "Ainda não tinha sido admitido !!";
        }

        # Se o ano pesquisado for o imediatamente posterior
        if($anoPesquisado == ($anoAdmissao+1)){

            # Pega o dia da admissão
            $dia = day($dtAdmissao);

            # Pega o mês da admissão
            $mes = month($dtAdmissao);

            $dataInicial = $dia."/".$mes;
            $dataFinal = "31/12";
            $retorno = $dataInicial." - ".$dataFinal;
        }

        # Se o ano pesquisado for depois da admissão
        if($anoPesquisado > ($anoAdmissao+1)){
            $dataInicial = "01/01";
            $dataFinal = "31/12";
            $retorno = $dataInicial." - ".$dataFinal;
        }

        return $retorno;
    }
    
##########################################################

function exibeFeriasPendentes($texto){
/**
 * Função o numero de dias de ferias pendentes de um servidor
 * 
 * Usado no relatorio de escala de ferias
 */
    
    # Divide o texto idServidor&Ano
    $pedaco = explode("&", $texto);

    # Pega os valores
    $idServidor = $pedaco[0];
    $anoPesquisado = $pedaco[1];
    
    # Define as variaveis 
    $retorno = NULL;
    $linhas = 0;    // numero de linhas para saber se for mais de um tere que ter br
            
    # Conecta o banco de dados
    $pessoal = new Pessoal();
    
    # Pega os dados do servidor
    $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);    // Data de admissao
    $anoAdmissao = year($dtAdmissao);
    
    # As ferias estao cadastradas somente apartir desse ano
    # Entao a busca sera a partir desse ano. Cadastrando-se mais anos
    # Altera-se esse valor
    #$feriasCadastradas = 2014;
    
    # Atualizaçao: SAndra pediu para ser a partir de 2016 para nao exibir alguns problemas antigos no cadastro de ferias
    $feriasCadastradas = 2016;
    
    # Monta o retorno
    for ($i = $anoAdmissao+1; $i <= $anoPesquisado; $i++) {
        if($i >= $feriasCadastradas){
            $dias = $pessoal->get_feriasSomaDias($i, $idServidor);
            
            # Transforma o nullo em zero
            if(is_null($dias)){ 
                $dias = 0;
            }
            
            # Verifica se e o ano atual e informa que nao tem mais ferias
            # a serem tiradas esse ano quando ja tirou 30 dias
            if($i == $anoPesquisado){
                if($dias > 0){
                    if($linhas >0){
                        $retorno.="<br/>";
                    }
                    
                    $retorno .= "Já solicitou os $dias dias de ".$anoPesquisado;
                    $linhas++;
                }
            }else{
                # Verifica se tem pendencia 
                if($dias < 30){
                    $pendencia = 30 - $dias;
                    
                    if($linhas >0){
                        $retorno.="<br/>";
                    }

                    $retorno .= "($i) - pendente $pendencia Dias,";
                    $linhas++;
                }
            }
        }
    }
    
    return $retorno;
}

##########################################################