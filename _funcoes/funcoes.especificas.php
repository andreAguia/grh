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
 * Exibe informações sobre a Nome do Laboratório, do Curso, da Gerência, da Diretoria ou da Pró Reitoria	
 * 
 * @note Usado na rotina de cadastro de Cargo em comissão de um detrerminado servidor
 * 
 * @syntax descricaoComissao($idComissao);
 * 
 * @param $idComissao integer NULL o id do cargo em comissão
 */

    # Conecta ao Banco de Dados
    $comissao = new CargoComissao();
    
    # Pega os dados da comissão
    $dados = $comissao->get_dados($idComissao);
    $descricao = $comissao->get_descricaoCargo($idComissao);
    $tipo = $dados['tipo'];
    
    $retorno = $descricao;
    
    # Informa o tipo
    switch ($tipo){
    
        case 1:
            $retorno .= " <span id='orgaoCedido'>(Pro Tempore)</span>";
            break;
        
        case 2:
            $retorno .= " <span id='orgaoCedido'>(Designado)</span>";
            break;
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
    
    $intra = new Intra();
    $dataDev = $intra->get_variavel("dataDevolucaoGrh");
    
    br();
    $grid = new Grid();
    $grid->abreColuna(4);
        p("01- De acordo com o Decreto 2479 Art. 91 §2., é  proibida a acumulação de férias, salvo imperiosa necessidade de serviço, não podendo a acumulação, neste caso, abranger mais de dois períodos.","justify","f14");
    $grid->fechaColuna();
    $grid->abreColuna(4);    
        p("02- Esta Escala deverá ser devolvida à Gerência de Recursos Humanos - GRH até o dia $dataDev. É imprescindível a assinatura do chefe imediato, e na impossibilidade do mesmo, deverá ser assinada pelo chefe superior.","justify","f14");
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
    
    # Conecta ao Banco de Dados
    $licenca = new LicencaPremio;
    
    # Verifica o número de vínculos com licença premio
    $numVinculos = $licenca->get_numVinculosPremio($idServidor);
    
    # Carrega os valores do servidor ativo
    $diasPublicados = $licenca->get_numDiasPublicados($idServidor);
    $diasFruidos = $licenca->get_numDiasFruidos($idServidor);
    $diasDisponiveis = $licenca->get_numDiasDisponiveis($idServidor);

    if($numVinculos > 1){
        # Carrega os valores do servidor ativo
        $diasPublicadosTotal = $licenca->get_numDiasPublicadosTotal($idServidor);
        $diasFruidosTotal = $licenca->get_numDiasFruidosTotal($idServidor);
        $diasDisponiveisTotal = $licenca->get_numDiasDisponiveisTotal($idServidor);
    }
    
    # Monta o retorno
    $retorno = "$diasPublicados / $diasFruidos / ";
    
    # Coloca em vermelho quando negativo
    if($diasDisponiveis < 0){
        $retorno .= "<span id='negativo'><B>".$diasDisponiveis."</B></span>";
    }else{
        $retorno .= $diasDisponiveis;
    }
    
    
    if($numVinculos > 1){
        # Pega o array dos vinculos
        $vinculos = $licenca->get_vinculosPremio($idServidor);
        
        foreach($vinculos as $tt){
            if($tt[0] <> $idServidor){
                $diasPublicados = $licenca->get_numDiasPublicados($tt[0]);
                $diasFruidos = $licenca->get_numDiasFruidos($tt[0]);
                $diasDisponiveis = $licenca->get_numDiasDisponiveis($tt[0]);

                $retorno .= "<br/>";
                $retorno .= "$diasPublicados / $diasFruidos / ";

                # Coloca em vermelho quando negativo
                if($diasDisponiveis < 0){
                    $retorno .= "<span id='negativo'><B>".$diasDisponiveis."</B></span>";
                }else{
                    $retorno .= $diasDisponiveis;
                }
            }
        }
        
        $retorno .= "<hr id='alerta'/>";
        $retorno .= "$diasPublicadosTotal / $diasFruidosTotal / ";

        # Coloca em vermelho quando negativo
        if($diasDisponiveisTotal < 0){
            $retorno .= "<span id='negativo'><B>".$diasDisponiveisTotal."</B></span>";
        }else{
            $retorno .= $diasDisponiveisTotal;
        }
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
    # Conecta ao Banco de Dados
    $licenca = new LicencaPremio;
    
    # Verifica o número de vínculos com licença premio
    $numVinculos = $licenca->get_numVinculosPremio($idServidor);
    
    # Carrega os valores do servidor ativo
    $numPublicacao = $licenca->get_numPublicacoes($idServidor);
    $numPublicacaoPossivel = $licenca->get_numPublicacoesPossiveis($idServidor);
    $numPublicacaoFaltante = $numPublicacaoPossivel - $numPublicacao;

    if($numVinculos > 1){
        # Carrega os valores do servidor ativo
        $numPublicacaoTotal = $licenca->get_numPublicacoesTotal($idServidor);
        $numPublicacaoPossivelTotal = $licenca->get_numPublicacoesPossiveisTotal($idServidor);
        $numPublicacaoFaltanteTotal = $numPublicacaoPossivelTotal - $numPublicacaoTotal;
    }

    
    # Monta o retorno
    $retorno = "$numPublicacao / $numPublicacaoPossivel / ";
    
    # Coloca em vermelho quando negativo
    if($numPublicacaoFaltante < 0){
        $retorno .= "<span id='negativo'><B>".$numPublicacaoFaltante."</B></span>";
    }else{
        $retorno .= $numPublicacaoFaltante;
    }
        
    if($numVinculos > 1){
        # Pega o array dos vinculos
        $vinculos = $licenca->get_vinculosPremio($idServidor);
        
        foreach($vinculos as $tt){
            if($tt[0] <> $idServidor){
                $numPublicacao = $licenca->get_numPublicacoes($tt[0]);
                $numPublicacaoPossivel = $licenca->get_numPublicacoesPossiveis($tt[0]);
                $numPublicacaoFaltante = $numPublicacaoPossivel - $numPublicacao;

                $retorno .= "<br/>";
                $retorno .= "$numPublicacao / $numPublicacaoPossivel / ";

                # Coloca em vermelho quando negativo
                if($numPublicacaoFaltante < 0){
                    $retorno .= "<span id='negativo'><B>".$numPublicacaoFaltante."</B></span>";
                }else{
                    $retorno .= $numPublicacaoFaltante;
                }
            }
        }
        
        $retorno .= "<hr id='alerta'/>";
        $retorno .= "$numPublicacaoTotal / $numPublicacaoPossivelTotal / ";

        # Coloca em vermelho quando negativo
        if($numPublicacaoFaltanteTotal < 0){
            $retorno .= "<span id='negativo'><B>".$numPublicacaoFaltanteTotal."</B></span>";
        }else{
            $retorno .= $numPublicacaoFaltanteTotal;
        }
    }
    
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

function consertaUf($uf){
    
    /* 
     * Funçao que conserta um campo de unidade federal que esta na forma de 
     * string e transforma em inteiro com o id da tabela tbestado
     */
    
    if(!is_integer($uf)){
        $select = 'SELECT idEstado
                     FROM tbestado
                    WHERE uf = '.$uf;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);

        $uf = $row[0];
    }
    
    return $uf;
}

###########################################################
/**
 * Função que retorna a situaçao do servidor mais informaçoes de ferias, licença, etc
 * Obs esta função só existe para ser usada na classe modelo
 */

function get_situacao($idServidor){
    $pessoal = new Pessoal();
    $situacao = $pessoal->get_situacao($idServidor);
    $especial = NULL;
    $title = NULL;
    
    # Pega as situações
    $ferias = $pessoal->emFerias($idServidor);
    $licenca = $pessoal->emLicenca($idServidor);
    $licencaPremio = $pessoal->emLicencaPremio($idServidor);
    $folgaTre = $pessoal->emFolgaTre($idServidor);
    $afastadoTre = $pessoal->emAfastamentoTre($idServidor);
    $cedido = $pessoal->emCessao($idServidor);
    $orgaoCedido = NULL;
    
    # Férias
    if($ferias){
        $especial = 'Férias';
        $title = 'Exercicio '.$pessoal->emFeriasExercicio($idServidor);
    }

    # Licenca
    if($licenca){
        $title = $pessoal->get_licenca($idServidor);
        $especial = 'Licença';
    }

    # Licenca Prêmio
    if($licencaPremio){
        $title = $pessoal->get_licencaNome(6);
        $especial = 'Licença';
    }

    # Folga TRE
    if($folgaTre){
        $especial = 'Folga TRE';
        $title = 'Em folga do TRE';
    }

    # Afastamento TRE
    if($afastadoTre){
        $especial = 'TRE';
        $title = 'Trabalhando no TRE';
    }

    # Cedido
    if($cedido){
        $orgaoCedido = $pessoal->get_orgaoCedido($idServidor);
        $especial = 'Cedido';
        $title = $orgaoCedido;
    }
    
    # Monta variavel de retorno    
    if(is_null($especial)){
        $retorno = $situacao;
    }else{
        $retorno = $situacao.'<br/><span title="'.$title.'" class="warning label">'.$especial.'</span>';
    }
    return $retorno;
}

###########################################################
/**
 * Função que retorna a situaçao do servidor mais informaçoes de ferias, licença, etc
 * Obs esta função só existe para ser usada na classe modelo
 */

function get_situacaoRel($idServidor){
    $pessoal = new Pessoal();
    $situacao = $pessoal->get_situacao($idServidor);
    
    return $situacao;
}

###########################################################
/**
 * Função que retorna o servidor cadastrado para o atyendimento do balcao no dia informado
 * Obs esta função só existe para ser usada na rotina de controle do atendimento do balcão
 */

function get_servidorBalcao($ano,$mes,$dia,$turno){
    $pessoal = new Pessoal();
    
    $select = 'SELECT manha,
                      tarde
                FROM tbbalcao
               WHERE ano = '.$ano.'
                 AND mes = '.$mes.' 
                 AND dia = '.$dia;

   $row = $pessoal->select($select,FALSE);
   $count = $pessoal->count($select,FALSE);
   
   if($count == 0){
       return "?";
   }else{
       if($turno == "m"){
           if(vazio($row[0])){
               return "?";
           }else{
               return $row[0];
           }
       }else{
           if(vazio($row[1])){
               return "?";
           }else{
               return $row[1];
           }
       }
   }
}

###########################################################
/**
 * Função que retorna o idBalcao de um dia específico para saber se será update ou insert
 * Obs esta função só existe para ser usada na rotina de controle do atendimento do balcão
 */

function get_idBalcao($ano,$mes,$dia){
    $pessoal = new Pessoal();
    
    $select = 'SELECT idBalcao
                FROM tbbalcao
               WHERE ano = '.$ano.'
                 AND mes = '.$mes.' 
                 AND dia = '.$dia;

   $row = $pessoal->select($select,FALSE);
   return $row[0];
}

###########################################################
/**
 * Função que retorna o primeiro nome de um idServidor
 * Obs esta função só existe para ser usada na rotina de controle do atendimento do balcão e chama a classe homonima
 */

function get_nomeSimples($nome){
    
    # trata o nome para pegar somente o primeiro nome
    $parte = explode(" ", $nome);

    # Verifica se e nome composto
    $nomesCompostos = array("Ana", "Maria", "Andre","André"); 

    # Verifica se o nome em questao e composto e insere o segundo nome
    if(in_array($parte[0], $nomesCompostos)) { 
        $nomeSimples = $parte[0]." ".$parte[1];
    }else{
        $nomeSimples = $parte[0];
    }  
    
    return $nomeSimples;
}

##################################################################

    function statusReducao($arquivado){
    /**
     * Exibe na tabela de redução de carga horária imagem se está arquivado ou não
     * 
     * @note Usado na rotina de cadastro de redução de carga horária
     * 
     * @syntax statusReducao($arquivado);
     * 
     * @param $arquivado int NULL se foi arquivado ou não
     */

        if($arquivado){
            $figura = new Imagem(PASTA_FIGURAS.'arquivo.png','Arquivado',30,30);
            $figura->show();
        }else{
            echo "";
        }
    }
    
##########################################################

    function idMatricula($idServidor){
    /**
     * Função exibe o id e a matrícula de um servidor
     * 
     * Usado quando se deseja nas tabelas exibir as duas informações na mesma coluna
     */

        $pessoal = new Pessoal();
    
        $select = 'SELECT idFuncional,
                          matricula
                     FROM tbservidor
                   WHERE idServidor = '.$idServidor;

       $row = $pessoal->select($select,FALSE);
       $matricula = dv($row[1]);
       
       if(vazio($row[0]) OR vazio($matricula)){
           $retorno = $row[0].$matricula;
       }else{
           $retorno = $row[0]." / ".$matricula;
       }
              
       return $retorno;
    }

##########################################################

    function marcaSePassou($data){
    /**
     * Função exibe a data com uma marca se a data já passou
     * 
     * Usado na rotina da área de aposentadoria
     */

        if(jaPassou($data)){
            label($data);
        }else{
           echo $data;
        }
    }

##########################################################

    function exibeLicencaPremio($idServidor){
    /**
     * Função exibe as licenças preomio de um servidor
     * 
     * Usado na rotina da área de Licença Premio
     */
        
        $licenca = new LicencaPremio();
        $licenca->exibeLicencaPremio($idServidor);
    }

##########################################################

    function exibeFoto($idPessoa){
    /**
     * Função exibe a foto do servidor 
     * 
     * Usado na rotina da área de fotografia
     */
        
        $foto = new ExibeFoto();
        $foto->set_fotoLargura(35);
        $foto->set_fotoAltura(45);
        $foto->set_url('?fase=exibeFoto&idPessoa='.$idPessoa);
        $foto->show($idPessoa);
    }

##########################################################
    
    function linkExibeVaga($idConcurso){
    /**
     * Exibe um link para as vagas de um concurso
     * 
     * @param $idConcurso integer NULL O id do Concurso
     * 
     * @syntax $plano->linkExibeVaga($idConcurso);
     */
        
        # Verifica o idConcurso
        if(Vazio($idConcurso)){
            alert("É necessario informar o id do Concurso");
        }else{
            # Pega os dados do concurso
            $concurso = new Concurso($idConcurso);
            $dados = $concurso->get_dados();
            
            # Varifica se o tipo do concurso (2 - Professores ou 1 - Adm & técnico)
            $tipo = $dados["tipo"];
            
            if($tipo == 2){
                # Monta o link
                $link = new Link(NULL,"cadastroConcurso.php?fase=listaVagasConcurso&id=$idConcurso","Exibe as vagas");
                $link->set_imagem(PASTA_FIGURAS_GERAIS."ver.png",20,20);
                $link->show();
            }else{
                echo "-";
            }
        }
    }
    
##########################################################

    function exibeDadosSalarioAtual($idServidor){
    /**
     * Função exibe dados do salario atual do servidor
     * 
     * Usado no relatório financeiro de progressao
     */
        
        # Conecta 
        $pessoal = new Pessoal();
        
        # Pega o idClasse so salário atual
        $idClasse = $pessoal->get_idClasseServidor($idServidor);
        
        if(vazio($idClasse)){
            return NULL;
        }else{
        
            # Pega os dados desse idClasse
            $select = "SELECT faixa,
                              valor,
                              tbplano.numdecreto
                         FROM tbclasse LEFT JOIN tbplano USING (idPlano)
                         WHERE idClasse = $idClasse";

            $row = $pessoal->select($select,FALSE);

            $return = $row[0]." - ".$row[1]."<br/>".$row[2];
            return $return;
        }
    }

###########################################################

function exibeDocumentacaoLicenca($idTipoLicenca){
/**
 * Exibe um quadro com a documentação e a observação desse tipo de licença
 * 
 * @note Usado na rotina de licença 
 * 
 * @syntax exibeDocumentacaoLicenca($idTpLicenca);
 * 
 * @param $idTipoLicenca integer NULL o id do tipo de licença
 */
    
    # Conecta 
    $pessoal = new Pessoal();
    
    # Pega os dados desse idClasse
    $select = "SELECT documentacao,
                      nome
                 FROM tbtipolicenca
                 WHERE idTpLicenca = $idTipoLicenca";

    $row = $pessoal->select($select,FALSE);
    
    
    tituloTable($row[1]);
    $painel = new Callout();
    $painel->abre();
    
        p("Documentação Necessária",'f14',"left");    
        p(trataNulo($row[0]),'f13',"left");
    
    $painel->fecha();
    return;
}

###########################################################

function exibeBotaoDocumentacaoLicenca($idTipoLicenca){
/**
 * Exibe um quadro com a documentação e a observação desse tipo de licença
 * 
 * @note Usado na rotina de licença 
 * 
 * @syntax exibeDocumentacaoLicenca($idTpLicenca);
 * 
 * @param $idTipoLicenca integer NULL o id do tipo de licença
 */
    
    # Conecta 
    $pessoal = new Pessoal();
    
    # Pega os dados desse idClasse
    $select = "SELECT documentacao
                 FROM tbtipolicenca
                 WHERE idTpLicenca = $idTipoLicenca";

    $row = $pessoal->select($select,FALSE);    
    
    if(vazio($row[0])){
        echo "---";
    }else{
        # Botão
        $botao = new BotaoGrafico();
        $botao->set_label('');
        $botao->set_title("Documentação Necessária:&#013;".$row[0]);
        $botao->set_url("?fase=documentacao&idTpLicenca=".$idTipoLicenca);       
        $botao->set_imagem(PASTA_FIGURAS.'documentacao.png',20,20);
        $botao->show();
    }
    return;
}

##################################################################
    