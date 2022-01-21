<?php

class ListaAfastamentosServidorPremio
{

    /**
     * Lista os afastamentos de um servidor que afetam o período aquisitivo da Licença prêmio
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    private $idServidor = null;

    ###########################################################

    /**
     * Método Construtor
     */
    public function __construct($idServidor)
    {

        $this->idServidor = $idServidor;
    }

    ###############################################################

    public function exibeTabela()
    {

        /**
         * monta o select para toda a classe
         *
         * @syntax $input->exibeTabela();
         */
        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        ###############################################################33
        # Faltas
        $select = "(SELECT YEAR(tblicenca.dtInicial),
                           tblicenca.dtInicial,
                           tblicenca.numDias,
                           ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                           CONCAT(tbtipolicenca.nome,IF(alta=1,' - Com Alta',''))
                      FROM tblicenca JOIN tbservidor USING (idServidor)
                                     JOIN tbpessoa USING (idPessoa)
                                     JOIN tbtipolicenca USING (idTpLicenca)
                    WHERE tbservidor.idServidor = {$this->idServidor}
                       AND idTpLicenca = 25";

        #######################    
        # Licença Médica inicial com mais de 90 dias
        $select .= ") UNION (
                  SELECT YEAR(tblicenca.dtInicial),
                           tblicenca.dtInicial,
                           tblicenca.numDias,
                           ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                           CONCAT(tbtipolicenca.nome,IF(alta=1,' - Com Alta',''))
                      FROM tblicenca JOIN tbservidor USING (idServidor)
                                     JOIN tbpessoa USING (idPessoa)
                                     JOIN tbtipolicenca USING (idTpLicenca)
                    WHERE tbservidor.idServidor = {$this->idServidor}
                       AND idTpLicenca = 1";
                    
        #######################    
        # Licença Médica prorrogação com mais de 90 dias
        $select .= ") UNION (
                  SELECT YEAR(tblicenca.dtInicial),
                           tblicenca.dtInicial,
                           tblicenca.numDias,
                           ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                           CONCAT(tbtipolicenca.nome,IF(alta=1,' - Com Alta',''))
                      FROM tblicenca JOIN tbservidor USING (idServidor)
                                     JOIN tbpessoa USING (idPessoa)
                                     JOIN tbtipolicenca USING (idTpLicenca)
                    WHERE tbservidor.idServidor = {$this->idServidor}
                       AND idTpLicenca = 30";

        #######################
        # Licença sem vencimentos
        $select .= ") UNION (
                   SELECT YEAR(tblicencasemvencimentos.dtInicial),
                           tblicencasemvencimentos.dtInicial,
                                   tblicencasemvencimentos.numDias,
                                   CONCAT('tblicencasemvencimentos&',idLicencaSemVencimentos),
                                   tbtipolicenca.nome
                              FROM tblicencasemvencimentos JOIN tbservidor USING (idServidor)
                                                           JOIN tbpessoa USING (idPessoa)
                                                           JOIN tbtipolicenca USING (idTpLicenca)
                             WHERE tbservidor.idServidor = {$this->idServidor}";

        #######################                      
        $select .= ") ORDER BY 1 desc, 2 desc";
        
        # Mensagem
        callout("Aqui estão listados os afastamentos que PODEM afetar o período aquisitivo da licença prêmio.");

        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        $result = $pessoal->select($select);
        $cont = $pessoal->count($select);

        $tabela = new Tabela();
        $tabela->set_titulo('Afastamentos');
        $tabela->set_label(array('Ano', 'Data Inicial', 'Dias', 'Data Final', 'Descrição'));
        $tabela->set_align(array('center', 'center', 'center', 'center', 'left'));
        $tabela->set_funcao(array(null, "date_to_php", null, "exibeDtTermino"));
        $tabela->set_width(array(10, 10, 5, 10, 65));
        $tabela->set_rowspan(0);
        $tabela->set_grupoCorColuna(0);
        $tabela->set_conteudo($result);
        $tabela->show();
    }

}
