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

Function dv($matricula) {
    if (vazio($matricula)) {
        return $matricula;
    } else {
        $ndig = 0;
        $npos = 0;

        switch (strlen($matricula)) {
            case 4:
                $matricula = "0" . $matricula;
                break;
            case 3:
                $matricula = "00" . $matricula;
                break;
            case 2:
                $matricula = "000" . $matricula;
                break;
        }

        # 5º Dígito

        $npos = substr($matricula, 4, 1);
        $npos = $npos * 2;
        if ($npos < 10) {
            $ndig = $ndig + $npos;
        } else {
            $ndig = $ndig + 1 + ($npos - 10);
        }

        # 4º Dígito

        $npos = substr($matricula, 3, 1);
        $ndig = $ndig + $npos;

        # 3º Dígito

        $npos = substr($matricula, 2, 1);
        $npos = $npos * 2;
        if ($npos < 10) {
            $ndig = $ndig + $npos;
        } else {
            $ndig = $ndig + 1 + ($npos - 10);
        }

        # 2º Dígito

        $npos = substr($matricula, 1, 1);
        $ndig = $ndig + $npos;

        # 1º Dígito

        $npos = substr($matricula, 0, 1);
        $npos = $npos * 2;
        if ($npos < 10) {
            $ndig = $ndig + $npos;
        } else {
            $ndig = $ndig + 1 + ($npos - 10);
        }

        # Finalmente o resultado
        $divisao = $ndig / 10;
        $int_div = intval($divisao);
        $fra_div = $divisao - $int_div;
        $mod = $fra_div * 10;

        if ($mod == 0) {
            $ndig = 0;
        } else {
            $ndig = 10 - $mod;
        }

        return $matricula . '-' . $ndig;
    }
}

###########################################################
/**
 * Função que retorna uma tabela com os dados do servidor
 * 
 * Obs esta função só existe para ser usada na classe modelo
 */

function get_DadosServidor($idServidor) {
    Grh::listaDadosServidor($idServidor);
}

###########################################################

function get_DadosFrequencia($idHistCessao) {
    $cessao = new Cessao();
    $dados = $cessao->getDados($idHistCessao);

    # Dados do Servidor
    Grh::listaDadosServidor($dados['idServidor']);

    # Dados da Cessão
    $cessao->exibeDados($idHistCessao);
}

##########################################################
/**
 * Função que formata as atribuições de um cargo
 * 
 */

function formataAtribuicao($texto) {

    $retorno = null;

    if (!empty($texto)) {
        $linhas = explode("- ", $texto);
        foreach ($linhas as $linha) {
            if (!empty($linha)) {
                $retorno .= "- {$linha}<br/>";
            }
        }
    }

    return $retorno;
}

###########################################################

function exibeDescricaoStatus($status) {
    /**
     * Retorna a descrição de um status de férias no on mouse over
     * 
     * @note Usado na rotina de férias e área de férias 
     * 
     * @syntax exibeDescricaoStatus($status);
     * 
     * @param $status string null o status das férias
     */
    $texto = "";
    switch ($status) {
        case "solicitada" :
            $texto = "Férias solicitadas pelo Servidor";
            break;
        
        case "fruindo" :
            $texto = "Férias que o servidor está fruindo nesse momento.";
            break;

        case "fruída" :
            $texto = "Férias já gozadas, desfrutadas. O sistema altera para fruídas todas as férias solicitadas em que a data de término já passou.";
            break;
    }
    echo "<abbr title='$texto'>$status</abbr>";
}

##################################################################
/**
 * Função que exibe um subtitulo na ficha cadastral
 * 
 */

function tituloRelatorio($texto) {
    $div = new Div("tituloFichaCadastral");
    $div->abre();
    echo $texto;
    $div->fecha();
}

##################################################################
/**
 * Função que exibe um subtitulo na ficha cadastral
 * 
 */

function tituloRelatorio2($texto) {
    p($texto, "pFormSaudeTitulo");
}

##########################################################
/**
 * Função que exibe um texto no final da escala de férias
 * 
 */

function textoEscalaFerias() {

    $intra = new Intra();
    $dataDev = $intra->get_variavel("dataDevolucaoGrh");

    br();
    $grid = new Grid();
    $grid->abreColuna(4);
    p("01- De acordo com o Decreto 2479 Art. 91 §2., é  proibida a acumulação de férias, salvo imperiosa necessidade de serviço, não podendo a acumulação, neste caso, abranger mais de dois períodos.", "justify", "f14");
    $grid->fechaColuna();
    $grid->abreColuna(4);
    p("02- Esta Escala deverá ser devolvida à Gerência de Recursos Humanos - GRH até o dia $dataDev. É imprescindível a assinatura do chefe imediato, e na impossibilidade do mesmo, deverá ser assinada pelo chefe superior.", "justify", "f14");
    $grid->fechaColuna();
    $grid->abreColuna(4);
    p("03- Eventuais alterações deverão ser comunicadas a esta gerência com antecedência mínima de 60 (sessenta dias) a contar da data de início das férias", "justify", "f14");
    $grid->fechaColuna();
    $grid->abreColuna(12);
    p("Data: _____/_____/_____&ensp;&ensp;&ensp;&ensp;Local: _____________________________&ensp;&ensp;&ensp;&ensp;Assinatura: ___________________________________", "f14");
    $grid->fechaColuna();
    $grid->fechaGrid();
}

