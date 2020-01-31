<?php
class LicencaSemVencimentos{
 /**
  * Abriga as várias rotina referentes ao afastamento do servidor
  *
  * @author André Águia (Alat) - alataguia@gmail.com
  */

    
    private $linkEditar = NULL;
    private $atual = TRUE;
    
    ###########################################################

    public function set_linkEditar($linkEditar){
    /**
     * Informa a rotina de edição (se houver)
     *
     * @param $linkEditar string NULL O link da rotina de edição
     *
     * @syntax $input->set_linkEditar($linkEditar);
     */

        $this->linkEditar = $linkEditar;
    }

    ###########################################################

    public function set_atual($atual){
    /**
     * Informa se é somente os que estão atualmente em licença ou se são todos
     *
     * @param $atual BOOL TRUE TRUE para somente os que estão atualmente em licença
     *
     * @syntax $input->set_atual($atual);
     */

        $this->atual = $atual;
    }

    ###########################################################

    /**
    * Método Construtor
    */
    public function __construct(){

    }

    ###########################################################

    public function exibeTabela(){

    /**
     * Exibe uma tabela com a relação dos servidores comafastamento
     *
     * @syntax $input->exibeTabela();
     */

       # Inicia o banco de Dados
       $pessoal = new Pessoal();
       
       $data = date("Y-m-d");

       # Licença
       $select = 'SELECT tbservidor.idfuncional,
                         tbpessoa.nome,
                         tblicenca.dtInicial,
                         tblicenca.numDias,
                         ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1) as df,
                         CONCAT(tbtipolicenca.nome,"<br/>",IFNULL(tbtipolicenca.lei,"")),
                         tblicenca.idTpLicenca,
                         tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                    LEFT JOIN tblicenca USING (idServidor)
                                    LEFT JOIN tbtipolicenca USING (idTpLicenca)
                  WHERE tbservidor.situacao = 1
                    AND (idTpLicenca = 5 OR idTpLicenca = 8 OR idTpLicenca = 16)';
       
       if($this->atual){
           $select .= 'AND (("'.$data.'" BETWEEN tblicenca.dtInicial AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                        OR  (LAST_DAY("'.$data.'") BETWEEN tblicenca.dtInicial AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                        OR  ("'.$data.'" < tblicenca.dtInicial AND LAST_DAY("'.$data.'") > ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)))';
       }
       
       $select .= ' ORDER BY df desc';
       
       $result = $pessoal->select($select);
       $count = $pessoal->count($select);
       
       $titulo = 'Servidores Em Licença Sem vencimentos';

       $tabela = new Tabela();
       $tabela->set_titulo($titulo);
       $tabela->set_conteudo($result);
       $tabela->set_label(array('IdFuncional','Nome','Data Inicial','Dias','Data Final','Descrição','Doc.'));
       $tabela->set_align(array('center','left','center','center','center','left'));
       $tabela->set_funcao(array(NULL,NULL,"date_to_php",NULL,"date_to_php",NULL,"exibeBotaoDocumentacaoLicenca"));
       $tabela->set_idCampo('idServidor');
       $tabela->set_editar($this->linkEditar);

       if($count>0){
           $tabela->show();
       }else{
           titulotable($titulo);
           callout("Nenhum valor a ser exibido !","secondary");
       }
  }

  ###########################################################

    public function exibeRelatorio(){

    /**
     * Exibe uma tabela com a relação dos servidores comafastamento
     *
     * @syntax $input->exibeTabela();
     */

        # Inicia o banco de Dados
        $pessoal = new Pessoal();
       
        $data = date("Y-m-d");

        # Licença
        $select = 'SELECT tbservidor.idfuncional,
                          tbpessoa.nome,
                          tblicenca.dtInicial,
                          tblicenca.numDias,
                          ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1) as df,
                          CONCAT(tbtipolicenca.nome,"<br/>",IFNULL(tbtipolicenca.lei,"")),
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                     LEFT JOIN tblicenca USING (idServidor)
                                     LEFT JOIN tbtipolicenca USING (idTpLicenca)
                   WHERE tbservidor.situacao = 1
                     AND (idTpLicenca = 5 OR idTpLicenca = 8 OR idTpLicenca = 16)
                     AND (("'.$data.'" BETWEEN tblicenca.dtInicial AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                       OR  (LAST_DAY("'.$data.'") BETWEEN tblicenca.dtInicial AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                       OR  ("'.$data.'" < tblicenca.dtInicial AND LAST_DAY("'.$data.'") > ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)))
                ORDER BY df desc';

        $result = $pessoal->select($select);
        $count = $pessoal->count($select);

        $titulo = 'Servidores Em Licença Sem vencimentos';

        # Monta o Relatório
        $relatorio = new Relatorio();
        $relatorio->set_titulo($titulo);

        $relatorio->set_label(array('IdFuncional','Nome','Data Inicial','Dias','Data Final','Descrição'));
        $relatorio->set_conteudo($result);
        $relatorio->set_align(array('center','left','center','center','center','left'));
        $relatorio->set_funcao(array(NULL,NULL,"date_to_php",NULL,"date_to_php"));
        $relatorio->show();
  }

  ###########################################################


}
