<?php

class ListaAfastamentosServidor {

    /**
     * Lista os afastamentos de um servidor específico
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    private $idServidor = null;
    private $exibeObs = true;
    private $titulo = "Afastamentos";
    private $subtitulo = null;

    # Quanto a interrupção dos tempos
    private $semTempoServicoSemTempoContribuicao = null;
    private $semTempoServicoComTempoContribuicao = null;

    ###########################################################

    /**
     * Método Construtor
     */
    public function __construct($idServidor, $titulo = null, $subtitulo = null) {

        $this->idServidor = $idServidor;

        if (!empty($titulo)) {
            $this->titulo = $titulo;
        }

        if (!empty($subtitulo)) {
            $this->subtitulo = $subtitulo;
        }
    }

    ###############################################################

    public function exibeObs($exibe) {

        /**
         * informa se exibe ou não as observações
         *
         * @syntax $input->exibeObs([$exibe]);
         */
        $this->exibeObs = $exibe;
    }

    ###############################################################

    public function set_semTempoServicoComTempoContribuicao($tempoContribiocao) {

        /**
         * define se será exibido somente os afastamentos que interrompem o tempo de contribuição
         *
         * @syntax $input->set_tempoContribuicao([$tempoContribiocao]);
         */
        $this->semTempoServicoComTempoContribuicao = $tempoContribiocao;
    }

    ###############################################################

    public function set_semTempoServicoSemTempoContribuicao($tempoServico) {

        /**
         * define se será exibido somente os afastamentos que interrompem o tempo de serviço
         *
         * @syntax $input->set_tempoServico([$tempoServico]);
         */
        $this->semTempoServicoSemTempoContribuicao = $tempoServico;
    }

    ###############################################################

    public function montaSelect() {

        /**
         * monta o select para toda a classe
         *
         * @syntax $input->exibeTabela();
         */
        /*
         * Todas as licenças
         */

        #######################
        # Licença Geral
        $select = "(SELECT YEAR(tblicenca.dtInicial),
                           tblicenca.dtInicial,
                           tblicenca.numDias,
                           ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                           CONCAT(tbtipolicenca.nome,IF(alta=1,' - Com Alta','')),
                           CONCAT('tblicenca','&',tblicenca.idLicenca) 
                      FROM tblicenca JOIN tbservidor USING (idServidor)
                                     JOIN tbpessoa USING (idPessoa)
                                     JOIN tbtipolicenca USING (idTpLicenca)
                     WHERE tbservidor.idServidor = {$this->idServidor}";

        #######################    
        # Licença Prêmio
        $select .= ") UNION (
                  SELECT YEAR(tblicencapremio.dtInicial),
                         tblicencapremio.dtInicial,
                         tblicencapremio.numDias,
                         ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1),
                         'Licença Prêmio',
                         CONCAT('tblicencapremio','&',tblicencapremio.idLicencaPremio) 
                    FROM tblicencapremio JOIN tbservidor USING (idServidor)
                                         JOIN tbpessoa USING (idPessoa)
                    WHERE tbservidor.idServidor = {$this->idServidor}";

        #######################
        # Férias

        $select .= ") UNION (
                   SELECT YEAR(tbferias.dtInicial),
                          tbferias.dtInicial,
                          tbferias.numDias,
                          ADDDATE(tbferias.dtInicial,tbferias.numDias-1),
                          CONCAT('Férias ',tbferias.anoExercicio),
                          CONCAT('tbferias','&',tbferias.idFerias) 
                     FROM tbferias JOIN tbservidor USING (idServidor)
                                   JOIN tbpessoa USING (idPessoa)
                    WHERE tbservidor.idServidor = {$this->idServidor}";