##########################################################

function exibeProcesso($texto) {
    /**
     * Função que exibe os processo das licença de diversas tabelas
     * 
     * A tabela de licença de um servidor recebe informação de 2
     * tabelas unidas. Dessa forma foi criado uma codificação para
     * exibir o processo ora da tabela tbLicenca ora da tbservidor.
     * Essa função identifica e retorna o processo correto
     * 
     */
    # Divide o texto TIPO&ID
    $pedaco = explode("&", $texto);

    # Pega os pedaços
    $tipo = $pedaco[0];
    $id = $pedaco[1];

    # Inicia a variável de retorno
    $processo = null;

    # Execute uma rotina específica para cada tipo de licença
    switch ($tipo) {

        # Licença Prêmio
        case 6 :
            # Inicia a classe
            $licenca = new LicencaPremio();

            # Pega o processo 
            $processo = $licenca->get_numProcessoFruicao($id);
            break;

        # Licença Sem Vencimentos
        case 5 :
        case 8 :
        case 16 :
            # Inicia a classe
            $licenca = new LicencaSemVencimentos();

            # Pega os dados
            $dados = $licenca->get_dados($id);

            # Pega o processo 
            $processo = $dados["processo"];
            break;

        # Outras Licenças
        default:

            # Inicia a classe
            $pessoal = new Pessoal();

            # Pega o processo 
            $processo = $pessoal->get_licencaNumeroProcesso($pedaco[1]);
    }

    return $processo;
}

##########################################################

function acertaDataFerias($texto) {
    /**
     * Função que acerta o nome do mês e exibe junto do ano
     * 
     * Usado no relatório que lista solicitações de férias de um ano exercício
     * 
     */
    # Divide o texto mes/ano
    $pedaco = explode("/", $texto);

    $mes = get_nomeMes($pedaco[0]);

    $retorno = $mes . " / " . $pedaco[1];

    return $retorno;
}

##########################################################

function exibeDiasLicencaPremio($idServidor) {
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

    if ($numVinculos > 1) {
        # Carrega os valores do servidor ativo
        $diasPublicadosTotal = $licenca->get_numDiasPublicadosTotal($idServidor);
        $diasFruidosTotal = $licenca->get_numDiasFruidosTotal($idServidor);
        $diasDisponiveisTotal = $licenca->get_numDiasDisponiveisTotal($idServidor);
    }

    # Monta o retorno
    $retorno = "$diasPublicados | $diasFruidos | ";

    # Coloca em vermelho quando negativo
    if ($diasDisponiveis < 0) {
        $retorno .= "<span id='negativo'><B>" . $diasDisponiveis . "</B></span>";
    } else {
        $retorno .= $diasDisponiveis;
    }


    if ($numVinculos > 1) {
        # Pega o array dos vinculos
        $vinculos = $licenca->get_vinculosPremio($idServidor);

        foreach ($vinculos as $tt) {
            if ($tt[0] <> $idServidor) {
                $diasPublicados = $licenca->get_numDiasPublicados($tt[0]);
                $diasFruidos = $licenca->get_numDiasFruidos($tt[0]);
                $diasDisponiveis = $licenca->get_numDiasDisponiveis($tt[0]);

                $retorno .= "<br/>";
                $retorno .= "$diasPublicados | $diasFruidos | ";

                # Coloca em vermelho quando negativo
                if ($diasDisponiveis < 0) {
                    $retorno .= "<span id='negativo'><B>" . $diasDisponiveis . "</B></span>";
                } else {
                    $retorno .= $diasDisponiveis;
                }
            }
        }

        $retorno .= "<hr id='alerta'/>";
        $retorno .= "$diasPublicadosTotal | $diasFruidosTotal | ";

        # Coloca em vermelho quando negativo
        if ($diasDisponiveisTotal < 0) {
            $retorno .= "<span id='negativo'><B>" . $diasDisponiveisTotal . "</B></span>";
        } else {
            $retorno .= $diasDisponiveisTotal;
        }
    }

    return $retorno;
}

##########################################################

