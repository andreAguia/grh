<?php

class Aposentadoria {
    /**
     * Abriga as várias rotina referentes a aposentadoria do servidor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */

    /**
     * Método exibeMenu
     * Exibe menu da área de aposentadoria
     * 
     * @param string $itemBold o item do menu para colocar o bold no menu
     */
    ##################################################

    public function exibeMenu($itemBold = null) {

        # Pega as idades de aposentadoria
        $intra = new Intra();
        $idadeAposentMasculino = $intra->get_variavel("aposentadoria.integral.idade.masculino");
        $idadeAposentFeminino = $intra->get_variavel("aposentadoria.integral.idade.feminino");

        tituloTable("Menu");
        $menu = new Menu("menuAposentadoria", $itemBold);

        $menu->add_item("titulo", "Servidores Aposentados");

        $menu->add_item("link", "Aposentados por Ano", "areaAposentadoria_aposentadosPorAno.php", "Servidores Aposentados por Ano de Aposentadoria");
        $menu->add_item("link", "Aposentados por Tipo", "areaAposentadoria_aposentadosPorTipo.php", "Servidores Aposentados por Tipo de Aposentadoria");
        $menu->add_item("link", "Estatística", "areaAposentadoria_aposentadosEstatistica.php", "Estatística dos Servidores Aposentados");

        $menu->add_item("titulo", "Previsão");
        $menu->add_item("titulo1", "Regras Permanentes");
        $menu->add_item("link", "Por Idade e Contribuição", "areaAposentadoria_previsaoPorIdadePorContribuicao.php", "Aposentadoria voluntária por idade e tempo de contribuição");
        $menu->add_item("link", "Por Idade", "areaAposentadoria_previsaoPorIdade.php", "Aposentadoria voluntária por idade");
        $menu->add_item("link", "Compulsória", "areaAposentadoria_previsaoCompulsoria.php", "Previsão de aposentadoria compulsória");
        $menu->add_item("link", "Compulsória por Ano", "areaAposentadoria_previsaoCompulsoriaPorAno.php", "Previsão de aposentadoria compulsória por ano");
        #$menu->add_item("link", "Configuração Compulsória", "areaAposentadoria.php?fase=configuracaoCompulsoria", "Configuração");

        $menu->add_item("titulo1", "Regras de Transição");
        $menu->add_item("link", "EC nº 41/2003", "areaAposentadoria_previsaoTransicaoEC41.php", "Regras de transição - EC nº 41/2003");
        $menu->add_item("link", "EC nº 47/2005", "areaAposentadoria_previsaoTransicaoEC47.php", "Regras de transição - EC nº 47/2005");
        $menu->show();
    }

    ##################################################

    public function exibeMenuServidor($itemBold = null) {

        tituloTable("Menu");
        $menu = new Menu("menuAposentadoria", $itemBold);

        $menu->add_item("titulo", "Dados do Servidor");
        $menu->add_item("link", "Resumo Geral", "?", "resumo dos dados do servidor");
        $menu->add_item("link", "Tempo Averbado", "?fase=averbado", "Exibe o tempo averbado com detalhes");
        $menu->add_item("link", "Vínculos Anteriores", "?fase=vinculos", "Exibe os vínculos anteriores do servidor na Uenf");
        $menu->add_item("link", "Afastamentos", "?fase=afastamentos", "Exibe todos os afastamentos do servidor");

        $menu->add_item("titulo", "Regras Permanentes");
        $menu->add_item("link", "Aposentadoria Voluntária", "?fase=permanenteVoluntaria");
        $menu->add_item("link", "Aposentadoria Compulsória", "?fase=permanenteCompulsoria");
        $menu->add_item("link", "Incap. Permanente", "?fase=incapacidadePermanente");
        $menu->add_item("link", "Incap. Permanente - Acid.Trabalho", "?fase=incapacidadeAcidenteAT");

        $menu->add_item("titulo", "Regras de Transição");
        $menu->add_item("titulo1", "Regra dos Pontos");
        $menu->add_item("link", "Integralidade e Paridade", "?fase=pontosIntegral");
        $menu->add_item("link", "Média da Lei Federal nº 10.887/2004", "?fase=pontosMedia");
        $menu->add_item("titulo1", "Regra do Pedágio");
        $menu->add_item("link", "Integralidade e Paridade", "?fase=pedagioIntegral");
        $menu->add_item("link", "Redutor de Idade", "?fase=pedagioRedutor");
        $menu->add_item("link", "Média da Lei Federal nº 10.887/2004", "?fase=pedagioMedia");

        $menu->add_item("titulo", "Direito Adquirido");
        $menu->add_item("link", "Art. 40, §1º, III, alínea a", "?fase=idadeContribuicao", "Artigo 40 - Aposentadoria voluntária por idade e tempo de contribuição");
        $menu->add_item("link", "Art. 40, §1º, III, alínea b", "?fase=idade", "Aposentadoria voluntária por idade");
        #$menu->add_item("link", "Artigo 2º da EC nº 41/2003", "?fase=41_2", "Regras de transição - Artigo 2º da EC nº 41/2003");
        $menu->add_item("link", "Artigo 6º da EC nº 41/2003", "?fase=41_6", "Regras de transição - Artigo 6º da EC nº 41/2003");
        $menu->add_item("link", "Artigo 3º da EC nº 47/2005", "?fase=47_3", "Regras de transição - Artigo 3º da EC nº 47/2005");
        #$menu->add_item("link", "Artigo 3º da EC nº 41/2003", "?fase=41_3", "Regras de transição - Artigo 3º da EC nº 47/2005");

        $menu->add_item("titulo", "Documentação");

        # Banco de dados
        $pessoal = new Pessoal();

        # Pega os projetos cadastrados
        $select = 'SELECT idMenuDocumentos,
                          categoria,
                          texto,
                          title
                     FROM tbmenudocumentos
                     WHERE categoria = "Regras de Aposentadoria"
                  ORDER BY categoria, texto';

        $dados = $pessoal->select($select);
        $num = $pessoal->count($select);

        # Verifica se tem itens no menu
        if ($num > 0) {
            # Percorre o array 
            foreach ($dados as $valor) {

                if (empty($valor["title"])) {
                    $title = $valor["texto"];
                } else {
                    $title = $valor["title"];
                }

                # Verifica qual documento
                $arquivoDocumento = PASTA_DOCUMENTOS . $valor["idMenuDocumentos"] . ".pdf";
                if (file_exists($arquivoDocumento)) {
                    # Caso seja PDF abre uma janela com o pdf
                    $menu->add_item('linkWindow', $valor["texto"], PASTA_DOCUMENTOS . $valor["idMenuDocumentos"] . '.pdf', $title);
                } else {
                    # Caso seja um .doc, somente faz o download
                    $menu->add_item('link', $valor["texto"], PASTA_DOCUMENTOS . $valor["idMenuDocumentos"] . '.doc', $title);
                }
            }
        }

        $menu->add_item("linkWindow", "Regras Vigentes a partir de 01/01/2022", "https://www.rioprevidencia.rj.gov.br/PortalRP/Servicos/RegrasdeAposentadoria/apos2022/index.htm");
        $menu->add_item("linkWindow", "Regras Vigentes até 31/12/2021", "https://www.rioprevidencia.rj.gov.br/PortalRP/Servicos/RegrasdeAposentadoria/ate2021/index.htm");

        $menu->show();
    }

