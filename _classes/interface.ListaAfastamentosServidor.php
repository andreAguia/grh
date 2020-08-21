<?php

class ListaAfastamentosServidor
{

    /**
     * Abriga as várias rotina referentes ao afastamento do servidor
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
        # Licença Geral
        $select = "(SELECT YEAR(tblicenca.dtInicial),
                           tblicenca.dtInicial,
                           tblicenca.numDias,
                           ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                           CONCAT(tbtipolicenca.nome,IF(alta=1,' - Com Alta',''))
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
                         'Licença Pêmio'
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
                          CONCAT('Férias ',tbferias.anoExercicio)
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
                          'Falta Abonada'
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
                          'Trabalhando no TRE'
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
                          'Folga TRE'
                     FROM tbfolga JOIN tbservidor USING (idServidor)
                                  JOIN tbpessoa USING (idPessoa)
                    WHERE tbservidor.idServidor = {$this->idServidor}";

        #######################
        # Licença sem vencimentos
        $select .= ") UNION (
                   SELECT YEAR(tblicencasemvencimentos.dtInicial),
                           tblicencasemvencimentos.dtInicial,
                                   tblicencasemvencimentos.numDias,
                                   ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1),
                                   tbtipolicenca.nome
                              FROM tblicencasemvencimentos JOIN tbservidor USING (idServidor)
                                                           JOIN tbpessoa USING (idPessoa)
                                                           JOIN tbtipolicenca USING (idTpLicenca)
                             WHERE tbservidor.idServidor = {$this->idServidor}";

        #######################                      
        $select .= ") ORDER BY 1 desc, 2 desc";

        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        $result = $pessoal->select($select);
        $cont = $pessoal->count($select);

        $tabela = new Tabela();
        $tabela->set_titulo('Afastamentos');
        $tabela->set_label(array('Ano', 'Data Inicial', 'Dias', 'Data Final', 'Descrição'));
        $tabela->set_align(array('center', 'center', 'center', 'center', 'left'));
        $tabela->set_funcao(array(null, "date_to_php", null, "date_to_php"));
        $tabela->set_width(array(10, 10, 5, 10, 65));
        $tabela->set_rowspan(0);
        $tabela->set_grupoCorColuna(0);
        $tabela->set_conteudo($result);
        $tabela->show();
    }

}