function exibeNumPublicacoesLicencaPremio($idServidor) {
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

    if ($numVinculos > 1) {
        # Carrega os valores do servidor ativo
        $numPublicacaoTotal = $licenca->get_numPublicacoesTotal($idServidor);
        $numPublicacaoPossivelTotal = $licenca->get_numPublicacoesPossiveisTotal($idServidor);
        $numPublicacaoFaltanteTotal = $numPublicacaoPossivelTotal - $numPublicacaoTotal;
    }


    # Monta o retorno
    $retorno = "$numPublicacao | $numPublicacaoPossivel | ";

    # Coloca em vermelho quando negativo
    if ($numPublicacaoFaltante < 0) {
        $retorno .= "<span id='negativo'><B>" . $numPublicacaoFaltante . "</B></span>";
    } else {
        $retorno .= $numPublicacaoFaltante;
    }

    if ($numVinculos > 1) {
        # Pega o array dos vinculos
        $vinculos = $licenca->get_vinculosPremio($idServidor);

        foreach ($vinculos as $tt) {
            if ($tt[0] <> $idServidor) {
                $numPublicacao = $licenca->get_numPublicacoes($tt[0]);
                $numPublicacaoPossivel = $licenca->get_numPublicacoesPossiveis($tt[0]);
                $numPublicacaoFaltante = $numPublicacaoPossivel - $numPublicacao;

                $retorno .= "<br/>";
                $retorno .= "$numPublicacao | $numPublicacaoPossivel | ";

                # Coloca em vermelho quando negativo
                if ($numPublicacaoFaltante < 0) {
                    $retorno .= "<span id='negativo'><B>" . $numPublicacaoFaltante . "</B></span>";
                } else {
                    $retorno .= $numPublicacaoFaltante;
                }
            }
        }

        $retorno .= "<hr id='alerta'/>";
        $retorno .= "$numPublicacaoTotal | $numPublicacaoPossivelTotal | ";

        # Coloca em vermelho quando negativo
        if ($numPublicacaoFaltanteTotal < 0) {
            $retorno .= "<span id='negativo'><B>" . $numPublicacaoFaltanteTotal . "</B></span>";
        } else {
            $retorno .= $numPublicacaoFaltanteTotal;
        }
    }

    return $retorno;
}

##########################################################

function exibePrazoParaGozoEscalaFerias($texto) {
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
    $retorno = null;

    # Se o ano pesquisado for o mesmo da admissão
    if ($anoPesquisado == $anoAdmissao) {
        $retorno = "Ainda não fez um ano !";
    }

    # Se o ano pesquisado for anterior da admissão
    if ($anoPesquisado < $anoAdmissao) {
        $retorno = "Ainda não tinha sido admitido !!";
    }

    # Se o ano pesquisado for o imediatamente posterior
    if ($anoPesquisado == ($anoAdmissao + 1)) {

        # Pega o dia da admissão
        $dia = day($dtAdmissao);

        # Pega o mês da admissão
        $mes = month($dtAdmissao);

        $dataInicial = $dia . "/" . $mes;
        $dataFinal = "31/12";
        $retorno = $dataInicial . " - " . $dataFinal;
    }

    # Se o ano pesquisado for depois da admissão
    if ($anoPesquisado > ($anoAdmissao + 1)) {
        $dataInicial = "01/01";
        $dataFinal = "31/12";
        $retorno = $dataInicial . " - " . $dataFinal;
    }

    return $retorno;
}

##########################################################

