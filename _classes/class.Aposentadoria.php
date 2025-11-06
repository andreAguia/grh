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
    ############################################################################ 

    function exibeAposentadosPorAno($parametroAno = null, $fase = null, $relatório = false) {

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
        $select = "SELECT year(dtDemissao),
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.dtAdmissao,
                          tbservidor.dtDemissao,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbmotivo USING (idMotivo)
                    WHERE YEAR(tbservidor.dtDemissao) = '{$parametroAno}'
                      AND situacao = 2
                      AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                 ORDER BY dtDemissao desc";

        $result = $pessoal->select($select);

        if ($relatório) {

            $tabela = new Relatorio();
            $tabela->set_numGrupo(0);
            $tabela->set_bordaInterna(true);
        } else {
            $tabela = new Tabela();

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_idCampo("idServidor");
            $tabela->set_editar("?fase={$fase}");
        }

        $tabela->set_titulo('Servidores Aposentados por Ano');
        $tabela->set_subtitulo('Ordenado pela Data de Saída');

        $tabela->set_label(['Ano', 'IdFuncional<br/>Matrícula', 'Servidor', 'Admissão', 'Saída', 'Tipo']);
        $tabela->set_align([null, 'center', 'left', 'center', 'center', 'left']);
        $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php"]);
        $tabela->set_width([10, 10, 25, 10, 10, 25]);

        $tabela->set_classe([null, "pessoal", "pessoal", null, null, "Aposentadoria"]);
        $tabela->set_metodo([null, "get_idFuncionalEMatricula", "get_nomeECargoELotacao", null, null, "get_tipoAposentadoria"]);

        $tabela->set_conteudo($result);
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
                                         LEFT JOIN tbmotivo USING (idMotivo)
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

    function exibeAposentadosPorTipo($parametroMotivo = null, $fase = null, $relatório = false) {

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
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbmotivo USING (idMotivo)
                    WHERE tbservidor.idMotivo = {$parametroMotivo}
                      AND situacao = 2
                      AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                 ORDER BY dtDemissao desc";

        $result = $pessoal->select($select);

        if ($relatório) {

            $tabela = new Relatorio();
            $tabela->set_numGrupo(0);
            $tabela->set_bordaInterna(true);
        } else {
            $tabela = new Tabela();

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_idCampo("idServidor");
            $tabela->set_editar("?fase={$fase}");
        }

        $tabela->set_titulo($pessoal->get_motivoAposentadoria($parametroMotivo));
        $tabela->set_subtitulo('Ordenado pela Data de Saída');

        $tabela->set_label(["Ano", 'IdFuncional<br/>Matrícula', 'Servidor', 'Admissão', 'Saída', 'Tipo']);
        $tabela->set_align([null, 'center', 'left', 'center', 'center', 'left']);
        $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php"]);

        $tabela->set_width([10, 10, 25, 10, 10, 25]);

        $tabela->set_classe([null, "pessoal", "pessoal", null, null, "Aposentadoria"]);
        $tabela->set_metodo([null, "get_idFuncionalEMatricula", "get_nomeECargoELotacao", null, null, "get_tipoAposentadoria"]);

        $tabela->set_conteudo($result);
        $tabela->show();
    }

    #####################################################

    function exibeAposentadosPorFundamentacaoLegal($parametroFundamentacao = null, $fase = null, $relatório = false) {

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
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tbmotivo USING (idMotivo)";

        if (empty($parametroFundamentacao)) {
            $select .= " WHERE tbservidor.motivoDetalhe IS NULL";
        } else {
            $select .= " WHERE tbservidor.motivoDetalhe = '{$parametroFundamentacao}'";
        }

        $select .= "  AND situacao = 2
                      AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                 ORDER BY dtDemissao desc";

        $result = $pessoal->select($select);

        if ($relatório) {

            $tabela = new Relatorio();
            $tabela->set_numGrupo(0);
            $tabela->set_bordaInterna(true);
        } else {
            $tabela = new Tabela();

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_idCampo("idServidor");
            $tabela->set_editar("?fase={$fase}");
        }

        $tabela->set_titulo($parametroFundamentacao);
        $tabela->set_subtitulo('Ordenado pela Data de Saída');

        $tabela->set_label(["Ano", 'IdFuncional<br/>Matrícula', 'Servidor', 'Admissão', 'Saída', 'Tipo']);
        $tabela->set_align([null, 'center', 'left', 'center', 'center', 'left']);
        $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php"]);

        $tabela->set_width([10, 10, 25, 10, 10, 25]);

        $tabela->set_classe([null, "pessoal", "pessoal", null, null, "Aposentadoria"]);
        $tabela->set_metodo([null, "get_idFuncionalEMatricula", "get_nomeECargoELotacao", null, null, "get_tipoAposentadoria"]);

        $tabela->set_conteudo($result);
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
     * Método get_dtIngresso
     * 
     * Informa a data de ingresso para ser usada como fixação de data de ingresso.
     * Essa data NÃO considera tempo público averbado celetista
     * Atente que essa data é diferente da data de ingresso para contagem de tempo público
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
                    WHERE empresaTipo = 1
                      AND regime = 2
                      AND idServidor = {$idServidor}
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
     * Método get_dtIngressoParaTempoPublico
     * 
     * Informa a data de ingresso para ser usada como início do tempo público
     * Essa data considera tempo público averbado celetista
     * Atente que essa data é diferente da data de ingresso
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_dtIngressoParaTempoPublico($idServidor) {

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

    public function exibeDadosServidor($idServidor = null, $relatorio = false) {

        /*
         * Exibe os dados de aposentadoria do servidor
         */

        # Conecta as Classes
        $pessoal = new Pessoal();
        $averbacao = new Averbacao();
        $tempoServico = new TempoServico();
        $afastamentos = new Afastamentos();

        # pega os valores
        $dtIngresso = $this->get_dtIngresso($idServidor);
        $dtIngressoTempoPublico = $this->get_dtIngressoParaTempoPublico($idServidor);

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
            ["Data de Ingresso<br/><p id='psubtitulo'>para Tempo de Serviço Público</p>", $dtIngressoTempoPublico]
        ];

        if ($dtIngressoTempoPublico <> $dtIngresso) {
            array_push($array, ["Data de Ingresso<br/><p id='psubtitulo'>para regra de Aposentadoria<p id='psubtitulo'></p>", $dtIngresso]);
        }


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

        # Informa quando a data de ingresso para tempo de serviço é
        # diferente da data de ingresso de fato
        if ($dtIngressoTempoPublico <> $dtIngresso) {
            $painel = new Callout();
            $painel->abre();
            tituloTable("Atenção");
            br();
            p("Servidor com data de ingresso diferente da data de ingresso considerada para tempo de serviço", "center");
            $painel->fecha();
        }

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
            ["Cargo Efetivo - Uenf", $tempoServico->get_tempoServicoUenfBruto($idServidor)],
            ["Tempo Averbado", $averbacao->get_tempoAverbadoTotal($idServidor)],
            ["Afastamento <b>SEM</b> Contribuição", -$afastamentos->get_tempoAfastamentoSuspendeTempoServicoSemContribuicao($idServidor)]
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
            ["Cargo Efetivo - Uenf", $tempoServico->get_tempoServicoUenfAntes31_12_21($idServidor)],
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
            ["Uenf Celetista", $tempoServico->get_tempoServicoUenfBrutoCeletista($idServidor)],
            ["Uenf Estatutária", $tempoServico->get_tempoServicoUenfBrutoEstatutario($idServidor)]
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
            ["<b>COM</b> Contribuição", $afastamentos->get_tempoAfastamentoSuspendeTempoServicoComContribuicao($idServidor)],
            ["<b>SEM</b> Contribuição", $afastamentos->get_tempoAfastamentoSuspendeTempoServicoSemContribuicao($idServidor)]
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
            //["Tempo Uenf", $this->get_tempoServicoUenfBruto($idServidor) - $this->get_tempoAfastadoComContribuicao($idServidor)],  // Tava retirando os afastamentos 2 vezes
            ["Tempo Uenf", $tempoServico->get_tempoServicoUenfBruto($idServidor)],
            ["Tempo Averbado", $averbacao->get_tempoAverbadoPublico($idServidor)],
            ["Afastamento <b>SEM</b> Contribuição", -$afastamentos->get_tempoAfastamentoSuspendeTempoServicoSemContribuicao($idServidor)],
            ["Afastamento <b>COM</b> Contribuição", -$afastamentos->get_tempoAfastamentoSuspendeTempoServicoComContribuicao($idServidor)]
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
            ["Tempo Ininterrupto", $tempoServico->get_tempoPublicoIninterrupto($idServidor)]
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
                      processo,
                      idAverbacao
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

            $tabela->set_label(["Data Inicial", "Data Final", "Dias Digitados", "Dias Calculados", "Dias Anteriores de 15/12/1998", "Empresa", "Tipo", "Regime", "Cargo", "Publicação", "Processo", "Obs"]);
            $tabela->set_width([8, 8, 8, 8, 8, 20, 5, 5, 5, 8, 10, 5]);
            $tabela->set_align(["center", "center", "center", "center", "center", "left"]);
            $tabela->set_funcao(["date_to_php", "date_to_php", null, null, null, null, null, null, null, "date_to_php"]);

            $tabela->set_classe([null, null, null, "Averbacao", "Averbacao", null, null, null, null, null, null, "Averbacao"]);
            $tabela->set_metodo([null, null, null, "getNumDias", "getDiasAnterior15_12_98", null, null, null, null, null, null, "exibeObs"]);

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

    /**
     * Método get_tipoAposentadoria
     * Informa tipo de aposentadoria de um servidor aposentado
     * 
     * @param string $idServidor    null idServidor do servidor
     */
    public function get_tipoAposentadoria($idServidor) {

        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        # Monta o select
        $select = "SELECT tbmotivo.motivo,
                          tbservidor.tipoAposentadoria,
                          tbservidor.motivoDetalhe
                     FROM tbservidor LEFT JOIN tbmotivo USING (idMotivo)
                    WHERE idServidor = {$idServidor}";

        $row = $pessoal->select($select, false);

        pLista(
                $row[0],
                $row[1],
                $row[2]
        );
    }

    ###########################################################   

    /**
     * Método exibe_alertaEntregaCtc
     * Informa Exibe o alerta para quando não entregou o CTC inss
     * 
     * @param string $idServidor    null idServidor do servidor
     */
    public function exibe_alertaEntregaCtc($idServidor = null) {

        # Compara se a adimossão é anterior a data divisora
        if ($this->precisaEntregarCtc($idServidor)) {
            $pessoal = new Pessoal();
            if (!$pessoal->get_entregouCtc($idServidor)) {
                callout("Servidor não entregou o CTC INSS", "alert");
            }
        }
    }

    ###########################################################   

    /**
     * Método precisaEntregarCtc
     * Informa Se precisa ou não entregar CTC Inss
     * 
     * @param string $idServidor    null idServidor do servidor
     */
    public function precisaEntregarCtc($idServidor = null) {

        # Define a data divisora
        $dtDivisora = "01/01/2002";

        # Pega a data de admissão
        $pessoal = new Pessoal();
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

        # Compara se a adimissão é anterior a data divisora
        if (strtotime(date_to_bd($dtAdmissao)) < strtotime(date_to_bd($dtDivisora))) {
            return true;
        } else {
            return false;
        }
    }

    ###########################################################   

    /**
     * Método exibeEntregouCtc
     * Exibe Sim / Não ou N/I para o servidor com relação a se entregou ou não o CTC
     * 
     * @param string $idServidor    null idServidor do servidor
     */
    public function exibeEntregouCtc($idServidor = null) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        } else {
            # Verifica se o servidor precisa entregar o CTC
            if ($this->precisaEntregarCtc($idServidor)) {

                # Inicia o banco de Dados
                $pessoal = new Pessoal();

                # Monta o select
                $select = "SELECT entregouCtc
                             FROM tbservidor
                            WHERE idServidor = {$idServidor}";

                $row = $pessoal->select($select, false);

                if ($row[0] == "s") {
                    label("Sim", "success");
                } elseif ($row[0] == "n") {
                    label("Não", "alert");
                } else {
                    label("Não Informado");
                }
            } else {
                echo "Não Precisa";
            }
        }
    }

    ###########################################################   

    /**
     * Método exibeEntregouCtc
     * Exibe Sim / Não ou N/I para o servidor com relação a se entregou ou não o CTC
     * 
     * @param string $idServidor    null idServidor do servidor
     */
    public function exibeEntregouCtcRelatorio($idServidor = null) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        } else {
            # Verifica se o servidor precisa entregar o CTC
            if ($this->precisaEntregarCtc($idServidor)) {

                # Inicia o banco de Dados
                $pessoal = new Pessoal();

                # Monta o select
                $select = "SELECT entregouCtc
                             FROM tbservidor
                            WHERE idServidor = {$idServidor}";

                $row = $pessoal->select($select, false);

                if ($row[0] == "s") {
                    return "Sim";
                } elseif ($row[0] == "n") {
                    return "Não";
                } else {
                    return "Não Informado";
                }
            } else {
                return "Não Precisa";
            }
        }
    }

    ##############################################################################################################################################

    /**
     * Método exibe_tempoContribuicao
     * Exibe os tempos de contribuição para serem exibidos na tabela
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function exibe_tempoContribuicao($idServidor) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        } else {
            $tempoServico = new TempoServico();
            
            # Pega o tempo de contribuição
            $total = $tempoServico->get_tempoContribuicao($idServidor);
            
            # Pega o tempo
            $ateData = $tempoServico->get_tempoTotalAntes31_12_21($idServidor);

            return "Tempo Geral: {$total} dias<br/>Até 31/12/2021: {$ateData} dias";
        }
    }

##############################################################################################################################################

    /**
     * Método get_data5AnosCargo
     * Retorna a data em que completa 5 anos no cargo efetivo
     * CONSIDERANDO os afastamentos 
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_data5AnosCargo($idServidor) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        } else {
            # Inicia a classe
            $pessoal = new Pessoal();

            # Quantos dias são 5 anos (1825 dias)
            $dias5anos = 365 * 5;

            # Pega a data de Admissão do servidor
            $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

            # Pega a data com 5 anos de serviço
            $dataRetorno = addAnos($dtAdmissao, 5, false);

            # Inicia as variaveis
            $diaTotal = $dias5anos;
            $diasCompensar = 0;

            # Verifica se teve algum afastamento
            if ($afastamentos->get_tempoAfastamentoSuspendeTempoServico($idServidor, $dataRetorno) > 0) {

                # Faz o laço
                do {

                    # Monta a data Final
                    $dataRetorno = addDias($dtAdmissao, $diaTotal, false);

                    echo "Data de retorno: ", $dataRetorno;
                    br();

                    # Pega o tempo de afastamento 
                    $tempoAfastamento = $afastamentos->get_tempoAfastamentoSuspendeTempoServico($idServidor, $dataRetorno);

                    echo "Tempo de Afastamento: ", $tempoAfastamento;
                    br();

                    # Dias Trabalhados
                    $diasTrabalhados = $diaTotal - $tempoAfastamento;

                    echo "Dias trabalhados: ", $diasTrabalhados;
                    br();
                    echo "Dias 5 anos: ", $dias5anos;
                    br();
                    echo "Dias Total: ", $diaTotal;
                    br();

                    # Dias para compensar
                    $diasCompensar = $tempoAfastamento;
                    echo "Dias para compensar: ", $diasCompensar;
                    br();

                    $diaTotal = $diasTrabalhados + (2 * $tempoAfastamento);
                    hr();
                } while ($diasTrabalhados < $dias5anos);
            }

            return $dataRetorno;
        }
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
}
