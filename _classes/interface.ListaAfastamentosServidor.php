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
    private $interrompe = false;

    ###########################################################

    /**
     * Método Construtor
     */
    public function __construct($idServidor, $titulo = null) {

        $this->idServidor = $idServidor;

        if (!empty($titulo)) {
            $this->titulo = $titulo;
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

    public function set_interrompe($interrompe) {

        /**
         * define se será exibido somente os afastamentos que interrompem o tempo de serviço
         *
         * @syntax $input->set_interrompeTs([$interrompe]);
         */
        $this->interrompe = $interrompe;
    }

    ###############################################################

    public function exibeTabela() {

        /**
         * monta o select para toda a classe
         *
         * @syntax $input->exibeTabela();
         */
        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        ###############################################################33
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

        if ($this->interrompe) {
            $select .= " AND tbtipolicenca.tempoServico is true ";
        }

        #######################    
        # Licença Prêmio
        if (!$this->interrompe) {
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
        }

        #######################
        # Férias
        if (!$this->interrompe) {
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
        }

        #######################
        # Faltas abonadas
        if (!$this->interrompe) {
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
        }

        #######################
        # Trabalhando TRE
        if (!$this->interrompe) {
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
        }

        #######################
        # Folga TRE
        if (!$this->interrompe) {
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
        }



        #######################
        # Licença sem vencimentos
        $select .= ") UNION (
                   SELECT YEAR(tblicencasemvencimentos.dtInicial),
                           tblicencasemvencimentos.dtInicial,
                           tblicencasemvencimentos.numDias,
                           ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1),
                           CONCAT(tbtipolicenca.nome, IF(optouContribuir=1,' - Optou Pagar Rio Previdência','')),
                           CONCAT('tblicencasemvencimentos','&',idLicencaSemVencimentos)
                      FROM tblicencasemvencimentos JOIN tbservidor USING (idServidor)
                                                   JOIN tbpessoa USING (idPessoa)
                                                   JOIN tbtipolicenca USING (idTpLicenca)
                      WHERE tbservidor.idServidor = {$this->idServidor}";

        if ($this->interrompe) {
            $select .= " AND optouContribuir is not true ";
        }

        #######################                      
        $select .= ") ORDER BY 1 desc, 2 desc";

        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        $result = $pessoal->select($select);
        $cont = $pessoal->count($select);

        $tabela = new Tabela();
        $tabela->set_titulo($this->titulo);

        if ($this->interrompe) {
            $tabela->set_colunaSomatorio(2);
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
}