        #######################
        # Faltas abonadas
        $select .= ") UNION (
                   SELECT YEAR(tbatestado.dtInicio),
                          tbatestado.dtInicio,
                          tbatestado.numDias,
                          ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1),
                          'Falta Abonada',
                          CONCAT('tbatestado','&',tbatestado.idAtestado)   
                     FROM tbatestado JOIN tbservidor USING (idServidor)
                                     JOIN tbpessoa USING (idPessoa) 
                    WHERE tbservidor.idServidor = {$this->idServidor}";

        #######################
        # Trabalhando TRE

        $select .= ") UNION (
                   SELECT YEAR(tbtrabalhotre.data),
                          tbtrabalhotre.data,
                          tbtrabalhotre.dias,
                          ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1),
                          'Trabalhando no TRE',
                          CONCAT('tbtrabalhotre','&',tbtrabalhotre.idTrabalhoTre)   
                     FROM tbtrabalhotre JOIN tbservidor USING (idServidor)
                                        JOIN tbpessoa USING (idPessoa)
                    WHERE tbservidor.idServidor = {$this->idServidor}";

        #######################
        # Folga TRE

        $select .= ") UNION (
                   SELECT YEAR(tbfolga.data),
                          tbfolga.data,
                          tbfolga.dias,
                          ADDDATE(tbfolga.data,tbfolga.dias-1),
                          'Folga TRE',
                          CONCAT('tbfolga','&',tbfolga.idFolga)                          
                     FROM tbfolga JOIN tbservidor USING (idServidor)
                                  JOIN tbpessoa USING (idPessoa)
                    WHERE tbservidor.idServidor = {$this->idServidor}";

        #######################
        # Licença sem vencimentos
        $select .= ") UNION (
                   SELECT YEAR(tblicencasemvencimentos.dtInicial),
                           tblicencasemvencimentos.dtInicial,
                           tblicencasemvencimentos.numDias,
                           IFNULL(tblicencasemvencimentos.dtretorno, ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1)),
                           CONCAT(tbtipolicenca.nome, IF(optouContribuir=1,' - (Optou Pagar RP)',' - (Optou NÃO Pagar RP)')),
                           CONCAT('tblicencasemvencimentos','&',idLicencaSemVencimentos)
                      FROM tblicencasemvencimentos JOIN tbservidor USING (idServidor)
                                                   JOIN tbpessoa USING (idPessoa)
                                                   JOIN tbtipolicenca USING (idTpLicenca)
                      WHERE tbservidor.idServidor = {$this->idServidor}";

        #######################                      
        $select .= ") ORDER BY 1 desc, 2 desc";

        /*
         * Somente as licenças que:
         *  - interrompem tempo de serviço
         *  - interrompem tempo de contribuição
         */

        if ($this->semTempoServicoSemTempoContribuicao) {

            #######################
            # Licença Geral
            $select = "(SELECT YEAR(tblicenca.dtInicial),
                           tblicenca.dtInicial,
                           tblicenca.numDias,
                           ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                           CONCAT(tbtipolicenca.nome,IF(alta=1,' - Com Alta','')),
                           CONCAT('tblicenca','&',tblicenca.idLicenca) 
                      FROM tblicenca JOIN tbservidor USING (idServidor)
                                     JOIN tbpessoa USING (idPessoa)
                                     JOIN tbtipolicenca USING (idTpLicenca)
                     WHERE tbservidor.idServidor = {$this->idServidor}
                       AND tbtipolicenca.tempoServico = 'Sim'";

            #######################
            # Licença sem vencimentos
            $select .= ") UNION (
                   SELECT YEAR(tblicencasemvencimentos.dtInicial),
                           tblicencasemvencimentos.dtInicial,
                           tblicencasemvencimentos.numDias,
                           ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1),
                           CONCAT(tbtipolicenca.nome, IF(optouContribuir=1,' - (Optou Pagar RP)',' - (Optou NÃO Pagar RP)')),
                           CONCAT('tblicencasemvencimentos','&',idLicencaSemVencimentos)
                      FROM tblicencasemvencimentos JOIN tbservidor USING (idServidor)
                                                   JOIN tbpessoa USING (idPessoa)
                                                   JOIN tbtipolicenca USING (idTpLicenca)
                      WHERE tbservidor.idServidor = {$this->idServidor}
                        AND (optouContribuir = 2 OR optouContribuir is NULL)";

            #######################                      
            $select .= ") ORDER BY 1 desc, 2 desc";
        }


        /*
         * Somente as licenças que:
         *  - interrompem tempo de serviço
         *  - NÃO interrompem tempo de contribuição
         */

        if ($this->semTempoServicoComTempoContribuicao) {

            #######################
            # Licença Geral
            $select = "SELECT YEAR(tblicencasemvencimentos.dtInicial),
                              tblicencasemvencimentos.dtInicial,
                              tblicencasemvencimentos.numDias,
                              ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1),
                              CONCAT(tbtipolicenca.nome, IF(optouContribuir=1,' - (Optou Pagar RP)',' - (Optou NÃO Pagar RP)')),
                              CONCAT('tblicencasemvencimentos','&',idLicencaSemVencimentos)
                         FROM tblicencasemvencimentos JOIN tbservidor USING (idServidor)
                         JOIN tbpessoa USING (idPessoa)
                         JOIN tbtipolicenca USING (idTpLicenca)
                        WHERE tbservidor.idServidor = {$this->idServidor}
                          AND optouContribuir = 1
                     ORDER BY 1 desc, 2 desc";
        }

        return $select;
    }

    ###############################################################

    public function exibeTabela() {

        /**
         * monta o select para toda a classe
         *
         * @syntax $input->exibeTabela();
         */
        /*
         * Todas as licenças
         */

        $select = $this->montaSelect();

        ###########################################33
        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        $result = $pessoal->select($select);
        $cont = $pessoal->count($select);

        $tabela = new Tabela();
        $tabela->set_titulo($this->titulo);
        $tabela->set_subtitulo($this->subtitulo);

        if ($this->semTempoServicoComTempoContribuicao OR $this->semTempoServicoSemTempoContribuicao) {
            $tabela->set_colunaSomatorio(2);
            $tabela->set_totalRegistro(false);
        }

        if ($this->exibeObs) {
            $tabela->set_label(['Ano', 'Data Inicial', 'Dias', 'Data Final', 'Descrição', "Obs"]);
            $tabela->set_funcao([null, "date_to_php", null, "date_to_php", null, "exibeObsLicenca"]);
        } else {
            $tabela->set_label(['Ano', 'Data Inicial', 'Dias', 'Data Final', 'Descrição']);
            $tabela->set_funcao([null, "date_to_php", null, "date_to_php", null]);
        }
        $tabela->set_align(['center', 'center', 'center', 'center', 'left']);
        $tabela->set_width([10, 10, 5, 10, 50, 15]);
        $tabela->set_rowspan(0);
        $tabela->set_grupoCorColuna(0);
        $tabela->set_conteudo($result);
        $tabela->show();
    }

    ###########################################################

    public function exibeRelatorio() {

        /**
         * Exibe um relatório com a relação dos servidores com afastamento
         *
         * @syntax $afast->exibeRelatorio();
         */
        $select = $this->montaSelect();

        ###########################################33
        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        $result = $pessoal->select($select);
        $cont = $pessoal->count($select);

        # Titulo
        tituloRelatorio($this->titulo);

        $tabela = new Relatorio();
        $tabela->set_cabecalhoRelatorio(false);
        $tabela->set_cabecalhoRelatorioGeral(false);
        $tabela->set_menuRelatorio(false);
        $tabela->set_dataImpressao(false);

        if ($this->semTempoServicoComTempoContribuicao OR $this->semTempoServicoSemTempoContribuicao) {
            $tabela->set_colunaSomatorio(2);
            $tabela->set_totalRegistro(false);
        }


        $tabela->set_label(['Ano', 'Data Inicial', 'Dias', 'Data Final', 'Descrição']);
        $tabela->set_funcao([null, "date_to_php", null, "date_to_php", null]);
        $tabela->set_align(['center', 'center', 'center', 'center', 'left']);
        #$tabela->set_width([10, 10, 5, 10, 60]);
        $tabela->set_rowspan(0);
        #$tabela->set_grupoCorColuna(0);
        $tabela->set_conteudo($result);
        $tabela->show();
    }
    
    ###########################################################

    public function exibeRelatorio2() {

        /**
         * Exibe um relatório com a relação dos servidores com afastamento
         *
         * @syntax $afast->exibeRelatorio();
         */
        $select = $this->montaSelect();

        ###########################################33
        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        $result = $pessoal->select($select);
        $cont = $pessoal->count($select);

        $tabela = new Relatorio();
        $tabela->set_titulo($this->titulo);

        if ($this->semTempoServicoComTempoContribuicao OR $this->semTempoServicoSemTempoContribuicao) {
            $tabela->set_colunaSomatorio(2);
            $tabela->set_totalRegistro(false);
        }


        $tabela->set_label(['Ano', 'Data Inicial', 'Dias', 'Data Final', 'Descrição']);
        $tabela->set_funcao([null, "date_to_php", null, "date_to_php", null]);
        $tabela->set_align(['center', 'center', 'center', 'center', 'left']);
        #$tabela->set_width([10, 10, 5, 10, 60]);
        $tabela->set_numGrupo(0);
        $tabela->set_conteudo($result);
        $tabela->show();
    }
}