    ############################################################################

    function get_numServidoresAposentados() {

        /**
         * informa o número de Servidores Ativos
         * 
         * @param integer $idPessoa do servidor
         */
        $select = 'SELECT idServidor
                     FROM tbservidor
                    WHERE situacao = 2
                    AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)';

        $pessoal = new Pessoal();
        $count = $pessoal->count($select);
        return $count;
    }

    ############################################################################ 

    function exibeAposentadosPorAno($parametroAno = null, $fase = null) {

        /**
         * Exibe tabela com os aposentados por ano de aposentadoria
         * 
         * @param integer $parametroAno da aposentadoria
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Trata os parametros
        if (empty($parametroAno)) {
            $parametroAno = date('Y');
        }

        if (empty($fase)) {
            $fase = "editar";
        }

        # Monta o select
        $select = 'SELECT year(dtDemissao),
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.dtAdmissao,
                          tbservidor.dtDemissao,
                          tbmotivo.motivo
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                    WHERE YEAR(tbservidor.dtDemissao) = "' . $parametroAno . '"
                      AND situacao = 2
                      AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                 ORDER BY dtDemissao';

        $result = $pessoal->select($select);

        $tabela = new Tabela();
        $tabela->set_titulo('Servidores Aposentados em ' . $parametroAno);
        $tabela->set_subtitulo('Ordenado pela Data de Saída');

        $tabela->set_label(['Ano', 'IdFuncional<br/>Matrícula', 'Servidor', 'Admissão', 'Saída', 'Motivo']);
        $tabela->set_align([null, 'center', 'left', 'center', 'center', 'left']);
        $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php"]);

        $tabela->set_classe([null, "pessoal", "pessoal"]);
        $tabela->set_metodo([null, "get_idFuncionalEMatricula", "get_nomeECargo"]);

        $tabela->set_conteudo($result);

        $tabela->set_rowspan(0);
        $tabela->set_grupoCorColuna(0);

        $tabela->set_idCampo("idServidor");
        $tabela->set_editar("?fase={$fase}");
        $tabela->show();
    }

    ##################################################### 

    function exibeAposentadosPorPeriodo($dtInicial = null, $dtFinal = null) {

        /**
         * Exibe tabela com os aposentados por ano de aposentadoria
         * 
         * @param integer $parametroAno da aposentadoria
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Monta o select
        $select = "SELECT tbservidor.idfuncional,
                              tbservidor.idServidor,
                              tbservidor.dtAdmissao,
                              tbservidor.dtDemissao,
                              tbmotivo.motivo
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                        WHERE (tbservidor.dtDemissao >= '{$dtInicial}' AND tbservidor.dtDemissao <= '{$dtFinal})'
                          AND situacao = 2
                          AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                     ORDER BY dtDemissao";

        $result = $pessoal->select($select);

        $tabela = new Tabela();
        $tabela->set_titulo("Servidores Aposentados no Período de " . date_to_php($dtInicial) . " a " . date_to_php($dtFinal));
        $tabela->set_subtitulo('Ordenado pela Data de Saída');

        $tabela->set_label(['IdFuncional', 'Servidor', 'Admissão', 'Saída', 'Motivo']);
        $tabela->set_align(['center', 'left', 'center', 'center', 'left']);
        $tabela->set_funcao([null, null, "date_to_php", "date_to_php"]);

        $tabela->set_classe([null, "pessoal"]);
        $tabela->set_metodo([null, "get_nomeECargo"]);

        $tabela->set_conteudo($result);

        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editarAno');
        $tabela->show();
    }

    #####################################################

    function exibeAposentadosPorTipo($parametroMotivo = null, $fase = null) {

        /**
         * Exibe tabela com os aposentados por tipo de aposentadoria
         * 
         * @param string $parametroMotivo da aposentadoria
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Trata os parametros
        if (empty($fase)) {
            $fase = "editar";
        }

        # Monta o select
        $select = "SELECT year(dtDemissao),
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.dtAdmissao,
                          tbservidor.dtDemissao,
                          tbmotivo.motivo
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                    WHERE tbservidor.motivo = {$parametroMotivo}
                      AND situacao = 2
                      AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                 ORDER BY dtDemissao";

        $result = $pessoal->select($select);

        $tabela = new Tabela();
        $tabela->set_titulo($pessoal->get_motivoAposentadoria($parametroMotivo));
        $tabela->set_subtitulo('Ordenado pela Data de Saída');

        $tabela->set_label(["Ano", 'IdFuncional<br/>Matrícula', 'Servidor', 'Admissão', 'Saída', 'Motivo']);
        $tabela->set_align([null, 'center', 'left', 'center', 'center', 'left']);
        $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php"]);

        $tabela->set_classe([null, "pessoal", "pessoal"]);
        $tabela->set_metodo([null, "get_idFuncionalEMatricula", "get_nomeECargo"]);

        $tabela->set_conteudo($result);

        $tabela->set_rowspan(0);
        $tabela->set_grupoCorColuna(0);

        $tabela->set_idCampo("idServidor");
        $tabela->set_editar("?fase={$fase}");
        $tabela->show();
    }

    #####################################################

    /**
     * Método get_ultimoAnoAposentadoria
     * informa ultimo ano de uma aposentadoria no banco de dados
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_ultimoAnoAposentadoria() {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        $select = 'SELECT YEAR(tbservidor.dtDemissao)
                         FROM tbservidor 
                        WHERE situacao = 2
                          AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                     ORDER BY 1 desc
                     LIMIT 1';

        $ano = $pessoal->select($select, false);

        if (empty($ano[0])) {
            return null;
        } else {
            return $ano[0];
        }
    }

    #####################################################    

    /**
     * Método get_tempoServicoUenf
     * informa o total de dias corridos de tempo de serviço dentro da uenf
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_tempoServicoUenf($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Data Inicial (data de admissão)
        $dtInicial = $pessoal->get_dtAdmissao($idServidor);

        # Verifica se o servidor é inativo e pega a data de saída dele
        if ($pessoal->get_idSituacao($idServidor) == 1) {
            $dtFinal = date("d/m/Y");
        } else {
            $dtFinal = $pessoal->get_dtSaida($idServidor);
        }

        $numdias = getNumDias($dtInicial, $dtFinal);
        return $numdias;
    }

    ##################################################### 

    /**
     * Método get_tempoServicoUenfCeletista
     * informa o total de dias corridos de tempo de serviço celetista dentro da uenf
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_tempoServicoUenfCeletista($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();
        $concurso = new Concurso();

        # define a data em que houve a transformação em estatutário (menos um dia)
        $dataEstatutario = "08/09/2003";

        # Data Inicial (data de admissão)
        $dtInicial = $pessoal->get_dtAdmissao($idServidor);

        # Pega o regime do concurso
        $regime = $concurso->get_regime($pessoal->get_idConcurso($idServidor));

        # Define a data final do período celetista
        if ($regime == "CLT") {
            # Verifica se o servidor é ativo
            if ($pessoal->get_idSituacao($idServidor) == 1) {
                $dtFinal = $dataEstatutario;
            } else {
                # Pega a data de saída
                $dtSaida = $pessoal->get_dtSaida($idServidor);

                # Verifica se foi antes ou depois da transformação
                if (dataMaior($dataEstatutario, $dtSaida) == $dtSaida) {
                    $dtFinal = $dataEstatutario;
                } else {
                    $dtFinal = $dtSaida;
                }
            }
        } else {
            return 0;
        }

        return getNumDias($dtInicial, $dtFinal);
    }

    #####################################################    

    /**
     * Método get_tempoServicoUenfEstatutario
     * informa o total de dias corridos de tempo de serviço estatutario dentro da uenf
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_tempoServicoUenfEstatutario($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();
        $concurso = new Concurso();

        # define a data em que houve a transformação em estatutário
        $dataEstatutario = "09/09/2003";

        # Data Inicial (data de admissão)
        $dtInicial = $pessoal->get_dtAdmissao($idServidor);

        # Pega a data de saída
        $dtSaida = $pessoal->get_dtSaida($idServidor);

        # Pega o regime do concurso
        $regime = $concurso->get_regime($pessoal->get_idConcurso($idServidor));

        # Define a data final do período celetista
        if ($regime == "CLT") {
            # Verifica se o servidor é ativo ou inativo
            if ($pessoal->get_idSituacao($idServidor) == 1) {
                $dtInicial = $dataEstatutario;
                $dtFinal = date("d/m/Y");
            } else {
                # Verifica se foi antes ou depois da transformação
                if (dataMaior($dataEstatutario, $dtSaida) == $dtSaida) {
                    $dtInicial = $dataEstatutario;
                } else {
                    return 0;
                }
            }
        } else {
            if ($pessoal->get_idSituacao($idServidor) == 1) {
                $dtFinal = date("d/m/Y");
            } else {
                $dtFinal = $dtSaida;
            }
        }

        return getNumDias($dtInicial, $dtFinal);
    }

    #####################################################        

    /**
     * Método get_tempoServicoUenfAntes31_12_21
     * informa o total de dias corridos de tempo de serviço 
     * dentro da uenf antes de 31/12/2021
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_tempoServicoUenfAntes31_12_21($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Define as datas
        $dataAlvo = "31/12/2021";
        $dtInicial = $pessoal->get_dtAdmissao($idServidor);

        # Verifica se a admissão é posterior a data alvo
        if (dataMenor($dataAlvo, $dtInicial) == $dataAlvo) {
            return 0;
        } else {
            # Verifica se o servidor é inativo e pega a data de saída dele
            if ($pessoal->get_idSituacao($idServidor) == 1) {
                $dtFinal = $dataAlvo;
            } else {
                $dtFinal = $pessoal->get_dtSaida($idServidor);

                # Verifica se saiu antes ou depois da data alvo
                if (dataMenor($dataAlvo, $dtFinal) == $dataAlvo) {
                    $dtFinal = $dataAlvo;
                }
            }
        }

        # Pega o tempo sem contribuição
        $tempoRetirar = $this->get_tempoUenfInterrompidoAntes31_12_21($idServidor);

        return getNumDias($dtInicial, $dtFinal) - $tempoRetirar;
    }

    #####################################################        

    /**
     * Método get_tempoServicoUenfAntesDataAlvo
     * informa o total de dias corridos de tempo de serviço 
     * dentro da uenf antes de uma data alvo
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_tempoServicoUenfAntesDataAlvo($idServidor = null, $dataAlvo = null) {

        # Verifica se foi informado os parâmetros
        if (empty($idServidor) OR empty($dataAlvo)) {
            return null;
        }

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Data inicial
        $dtInicial = $pessoal->get_dtAdmissao($idServidor);

        # Verifica se a admissão é posterior a data alvo
        if (dataMenor($dataAlvo, $dtInicial) == $dataAlvo) {
            return 0;
        } else {
            # Verifica se o servidor é inativo e pega a data de saída dele
            if ($pessoal->get_idSituacao($idServidor) == 1) {
                $dtFinal = $dataAlvo;
            } else {
                $dtFinal = $pessoal->get_dtSaida($idServidor);

                # Verifica se saiu antes ou depois da data alvo
                if (dataMenor($dataAlvo, $dtFinal) == $dataAlvo) {
                    $dtFinal = $dataAlvo;
                }
            }
        }

        # Pega o tempo sem contribuição
        $tempoRetirar = $this->get_tempoUenfInterrompidoAntes31_12_21($idServidor);

        return getNumDias($dtInicial, $dtFinal) - $tempoRetirar;
    }

    #####################################################

    /**
     * Método get_dtIngresso
     * informa a data de ingresso
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_dtIngresso($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Pega os dados do servidor
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

        # Pega os dados
        $select = "SELECT dtInicial,
                          dtFinal
                     FROM tbaverbacao
                    WHERE empresaTipo = 1 AND idServidor = {$idServidor}
                 ORDER BY dtInicial DESC";

        $result = $pessoal->select($select);
        $dtReferencia = $dtAdmissao;

        # Percorre os registros
        foreach ($result as $periodo) {
            $dtInicial = date_to_php($periodo[0]);
            $dtFinal = date_to_php($periodo[1]);

            # Verifica se é initerrupto
            if (($dtFinal == $dtReferencia) OR ($dtFinal == addDias($dtReferencia, -1, false))) {
                $dtReferencia = $dtInicial;
            } else {
                break;
            }
        }

        return $dtReferencia;
    }

    #####################################################

    /**
     * Método get_tempoPublicoIninterrupto
     * informa em dias o tempo publico ininterrupto
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoPublicoIninterrupto($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Pega a data de Ingresso
        $dtIngresso = $this->get_dtIngresso($idServidor);

        # Pega os dados
        $select = "SELECT dtInicial,
                          dias
                     FROM tbaverbacao
                    WHERE empresaTipo = 1 AND idServidor = {$idServidor}
                 ORDER BY dtInicial DESC";

        $result = $pessoal->select($select);
        $totalDias = $this->get_tempoServicoUenf($idServidor) - $this->get_tempoAfastadoComContribuicao($idServidor);

        # Percorre o arquivo de averbação para pegar os dias digitados (e não calculados)
        foreach ($result as $periodo) {

            # Se a data inicial do tempo averbado for igual ou maior que a data
            # de ingresso então acrescenta os dias
            if (strtotime(date_to_bd($dtIngresso)) <= strtotime($periodo[0])) {
                $totalDias += $periodo[1];
            }
        }

        return $totalDias;
    }

    #####################################################

    /**
     * Método get_tempoTotal
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoTotal($idServidor) {

        $averbacao = new Averbacao();
        $tempoAverbado = $averbacao->get_tempoAverbadoTotal($idServidor);
        $tempoUenf = $this->get_tempoServicoUenf($idServidor);

        return $tempoAverbado + $tempoUenf;
    }

    #####################################################

    /**
     * Método get_tempoTotal
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoTotalAntes31_12_21($idServidor) {

        $averbacao = new Averbacao();
        $tempoUenf = $this->get_tempoServicoUenfAntes31_12_21($idServidor);
        $tempoAverbado = $averbacao->getTempoAverbadoAntes31_12_21($idServidor);

        return $tempoUenf + $tempoAverbado;
    }

    #####################################################

    /**
     * Método get_tempoTotal
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoTotalAntesDataAlvo($idServidor = null, $dataAlvo = null) {

        # Verifica se foi informado os parâmetros
        if (empty($idServidor) OR empty($dataAlvo)) {
            return null;
        }

        $averbacao = new Averbacao();
        $tempoUenf = $this->get_tempoServicoUenfAntesDataAlvo($idServidor, $dataAlvo);
        $tempoAverbado = $averbacao->getTempoAverbadoAntesDataAlvo($idServidor, $dataAlvo);

        return $tempoUenf + $tempoAverbado;
    }

    #####################################################

    /**
     * Método get_data20anosPublicos
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_data20anosPublicos($idServidor) {

        $dtIngresso = $this->get_dtIngresso($idServidor);
        return day($dtIngresso) . "/" . month($dtIngresso) . "/" . (year($dtIngresso) + 20);
    }

    #####################################################

    /**
     * Método get_data10anosPublicos
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_data10anosPublicos($idServidor) {

        $dtIngresso = $this->get_dtIngresso($idServidor);
        return day($dtIngresso) . "/" . month($dtIngresso) . "/" . (year($dtIngresso) + 10);
    }

    #####################################################

    /**
     * Método get_data25anosPublicos
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_data25anosPublicos($idServidor) {

        $dtIngresso = $this->get_dtIngresso($idServidor);
        return day($dtIngresso) . "/" . month($dtIngresso) . "/" . (year($dtIngresso) + 25);
    }

    #####################################################

    /**
     * Método get_data30anosPublicos
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_data30anosPublicos($idServidor) {

        $dtIngresso = $this->get_dtIngresso($idServidor);
        return day($dtIngresso) . "/" . month($dtIngresso) . "/" . (year($dtIngresso) + 30);
    }

    #####################################################

    /**
     * Método get_data35anosPublicos
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_data35anosPublicos($idServidor) {

        $dtIngresso = $this->get_dtIngresso($idServidor);
        return day($dtIngresso) . "/" . month($dtIngresso) . "/" . (year($dtIngresso) + 35);
    }

    #####################################################

    /**
     * Método get_tempoAfastadoComContribuicao
     * informa o total de dias de tempo afastado mas com contribuição
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoAfastadoComContribuicao($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Licença Sem Vencimentos
        $select2 = "SELECT numDias                           
                      FROM tblicencasemvencimentos
                      WHERE idServidor = {$idServidor}
                        AND optouContribuir = 1";

        # Soma
        return array_sum(array_column($pessoal->select($select2), 'numDias'));
    }

    #####################################################

    function get_tempoUenfInterrompidoAntes31_12_21($idServidor) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        }

        # Licença Geral
        $select = "(SELECT dtInicial,
                           dtTermino,
                           numDias
                      FROM tblicenca JOIN tbtipolicenca USING(idTpLicenca)
                     WHERE idServidor = {$idServidor}
                       AND tbtipolicenca.tempoServico = 'Sim'
                   ) UNION (
                    SELECT dtInicial,
                           dtTermino,
                           numDias                           
                      FROM tblicencasemvencimentos
                      WHERE idServidor = {$idServidor}
                        AND (optouContribuir = 2 OR optouContribuir is NULL)
                        ) ORDER BY 1";

        # Conecta o banco de dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select);

        # Define a variavel de retorno
        $tempo = 0;

        # Define as datas
        $dataAlvo = "31/12/2021";

        # Percorre os registros
        foreach ($row as $itens) {
            # As datas
            $dtInicial = date_to_php($itens["dtInicial"]);
            $dtFinal = date_to_php($itens["dtTermino"]);

            # Verifica se a data Alvo está após o período
            if (dataMenor($dataAlvo, $dtFinal) == $dtFinal) {
                $tempo += $itens["numDias"];
            }

            # Verifica se a data Alvo está dentro  do período
            if (entre($dataAlvo, $dtInicial, $dtFinal)) {
                $tempo += getNumDias($dtInicial, $dataAlvo);
            }
        }

        return $tempo;
    }

    #####################################################

    function get_tempoUenfInterrompidoAntesDataAlvo($idServidor = null, $dataAlvo = null) {

        # Verifica se foi informado os parâmetros
        if (empty($idServidor) OR empty($dataAlvo)) {
            return null;
        }

        # Licença Geral
        $select = "(SELECT dtInicial,
                           dtTermino,
                           numDias
                      FROM tblicenca JOIN tbtipolicenca USING(idTpLicenca)
                     WHERE idServidor = {$idServidor}
                       AND tbtipolicenca.tempoServico = 'Sim'
                   ) UNION (
                    SELECT dtInicial,
                           dtTermino,
                           numDias                           
                      FROM tblicencasemvencimentos
                      WHERE idServidor = {$idServidor}
                        AND (optouContribuir = 2 OR optouContribuir is NULL)
                        ) ORDER BY 1";

        # Conecta o banco de dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select);

        # Define a variavel de retorno
        $tempo = 0;

        # Percorre os registros
        foreach ($row as $itens) {
            # As datas
            $dtInicial = date_to_php($itens["dtInicial"]);
            $dtFinal = date_to_php($itens["dtTermino"]);

            # Verifica se a data Alvo está após o período
            if (dataMenor($dataAlvo, $dtFinal) == $dtFinal) {
                $tempo += $itens["numDias"];
            }

            # Verifica se a data Alvo está dentro  do período
            if (entre($dataAlvo, $dtInicial, $dtFinal)) {
                $tempo += getNumDias($dtInicial, $dataAlvo);
            }
        }

        return $tempo;
    }

    ###########################################################

    public function get_dataAposentadoriaCompulsoria($idServidor) {

        /*
         * Retorna a data da aposentadoria compulsória do servidor
         */

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Pega a idade par aposentadoria compulsória
        $idade = $intra->get_variavel("aposentadoria.compulsoria.idade");

        $select = "SELECT ADDDATE(dtNasc, INTERVAL {$idade} YEAR)                    
                     FROM tbservidor JOIN tbpessoa USING (idPessoa)
                    WHERE idPerfil = 1
                      AND idServidor = {$idServidor}";

        $result = $pessoal->select($select, false);

        # retorno
        if (empty($result[0])) {
            return null;
        } else {
            return date_to_php($result[0]);
        }
    }

    ###########################################################

    public function exibeDadosServidor($idServidor = null, $relatorio = false) {

        /*
         * Exibe os dados de aposentadoria do servidor
         */

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Conecta as Classes
        $averbacao = new Averbacao();

        $regime = [
            [1, "Celetista"],
            [2, "Estatutário"],
            [3, "Próprio"],
            [4, "Militar"]
        ];

        $grid1 = new Grid();
        if ($relatorio) {
            $grid1->abreColuna(6);
        } else {
            $grid1->abreColuna(12, 6, 3);
        }

        $array = [
            ["Idade", $pessoal->get_idade($idServidor)],
            ["Data de Nascimento", $pessoal->get_dataNascimento($idServidor)],
            ["Data de Admissão", $pessoal->get_dtadmissao($idServidor)],
            ["Data de Ingresso<br/><p id='psubtitulo'>no Serviço Público</p>", $this->get_dtIngresso($idServidor)]
        ];

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Dados do Servidor");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Dados do Servidor");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Descrição", "Valor"]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        $grid1->fechaColuna();
        if ($relatorio) {
            $grid1->abreColuna(6);
        } else {
            $grid1->abreColuna(12, 6, 3);
        }

        /*
         *  Tempo Geral
         */

        $array = [
            ["Cargo Efetivo - Uenf", $this->get_tempoServicoUenf($idServidor)],
            ["Tempo Averbado", $averbacao->get_tempoAverbadoTotal($idServidor)]
        ];

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Tempo Geral");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo Geral");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Descrição", "Dias"]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_totalRegistro(false);
        $tabela->set_colunaSomatorio(1);
        $tabela->show();

        /*
         *  Tabela Tempo até 31/12/2021
         */

        $array = [
            ["Cargo Efetivo - Uenf", $this->get_tempoServicoUenfAntes31_12_21($idServidor)],
            ["Tempo Averbado", $averbacao->getTempoAverbadoAntes31_12_21($idServidor)]
        ];

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Tempo até 31/12/2021");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo até 31/12/2021");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Descrição", "Dias"]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_colunaSomatorio(1);
        $tabela->show();

        $grid1->fechaColuna();
        if ($relatorio) {
            $grid1->abreColuna(6);
        } else {
            $grid1->abreColuna(12, 6, 3);
        }

        /*
         *  Tempo Uenf
         */

        $array = [
            ["Uenf Celetista", $this->get_tempoServicoUenfCeletista($idServidor)],
            ["Uenf Estatutária", $this->get_tempoServicoUenfEstatutario($idServidor)]
        ];

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Tempo Uenf");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo Uenf");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Descrição", "Dias"]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_colunaSomatorio(1);
        $tabela->show();

        /*
         *  Afastamentos
         */

        $array = [
            ["<b>COM</b> Contribuição", $this->get_semTempoServicoComTempoContribuicao($idServidor)],
            ["<b>SEM</b> Contribuição", $this->get_semTempoServicoSemTempoContribuicao($idServidor)]
        ];

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Afastamentos que interrompem o Tempo de Serviço");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Afastamentos");
            $tabela->set_subtitulo("Interrompem o Tempo de Serviço");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Descrição", "Dias"]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        /*
         *  Tempo Averbado
         */
        $array = [
            ["Privado", $averbacao->get_tempoAverbadoPrivado($idServidor)]];

        foreach ($regime as $item) {
            if ($averbacao->get_tempoAverbadoPublicoRegime($idServidor, $item[0]) > 0) {
                array_unshift($array, array("Público<br/><p id='psubtitulo'>Regime {$item[1]}</p>", $averbacao->get_tempoAverbadoPublicoRegime($idServidor, $item[0])));
            }
        }

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Tempo Averbado");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo Averbado");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Descrição", "Dias"]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_colunaSomatorio(1);
        $tabela->show();

        $grid1->fechaColuna();
        if ($relatorio) {
            $grid1->abreColuna(6);
        } else {
            $grid1->abreColuna(12, 6, 3);
        }

        /*
         *  Tempo Público
         */
        $array = [
            ["Tempo Uenf", $this->get_tempoServicoUenf($idServidor) - $this->get_tempoAfastadoComContribuicao($idServidor)],
            ["Tempo Averbado", $averbacao->get_tempoAverbadoPublico($idServidor)]
        ];

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Tempo Público");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo Público");
        }

        $tabela->set_conteudo($array);
        $tabela->set_label(["Descrição", "Dias"]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_colunaSomatorio(1);
        $tabela->show();

        $array = [
            ["Tempo Ininterrupto", $this->get_tempoPublicoIninterrupto($idServidor)]
        ];

        # Exibe a tabela
        if ($relatorio) {
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);
        } else {
            $tabela = new Tabela();
        }
        $tabela->set_conteudo($array);
        $tabela->set_label(["", ""]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["left", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();

        $grid1->fechaColuna();
        $grid1->fechaGrid();
    }

    ###########################################################

    public function exibeTempoAverbado($idServidor = null, $relatorio = false) {

        /*
         *  Tempo Averbado Detalhado
         */

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        $grid1 = new Grid();
        $grid1->abreColuna(12);

        # Variáveis
        $empresaTipo = [
            [1, "Pública"],
            [2, "Privada"]
        ];

        $regime = [
            [1, "Celetista"],
            [2, "Estatutário"],
            [3, "Próprio"],
            [4, "Militar"]
        ];

        $select = "SELECT dtInicial,
                      dtFinal,
                      dias,";

        if (!$relatorio) {
            $select .= "  idAverbacao,
                      idAverbacao,";
        }

        $select .= "  empresa,
                      CASE empresaTipo ";

        foreach ($empresaTipo as $tipo) {
            $select .= " WHEN {$tipo[0]} THEN '{$tipo[1]}' ";
        }

        $select .= "      END,
                      CASE regime ";
        foreach ($regime as $tipo2) {
            $select .= " WHEN {$tipo2[0]} THEN '{$tipo2[1]}' ";
        }

        $select .= "      END,
                      cargo,
                      dtPublicacao,
                      processo
                 FROM tbaverbacao
                WHERE idServidor = {$idServidor}
             ORDER BY dtInicial desc";

        $result = $pessoal->select($select);

        # Exibe a tabela
        if ($relatorio) {
            tituloRelatorio("Tempo Averbado - Detalhado");
            $tabela = new Relatorio();
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);
            $tabela->set_totalRegistro(false);
            $tabela->set_dataImpressao(false);
            $tabela->set_bordaInterna(true);
            $tabela->set_log(false);

            $tabela->set_label(["Data Inicial", "Data Final", "Dias", "Empresa", "Tipo", "Regime", "Cargo", "Publicação", "Processo"]);
            $tabela->set_align(["center", "center", "center", "left"]);
            $tabela->set_funcao(["date_to_php", "date_to_php", null, null, null, null, null, "date_to_php"]);
            $tabela->set_funcao(["date_to_php", "date_to_php", null, null, null, null, null, "date_to_php"]);

            $tabela->set_totalRegistro(false);
            $tabela->set_colunaSomatorio(2);
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Tempo Averbado - Detalhado");

            $tabela->set_label(["Data Inicial", "Data Final", "Dias Digitados", "Dias Calculados", "Dias Anteriores a 15/12/1998", "Empresa", "Tipo", "Regime", "Cargo", "Publicação", "Processo"]);
            $tabela->set_width([9, 9, 6, 6, 6, 25, 6, 6, 6, 6, 15]);
            $tabela->set_align(["center", "center", "center", "center", "center", "left"]);
            $tabela->set_funcao(["date_to_php", "date_to_php", null, null, null, null, null, null, null, "date_to_php"]);

            $tabela->set_classe([null, null, null, "Averbacao", "Averbacao"]);
            $tabela->set_metodo([null, null, null, "getNumDias", "getDiasAnterior15_12_98"]);

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
                    'valor' => 0,
                    'operador' => '<>',
                    'id' => 'diasAntes'),
                array('coluna' => 4,
                    'valor' => 0,
                    'operador' => '=',
                    'id' => 'normal')
            ));

            $tabela->set_totalRegistro(false);
            $tabela->set_colunaSomatorio([2, 3]);
        }

        $tabela->set_conteudo($result);
        $tabela->show();

        $grid1->fechaColuna();
        $grid1->fechaGrid();
    }

    ###########################################################

    public function exibe_previsãoPermanente($idServidor = null) {

        foreach ($this->get_modalidades("Regras Permanentes") as $item) {
            $this->exibe_previsão($idServidor, $item);
        }
    }

    ###########################################################

    public function exibe_previsãoPermanente2($idServidor = null) {

        $modalidades = $this->get_modalidades("Regras Permanentes");
        $grid1 = new Grid();

        foreach ($modalidades as $item) {
            $grid1->abreColuna(12 / count($modalidades));

            $this->exibe_previsão($idServidor, $item);
            $grid1->fechaColuna();
        }

        $grid1->fechaGrid();
    }

    #####################################################  

    public function exibe_previsãoTransicao($idServidor = null) {

        # Preenche as modalidades
        foreach ($this->get_modalidades("Regras de Transição") as $item) {
            $this->exibe_previsão($idServidor, $item);
        }
    }

    ###########################################################

    public function exibe_previsãoTransicao2($idServidor = null) {

        $modalidades = $this->get_modalidades("Regras de Transição");
        $grid1 = new Grid();

        foreach ($modalidades as $item) {
            $grid1->abreColuna(12 / count($modalidades));

            $this->exibe_previsão($idServidor, $item);
            $grid1->fechaColuna();
        }

        $grid1->fechaGrid();
    }

    #####################################################  

    public function exibe_previsãoAdquirido($idServidor = null) {

        foreach ($this->get_modalidades("Direito Adquirido") as $item) {
            $this->exibe_previsão($idServidor, $item);
        }
    }

    #####################################################  

    public function exibe_previsãoAdquirido2($idServidor = null) {

        $modalidades = $this->get_modalidades("Direito Adquirido");
        $grid1 = new Grid();

        foreach ($modalidades as $item) {
            $grid1->abreColuna(12 / count($modalidades));

            $this->exibe_previsão($idServidor, $item);
            $grid1->fechaColuna();
        }

        $grid1->fechaGrid();
    }

    #####################################################  

    public function exibe_previsão($idServidor = null, $tipo = null) {

        $previsaoAposentadoria = new PrevisaoAposentadoria($tipo, $idServidor);
        $link = "?fase=carregarPagina&id={$idServidor}&link={$tipo}";
        $previsaoAposentadoria->exibe_analiseLink($idServidor, $link);
    }

    #####################################################

    /**
     * Método get_modalidades
     * retorna um array com as modalidades de aposentadoria
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_modalidades($tipo = null) {

        $arrayModalidades = [
            ["Regras Permanentes", "voluntaria"],
            ["Regras Permanentes", "compulsoria"],
            ["Regras de Transição", "pontos1"],
            ["Regras de Transição", "pontos2"],
            ["Regras de Transição", "pedagio1"],
            ["Regras de Transição", "pedagio2"],
            ["Regras de Transição", "pedagio3"],
            ["Direito Adquirido", "adquirido1"],
            ["Direito Adquirido", "adquirido2"],
            ["Direito Adquirido", "adquirido3"],
            ["Direito Adquirido", "adquirido4"],
        ];

        if (is_null($tipo)) {
            foreach ($arrayModalidades as $item) {
                # Pega os dados e coloca no array de retorno
                $retorno[] = $item[1];
            }
        } else {
            foreach ($arrayModalidades as $item) {
                # Pega os dados e coloca no array de retorno
                if ($item[0] == $tipo) {
                    $retorno[] = $item[1];
                }
            }
        }

        return $retorno;
    }

    #####################################################  

    public function get_semTempoServicoSemTempoContribuicao($idServidor = null, $dataPrevista = null) {

        # Inicia a variável de retorno
        $retorno = 0;

        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        ######
        # Licença sem vencimentos
        $select = "(SELECT dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                     FROM tblicencasemvencimentos
                    WHERE idServidor = {$idServidor}
                      AND (optouContribuir = 2 OR optouContribuir is NULL))
                    UNION
                    (SELECT dtInicial,
                            numDias,
                            ADDDATE(dtInicial,numDias-1) as dtFinal
                       FROM tblicenca JOIN tbservidor USING (idServidor)
                                      JOIN tbtipolicenca USING (idTpLicenca)
                      WHERE idServidor = {$idServidor}
                        AND tbtipolicenca.tempoServico = 'Sim')
                 ORDER BY 1";

        $result2 = $pessoal->select($select);

        # Percorre e soma os afastamentos
        foreach ($result2 as $item) {
            if (!is_null($dataPrevista)) {
                
                # Verifica se a data final do afastamento é anterior a data prevista
                if(strtotime($item["dtFinal"]) <= strtotime(date_to_bd($dataPrevista))){
                    $retorno += $item["numDias"];
                }
                
                # Verifica se a data final é posterior a data prevista mas a data inicial é anterior
                if(entre($dataPrevista, date_to_php($item["dtInicial"]),date_to_php($item["dtFinal"]), false)){
                    $retorno += getNumDias(date_to_php($item["dtInicial"]),date_to_php($item["dtFinal"]));
                }
            } else {
                $retorno += $item["numDias"];
            }
        }

        # Retorna o valor calculado
        return $retorno;
    }

    #####################################################  

    public function get_semTempoServicoComTempoContribuicao($idServidor = null, $dataPrevista = null) {

        # Inicia a variável de retorno
        $retorno = 0;

        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        ######
        # Licença sem vencimentos
        $select = "SELECT dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                     FROM tblicencasemvencimentos
                    WHERE idServidor = {$idServidor}
                      AND optouContribuir = 1
                 ORDER BY dtInicial";

        $result2 = $pessoal->select($select);

        # Percorre e soma os afastamentos
        foreach ($result2 as $item) {
            if (!is_null($dataPrevista)) {
                
                # Verifica se a data final do afastamento é anterior a data prevista
                if(strtotime($item["dtFinal"]) <= strtotime(date_to_bd($dataPrevista))){
                    $retorno += $item["numDias"];
                }
                
                # Verifica se a data final é posterior a data prevista mas a data inicial é anterior
                if(entre($dataPrevista, date_to_php($item["dtInicial"]),date_to_php($item["dtFinal"]), false)){
                    $retorno += getNumDias(date_to_php($item["dtInicial"]),date_to_php($item["dtFinal"]));
                }
            } else {
                $retorno += $item["numDias"];
            }
        }

        # Retorna o valor calculado
        return $retorno;
    }

    #####################################################  

    public function get_semTempoServico($idServidor = null, $dataPrevista = null) {

        # Inicia a variável de retorno
        $retorno = 0;

        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        ######
        # Licença sem vencimentos
        $select = "(SELECT dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                     FROM tblicencasemvencimentos
                    WHERE idServidor = {$idServidor})
                    UNION
                    (SELECT dtInicial,
                            numDias,
                            ADDDATE(dtInicial,numDias-1) as dtFinal
                       FROM tblicenca JOIN tbservidor USING (idServidor)
                                      JOIN tbtipolicenca USING (idTpLicenca)
                      WHERE idServidor = {$idServidor}
                        AND tbtipolicenca.tempoServico = 'Sim')
                 ORDER BY 1";

        $result2 = $pessoal->select($select);

        # Percorre e soma os afastamentos
        foreach ($result2 as $item) {
            if (!is_null($dataPrevista)) {
                
                # Verifica se a data final do afastamento é anterior a data prevista
                if(strtotime($item["dtFinal"]) <= strtotime(date_to_bd($dataPrevista))){
                    $retorno += $item["numDias"];
                }
                
                # Verifica se a data final é posterior a data prevista mas a data inicial é anterior
                if(entre($dataPrevista, date_to_php($item["dtInicial"]),date_to_php($item["dtFinal"]), false)){
                    $retorno += getNumDias(date_to_php($item["dtInicial"]),date_to_php($item["dtFinal"]));
                }
            } else {
                $retorno += $item["numDias"];
            }
        }

        # Retorna o valor calculado
        return $retorno;
    }

    #####################################################    
}