function exibeFeriasPendentes($texto) {
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
    $retorno = null;
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
    for ($i = $anoAdmissao + 1; $i <= $anoPesquisado; $i++) {
        if ($i >= $feriasCadastradas) {




















            #########################################33
            $dias = $pessoal->get_feriasSomaDias($i, $idServidor);

            # resolve temporariamente o problema de Simone Flores
            if (($idServidor == 15 and $i == 2020) OR ($idServidor == 15 and $i == 2021)) {
                $dias = 30;
            }

            # Transforma o nullo em zero
            if (is_null($dias)) {
                $dias = 0;
            }

            # Verifica se e o ano atual e informa que nao tem mais ferias
            # a serem tiradas esse ano quando ja tirou 30 dias
            if ($i == $anoPesquisado) {
                if ($dias > 0) {
                    if ($linhas > 0) {
                        $retorno .= "<br/>";
                    }

                    $retorno .= "Já solicitou os $dias dias de " . $anoPesquisado;
                    $linhas++;
                }
            } else {
                # Verifica se tem pendencia 
                if ($dias < 30) {
                    $pendencia = 30 - $dias;

                    if ($linhas > 0) {
                        $retorno .= "<br/>";
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

function consertaUf($uf) {

    /*
     * Funçao que conserta um campo de unidade federal que esta na forma de 
     * string e transforma em inteiro com o id da tabela tbestado
     */

    if (!is_integer($uf)) {
        $select = 'SELECT idEstado
                     FROM tbestado
                    WHERE uf = ' . $uf;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        $uf = $row[0];
    }

    return $uf;
}

###########################################################
/**
 * Função que retorna a situaçao do servidor e os afastamentos
 * Obs esta função só existe para ser usada na classe modelo
 */

function get_situacao($idServidor) {
    $pessoal = new Pessoal();

    # Preenche retorno com a situação
    $retorno = $pessoal->get_situacao($idServidor);

    # Somente exibe dados da cessão ou de afastamentos se o servidor for ativo
    if ($retorno == "Ativo") {
        # Verifica se está cedido
        if ($pessoal->emCessao($idServidor)) {
            $retorno .= "<br/><span title='{$pessoal->get_orgaoCedido($idServidor)}' class='primary label'>Cedido</span>";
        }

        # Pega os afastamentos
        $verifica = new VerificaAfastamentos($idServidor);

        if ($verifica->verifica()) {
            $retorno .= "<br/><span title='{$verifica->getDetalhe()}' class='warning label'>{$verifica->getAfastamento()}</span>";
        }

        # Verifica se está em vias de aposentadoria Compulsória
        $idade = $pessoal->get_idade($idServidor);
        $idPerfil = $pessoal->get_idPerfil($idServidor);

        if ($idade >= 75 AND $idPerfil == 1) {
            $retorno .= "<br/><span title='Servidor com {$idade} anos. Deverá aposentar Compulsoriamente.' class='primary label'>Aguardando<br/>Aposentadoria<br/>Compulsória</span>";
        }
    }

    return $retorno;
}

###########################################################
/**
 * Função que retorna a situaçao do servidor mais informaçoes de ferias, licença, etc
 * Obs esta função só existe para ser usada na classe modelo
 */

function get_situacaoRel($idServidor) {
    $pessoal = new Pessoal();
    $retorno = $pessoal->get_situacao($idServidor);
    return $retorno;
}

###########################################################

/**
 * Função que retorna o servidor cadastrado para o atyendimento do balcao no dia informado
 * Obs esta função só existe para ser usada na rotina de controle do atendimento do balcão
 */
function get_servidorBalcao($ano, $mes, $dia, $turno) {
    $pessoal = new Pessoal();

    $select = 'SELECT idServidorManha,
                      idServidorManhaOnline,
                      idServidorTarde,
                      idServidorTardeOnline
                FROM tbbalcao
               WHERE ano = ' . $ano . '
                 AND mes = ' . $mes . ' 
                 AND dia = ' . $dia;

    $row = $pessoal->select($select, false);
    $count = $pessoal->count($select, false);

    if ($count == 0) {
        return null;
    } else {
        if ($turno == "m") {
            if (empty($row[0])) {
                return null;
            } else {
                return $row[0];
            }
        } elseif ($turno == "mo") {
            if (vazio($row[1])) {
                return null;
            } else {
                return $row[1];
            }
        } elseif ($turno == "t") {
            if (vazio($row[2])) {
                return null;
            } else {
                return $row[2];
            }
        } elseif ($turno == "to") {
            if (vazio($row[3])) {
                return null;
            } else {
                return $row[3];
            }
        }
    }
}

###########################################################

/**
 * Função que retorna o idBalcao de um dia específico para saber se será update ou insert
 * Obs esta função só existe para ser usada na rotina de controle do atendimento do balcão
 */
function get_idBalcao($ano, $mes, $dia) {
    $pessoal = new Pessoal();

    $select = 'SELECT idBalcao
                FROM tbbalcao
               WHERE ano = ' . $ano . '
                 AND mes = ' . $mes . ' 
                 AND dia = ' . $dia;

    $row = $pessoal->select($select, false);
    return $row[0];
}

###########################################################

/**
 * Função que retorna o primeiro nome de um idServidor
 * Obs esta função só existe para ser usada na rotina de controle do atendimento do balcão e chama a classe homonima
 */
function get_nomeSimples($nome) {

    # trata o nome para pegar somente o primeiro nome
    $parte = explode(" ", $nome);

    # Verifica se e nome composto
    $nomesCompostos = array("Ana", "Maria", "Andre", "André");

    # Verifica se o nome em questao e composto e insere o segundo nome
    if (in_array($parte[0], $nomesCompostos)) {
        $nomeSimples = $parte[0] . " " . $parte[1];
    } else {
        $nomeSimples = $parte[0];
    }

    return $nomeSimples;
}

##################################################################

function statusReducao($arquivado) {
    /**
     * Exibe na tabela de redução de carga horária imagem se está arquivado ou não
     * 
     * @note Usado na rotina de cadastro de redução de carga horária
     * 
     * @syntax statusReducao($arquivado);
     * 
     * @param $arquivado int null se foi arquivado ou não
     */
    if ($arquivado) {
        $figura = new Imagem(PASTA_FIGURAS . 'arquivo.png', 'Arquivado', 30, 30);
        $figura->show();
    } else {
        echo "";
    }
}

##########################################################

function idMatricula($idServidor) {
    /**
     * Função exibe o id e a matrícula de um servidor
     * 
     * Usado quando se deseja nas tabelas exibir as duas informações na mesma coluna
     */
    $pessoal = new Pessoal();

    $select = 'SELECT idFuncional,
                          matricula
                     FROM tbservidor
                   WHERE idServidor = ' . $idServidor;

    $row = $pessoal->select($select, false);
    $matricula = dv($row[1]);

    if (vazio($row[0]) OR vazio($matricula)) {
        $retorno = $row[0] . $matricula;
    } else {
        $retorno = $row[0] . " / " . $matricula;
    }

    return $retorno;
}

##########################################################

function marcaSePassou($data) {
    /**
     * Função exibe a data com uma marca se a data já passou
     * 
     * Usado na rotina da área de aposentadoria
     */
    if (jaPassou($data)) {
        label($data);
    } else {
        echo $data;
    }
}

##########################################################

function exibeLicencaPremio($idServidor) {
    /**
     * Função exibe as licenças preomio de um servidor
     * 
     * Usado na rotina da área de Licença Premio
     */
    $licenca = new LicencaPremio();
    $licenca->exibeLicencaPremio($idServidor);
}

##########################################################

function exibeFoto($idPessoa) {
    /**
     * Função exibe a foto do servidor 
     * 
     * Usado na rotina da área de fotografia
     */
    $foto = new ExibeFoto();
    $foto->set_fotoLargura(80);
    $foto->set_fotoAltura(100);
    $foto->set_url('?fase=exibeFoto&idPessoa=' . $idPessoa);
    $foto->show($idPessoa);
}

##########################################################

function linkExibeVaga($idConcurso) {
    /**
     * Exibe um link para as vagas de um concurso
     * 
     * @param $idConcurso integer null O id do Concurso
     * 
     * @syntax $plano->linkExibeVaga($idConcurso);
     */
    # Verifica o idConcurso
    if (Vazio($idConcurso)) {
        alert("É necessario informar o id do Concurso");
    } else {
        # Pega os dados do concurso
        $concurso = new Concurso($idConcurso);
        $dados = $concurso->get_dados();

        # Varifica se o tipo do concurso (2 - Professores ou 1 - Adm & técnico)
        $tipo = $dados["tipo"];

        if ($tipo == 2) {
            # Monta o link
            $link = new Link(null, "cadastroConcurso.php?fase=listaVagasConcurso&id=$idConcurso", "Exibe as vagas");
            $link->set_imagem(PASTA_FIGURAS_GERAIS . "olho.png", 20, 20);
            $link->show();
        } else {
            echo "-";
        }
    }
}

##########################################################

function exibeDadosSalarioAtual($idServidor) {
    /**
     * Função exibe dados do salario atual do servidor
     * 
     * Usado no relatório financeiro de progressao
     */
    # Conecta 
    $pessoal = new Pessoal();

    # Pega o idClasse so salário atual
    $idClasse = $pessoal->get_idClasseServidor($idServidor);

    if (vazio($idClasse)) {
        return null;
    } else {

        # Pega os dados desse idClasse
        $select = "SELECT faixa,
                              valor,
                              tbplano.numdecreto
                         FROM tbclasse LEFT JOIN tbplano USING (idPlano)
                         WHERE idClasse = $idClasse";

        $row = $pessoal->select($select, false);

        $return = $row[0] . " - " . $row[1] . "<br/>" . $row[2];
        return $return;
    }
}

###########################################################

function exibeDocumentacaoLicenca($idTipoLicenca) {
    /**
     * Exibe um quadro com a documentação e a observação desse tipo de licença
     * 
     * @note Usado na rotina de licença 
     * 
     * @syntax exibeDocumentacaoLicenca($idTpLicenca);
     * 
     * @param $idTipoLicenca integer null o id do tipo de licença
     */
    # Conecta 
    $pessoal = new Pessoal();

    # Pega os dados desse idClasse
    $select = "SELECT documentacao,
                      obs,
                      nome
                 FROM tbtipolicenca
                 WHERE idTpLicenca = $idTipoLicenca";

    $row = $pessoal->select($select, false);

    p($row[2], "f22", "center");

    # Div onde vai exibir o procedimento
    $div = new Div("divNota");
    $div->abre();

    tituloTable("Documentação Necessária");
    $painel = new Callout();
    $painel->abre();

    p(trataNulo($row[0]));

    $painel->fecha();

    tituloTable("Observação");
    $painel = new Callout();
    $painel->abre();

    p(trataNulo($row[1]));

    $painel->fecha();
    $div->fecha();
    return;
}

###########################################################

function exibeBotaoDocumentacaoLicenca($idTipoLicenca) {
    /**
     * Exibe um quadro com a documentação e a observação desse tipo de licença
     * 
     * @note Usado na rotina de licença 
     * 
     * @syntax exibeDocumentacaoLicenca($idTpLicenca);
     * 
     * @param $idTipoLicenca integer null o id do tipo de licença
     */
    # Conecta 
    $pessoal = new Pessoal();

    # Pega os dados desse idClasse
    $select = "SELECT documentacao
                 FROM tbtipolicenca
                 WHERE idTpLicenca = $idTipoLicenca";

    $row = $pessoal->select($select, false);

    if (vazio($row[0])) {
        echo "---";
    } else {
        # Botão
        $botao = new BotaoGrafico();
        $botao->set_label('');
        $botao->set_title($row[0]);
        $botao->set_url("?fase=documentacao&idTpLicenca=" . $idTipoLicenca);
        $botao->set_imagem(PASTA_FIGURAS . 'documentacao.png', 20, 20);
        $botao->show();
    }
    return;
}

###########################################################

function exibeRegraStatusLSV() {
    /**
     * Exibe um quadro com a regras da mudança de status de uma lSV
     * 
     * @note Usado na rotina de licença Sem Vencimentos
     * 
     * @syntax exibeRegraStatusLSV();
     */
    # Abre a div para a invisibilidade
    $div = new Div("divRegrasLsv");
    $div->abre();

    $conteudo = array(array("Em Aberto", "Quando a data de publicação estiver vazia"),
        array("Vigente", "Quando a data de publicação estiver preenchida e o servidor ainda não retornou."),
        array("Aguardando CRP", "Quando a data de retorno do servidor já passou e ainda não entregou o CRP"),
        array("Arquivado", "Quando a data de retorno do servidor já passou e o campo CRP estiver SIM"),
        array("INCOMPLETO", "Quando a data de solicitação não foi preenchida"));

    # Exibe em forma de tabela
    $tabela = new Tabela();
    #$tabela->set_titulo("Regras de mudança do Status");
    $tabela->set_conteudo($conteudo);
    $tabela->set_label(array("", ""));
    $tabela->set_width(array(20, 80));
    $tabela->set_align(array("left", "left"));
    $tabela->set_totalRegistro(false);
    $tabela->show();

    $div->fecha();
    return;
}

###########################################################

function exibeDocumentoPasta($idPasta) {
    /**
     * Exibe umbotão que leva a um documento ou processo n a pasta funcional
     * 
     * @note Usado na rotina de pasta funcional 
     * 
     * @syntax exibeDocumentoPasta($idPasta);
     * 
     * @param $idPasta integer null o id da pasta
     */
    # Define a pasta
    $pasta = PASTA_FUNCIONAL;

    # Monta o arquivo
    $arquivo = $pasta . $idPasta . ".pdf";

    # Procura o arquivo
    if (file_exists($arquivo)) {

        # Define as variáveis
        $figura = 'documentacao.png';

        # Monta o botão
        $botao = new BotaoGrafico();
        $botao->set_url($arquivo);
        $botao->set_title("Clique para exibir o documento");
        $botao->set_target('_blank');
        $botao->set_imagem(PASTA_FIGURAS . 'documentacao.png', 20, 20);
        $botao->show();
    }

    return;
}

###########################################################

function exibeProcessoPasta($idPasta) {
    /**
     * Exibe umbotão que leva a um documento ou processo n a pasta funcional
     * 
     * @note Usado na rotina de pasta funcional 
     * 
     * @syntax exibeDocumentoPasta($idPasta);
     * 
     * @param $idPasta integer null o id da pasta
     */
    # Define a pasta
    $pasta = PASTA_FUNCIONAL;

    # Monta o arquivo
    $arquivo = $pasta . $idPasta . ".pdf";

    # Procura o arquivo
    if (file_exists($arquivo)) {

        # Define as variáveis
        $figura = 'documentacao.png';

        # Monta o botão
        $botao = new BotaoGrafico();
        $botao->set_url($arquivo);
        $botao->set_title("Clique para exibir o processo");
        $botao->set_target('_blank');
        $botao->set_imagem(PASTA_FIGURAS . 'processo.png', 20, 20);
        $botao->show();
    }

    return;
}

##########################################################

/**
 * Função que retorna uma tabela com os dados do servidor e das ocorrencias da cessao 
 */
function get_DadosServidorCessao($idServidor) {
    Grh::listaDadosServidor($idServidor);

    # Ocorrências
    $metodos = get_class_methods('Checkup');
    $ocorrencia = new Checkup(false);

    foreach ($metodos as $nomeMetodo) {
        if ($nomeMetodo == 'get_servidorCedidoLotacaoErrada' OR $nomeMetodo == 'get_servidorCedidoDataExpirada') {

            $texto[] = $ocorrencia->$nomeMetodo($idServidor);
        }
    }

    # Verifica se não está vazio
    if (!empty(array_filter($texto))) {

        $painel = new Callout("warning");
        $painel->abre();

        # Percorre o array 
        foreach ($texto as $mm) {
            if (!empty($mm)) {
                p("- " . $mm, "exibeOcorrencia");
            }
        }
        $painel->fecha();
    }
}

##########################################################

/**
 * Função que retorna uma tabela com as declarações de acumulação positivas de um servidor 
 */
function exibeDeclaracaoAcumulacao($idServidor) {
    # Abre a div para a invisibilidade
    $div = new Div("divRegrasLsv");
    $div->abre();

    # Conecta 
    $pessoal = new Pessoal();

    # select da lista
    $select = "SELECT anoReferencia,
                       dtEntrega, 
                       IF(acumula,'<span id=\'vermelho\'>SIM</span>','<span id=\'verde\'>Não</span>'),
                       CONCAT('SEI-',processo),
                       obs,
                       idAcumulacaoDeclaracao
                  FROM tbacumulacaodeclaracao 
                WHERE idServidor = {$idServidor}
                ORDER BY anoReferencia desc";

    $conteudo = $pessoal->select($select);

    # Exibe em forma de tabela
    $tabela = new Tabela();
    $tabela->set_titulo("Declarações de Acumulação");
    $tabela->set_conteudo($conteudo);

    $tabela->set_label(array("Referência", "Entregue em", "Declarou Acumular", "Processo", "Obs"));
    $tabela->set_width(array(10, 15, 10, 20, 35));
    $tabela->set_align(array("center", "center", "center", "left", "left"));
    $tabela->set_funcao(array(null, "date_to_php"));

    $tabela->set_formatacaoCondicional(array(
        array('coluna' => 2,
            'valor' => 'SIM',
            'operador' => '=',
            'id' => 'problemas')));

    $tabela->set_totalRegistro(false);
    $tabela->set_mensagemTabelaVazia("Este servidor não possui declarações de acumulação cadastradas!");
    $tabela->show();

    $div->fecha();
    return;
}

##########################################################

/**
 * Função que retorna uma tabela com as declarações de acumulação positivas de um servidor 
 */
function exibeProcessosAcumulacao($idServidor) {
    # Abre a div para a invisibilidade
    $div = new Div("divRegrasLsv");
    $div->abre();

    # Conecta 
    $pessoal = new Pessoal();

    # select da lista
    $select = "SELECT CASE conclusao
                         WHEN 1 THEN 'Pendente'
                         WHEN 2 THEN 'Resolvido'
                         ELSE '--'
                      END,
                      idAcumulacao,                                     
                      idAcumulacao,
                      idAcumulacao,    
                      idAcumulacao
                 FROM tbacumulacao
                WHERE idServidor = {$idServidor}
             ORDER BY tipoProcesso, dtProcesso";

    $conteudo = $pessoal->select($select);

    # Exibe em forma de tabela
    $tabela = new Tabela();
    $tabela->set_titulo("Processo de Acumulação");
    $tabela->set_conteudo($conteudo);

    $tabela->set_label(["Conclusão", "Resultado", "Data da<br/>Publicação", "Processo", "Dados do Segundo Vínculo"]);
    $tabela->set_align(["center", "center", "center", "center", "left", "left"]);
    $tabela->set_classe([null, "Acumulacao", "Acumulacao", "Acumulacao", "Acumulacao"]);
    $tabela->set_metodo([null, "get_resultado", "exibePublicacao", "exibeProcesso", "exibeDadosOutroVinculo"]);

    $tabela->set_formatacaoCondicional(array(
        array('coluna' => 0,
            'valor' => 'Resolvido',
            'operador' => '=',
            'id' => 'emAberto'),
        array('coluna' => 0,
            'valor' => 'Pendente',
            'operador' => '=',
            'id' => 'alerta')
    ));

    $tabela->set_totalRegistro(false);
    $tabela->set_mensagemTabelaVazia("Este servidor não possui processos de acumulação cadastrados!");
    $tabela->show();

    $div->fecha();
    return;
}

##########################################################

function exibeObsLicenca($texto) {
    /**
     * Função que exibe as observações das licenças
     * 
     * A tabela de licença de um servidor recebe informação de várias
     * tabelas unidas. Dessa forma foi criado uma codificação para
     * exibir a obs da tabela específica.
     * Essa função identifica e retorna a obs correta
     * 
     */
    # Verifica o que é texto
    if (strpos($texto, "&") === false) {
        # é uma data
        return date_to_php($texto);
    } else {
        # Divide o texto TIPO&ID
        $pedaco = explode("&", $texto);

        # Pega os pedaços
        $tipo = $pedaco[0];
        $id = $pedaco[1];

        # Inicia a variável de retorno
        $processo = null;

        # Execute uma rotina específica para cada tipo de licença
        switch ($tipo) {

            # Licença Prêmio
            case "tblicencapremio" :
                # Inicia a classe
                $licenca = new LicencaPremio();

                # Exibe a Obs
                $licenca->exibeObs($id);
                break;

            # Licença Sem Vencimentos
            case "tblicencasemvencimentos" :
                # Inicia a classe
                $licenca = new LicencaSemVencimentos();

                # Exibe a Obs
                $licenca->exibeObs($id);
                break;

            # Outras Licenças
            case "tblicenca" :

                # Inicia a classe
                $licenca = new Licenca();

                # Exibe a Obs
                $licenca->exibeObs($id);
                break;

            # Férias
            case "tbferias" :
                # Inicia a classe
                $ferias = new Ferias();

                # Exibe a Obs
                $ferias->exibeObs($id);
                break;

            # Atestado ou faltas abonadas
            case "tbatestado" :
                # Inicia a classe
                $atestado = new Atestado();

                # Exibe a Obs
                $atestado->exibeObs($id);
                break;

            # Folga TRE
            case "tbfolga" :
                # Inicia a classe
                $folga = new FolgaTre();

                # Exibe a Obs
                $folga->exibeObs($id);
                break;

            default :
                echo "---";
                break;
        }
    }
}

###########################################################

/**
 * Função que retorna o afastamento atual de um servidor (se houver)
 * Obs esta função acessa a classe verifica afastamento
 */
function exibeSituacaoAuxilioTransporte($dados) {

    # Verifica se o id foi informado
    if (empty($dados)) {
        return null;
    } else {

        # Separa os dados
        $valores = explode("-", $dados);
        $idServidor = $valores[0];

        $transporte = new AuxilioTransporte();
        $transporte->exibeSituacao($valores[0], $valores[1], $valores[2]);
    }
}

###########################################################

/**
 * Função que retorna o afastamento atual de um servidor (se houver)
 * Obs esta função acessa a classe verifica afastamento
 */
function exibeRecebeuAuxilioTransporte($dados) {

    # Verifica se o id foi informado
    if (empty($dados)) {
        return null;
    } else {

        # Separa os dados
        $valores = explode("-", $dados);
        $idServidor = $valores[0];

        # Verifica se houve upload
        $auxilio = new AuxilioTransporte();
        $houveUpload = $auxilio->houveUpload($valores[1], $valores[2]);

        if ($houveUpload) {
            $auxilio->exibeRecebeu($valores[0], $valores[1], $valores[2]);
        } else {
            return null;
        }
    }
}

##################################################################

function array_sort($array, $on, $order = SORT_ASC) {
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

###########################################################

/**
 * Função que retorna o afastamento atual de um servidor (se houver)
 * Obs esta função acessa a classe verifica afastamento
 */
function exibeDocumentosDeclaracaoAcumulacao($idServidor) {

    # Colunas
    $grid = new Grid();
    $grid->abreColuna(8);

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    tituloTable("Despachos:");
    br();

    $menu = new Menu();
    #$menu->add_item('titulo','Documentos');

    $menu->add_item("linkWindow", "Despacho: Solicitação de Declaração Pendente", "?fase=despachoDeclaracaoPendente");
    $menu->add_item("linkWindow", "Despacho: Solicitação de Modelo Padrão", "../grhRelatorios/despacho.Acumulacao.SolicitaModeloPadrao.php");
    $menu->add_item("linkWindow", "Despacho: Solicitação de Correção", "?fase=despachoCorrecao");
    $menu->add_item("linkWindow", "Despacho: Informação sobre Processo de Análise", "../grhRelatorios/despacho.Acumulacao.DeclaracaoAnalise.php");
    $menu->add_item("linkWindow", "Despacho para Servidor com Cargo de Confiança/Função Gratificada", "../grhRelatorios/despacho.Acumulacao.DeclaracaoConfianca.php");
    $menu->add_item("linkWindow", "Despacho de Conclusão Temporária", "../grhRelatorios/despacho.Acumulacao.ConclusaoTemporaria.php");

    $menu->show();

    $grid->fechaColuna();
    $grid->abreColuna(4);

    #######################################################
    # Vinculos do servidor
    $numVinculos = $pessoal->get_numVinculos($idServidor);

    tituloTable("Outros Vínculos:");
    br();

    # Número de Vinculos
    if ($numVinculos > 1) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Exibe os vinculos
        $vinculos = $pessoal->get_vinculos($idServidor);

        # Percorre os vínculos
        foreach ($vinculos as $rr) {

            # Descarta o vinculo em tela
            if ($rr[0] <> $idServidor) {
                $dtAdm = $pessoal->get_dtAdmissao($rr[0]);
                $dtSai = $pessoal->get_dtSaida($rr[0]);
                $perfil = $pessoal->get_perfilSimples($rr[0]);
                $cargo = $pessoal->get_cargoSimples($rr[0]);
                $idSituacao = $pessoal->get_idSituacao($rr[0]);

                # Quando o cargo for null
                if (!empty($cargo)) {
                    $cargo = "Cargo: {$cargo}";
                }

                # Cria um motivo Ativo
                if ($idSituacao == 1) {
                    $motivo = "Ativo";
                } else {
                    $motivo = $pessoal->get_motivo($rr[0]) . " - " . $pessoal->get_dtAdmissao($rr[0]) . " - " . $pessoal->get_dtSaida($rr[0]);
                }

                plista($cargo, $perfil, $motivo);
                hr();
            }
        }
    } else {
        br();
        p("Não há outros vinculos deste servidor na Uenf.", "center", "f12");
        br(3);
    }

    $grid->fechaColuna();
    $grid->fechaGrid();
}

###########################################################

function trataProcesso($processo = null) {
    # Verfica null
    if (empty($processo)) {
        return null;
    }

    # Retira o ponto
    $processo = str_replace(".", "", $processo);

    # Ano com 4 digitos
    $posicao = strripos($processo, "/");
    $ano = substr($processo, $posicao + 1);
    $resto = substr($processo, 0, $posicao);

    # Verifica o tamanho do ano
    if (strlen($ano) < 4) {
        if ($ano > 50) {
            $ano = "19" . $ano;
        } else {
            $ano = "20" . $ano;
        }

        # Monta o processo novamente
        $processo = "{$resto}/{$ano}";
    }

    return $processo;
}

###########################################################

function exibeAfastamentoAtual($idServidor = null) {
    # Verfica null
    if (empty($idServidor)) {
        return null;
    }

    $verifica = new VerificaAfastamentos($idServidor);
    $verifica->verifica();

    return $verifica->getDetalhe() . "<br/>" . $verifica->getPeriodo();
}

###########################################################