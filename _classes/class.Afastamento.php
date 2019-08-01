<?php
class Afastamento{
 /**
  * Abriga as várias rotina referentes ao afastamento do servidor
  *
  * @author André Águia (Alat) - alataguia@gmail.com
  */


    private $ano = NULL;
    private $mes = NULL;
    private $lotacao = NULL;
    private $linkEditar = NULL;
    private $idFuncional = TRUE;
    private $nomeSimples = FALSE;

    ###########################################################

    /**
    * Método Construtor
    */
    public function __construct(){

    }

    ###########################################################

    public function set_mes($mes){
    /**
     * Informa o mês do afastamento
     *
     * @param $mes string NULL O mês
     *
     * @syntax $input->set_mes($mes);
     */

        $this->mes = $mes;
    }

    ###########################################################

    public function set_ano($ano){
    /**
     * Informa o ano do afastamento
     *
     * @param $ano string NULL O ano
     *
     * @syntax $input->set_ano($ano);
     */

        $this->ano = $ano;
    }

    ###########################################################

    public function set_lotacao($lotacao){
    /**
     * Informa a lotação dos servidores com afastamento
     *
     * @param $lotacao string NULL A lotacao
     *
     * @syntax $input->set_lotacao($lotacao);
     */

        $this->lotacao = $lotacao;
    }

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

    public function set_idFuncional($idFuncional){
    /**
     * Informa se terá a coluna de idFuncional ou não. Usado para economizar espaço
     *
     * @param $idFuncional bool NULL Se terá ou não
     *
     * @syntax $input->set_idFuncional($idFuncional);
     */

        $this->idFuncional = $idFuncional;
    }

    ###########################################################

    public function set_nomeSimples($nomeSimples){
    /**
     * Informa se terá o nome simples ou completo do servidor
     *
     * @param $nomeSimples bool NULL Se terá ou não
     *
     * @syntax $input->set_nomeSimples($nomeSimples);
     */

        $this->nomeSimples = $nomeSimples;
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

       # Constroi a data
       $data = $this->ano.'-'.$this->mes.'-01';

       # Licença
       $select = '(SELECT ';

       if($this->idFuncional){
         $select .= 'tbservidor.idfuncional,';
       }

       $select .= '       tbpessoa.nome,
                          tblicenca.dtInicial,
                          tblicenca.numDias,
                          ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                          CONCAT(tbtipolicenca.nome,"<br/>",IFNULL(tbtipolicenca.lei,"")),
                         tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                          JOIN tbhistlot USING (idServidor)
                                          JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     LEFT JOIN tblicenca USING (idServidor)
                                     LEFT JOIN tbtipolicenca USING (idTpLicenca)
                   WHERE tbservidor.situacao = 1
                     AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND (("'.$data.'" BETWEEN tblicenca.dtInicial AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                      OR  (LAST_DAY("'.$data.'") BETWEEN tblicenca.dtInicial AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                      OR  ("'.$data.'" < tblicenca.dtInicial AND LAST_DAY("'.$data.'") > ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)))';
       # lotacao
       if(!is_null($this->lotacao)){
           # Verifica se o que veio é numérico
           if(is_numeric($this->lotacao)){
               $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
           }else{ # senão é uma diretoria genérica
               $select .= ' AND (tblotacao.DIR = "'.$this->lotacao.'")';
           }
       }

       $select .= ') UNION (
                 SELECT ';

                 if($this->idFuncional){
                   $select .= 'tbservidor.idfuncional,';
                 }

     $select .= '       tbpessoa.nome,
                        tblicencapremio.dtInicial,
                        tblicencapremio.numDias,
                        ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1),
                        (SELECT CONCAT(tbtipolicenca.nome,"<br/>",IFNULL(tbtipolicenca.lei,"")) FROM tbtipolicenca WHERE idTpLicenca = 6),
                         tbservidor.idServidor
                   FROM tbtipolicenca,tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                                      JOIN tbhistlot USING (idServidor)
                                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                                 LEFT JOIN tblicencapremio USING (idServidor)
                   WHERE tbtipolicenca.idTpLicenca = 6 AND tbservidor.situacao = 1
                     AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND (("'.$data.'" BETWEEN tblicencapremio.dtInicial AND ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1))
                      OR  (LAST_DAY("'.$data.'") BETWEEN tblicencapremio.dtInicial AND ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1))
                      OR  ("'.$data.'" < tblicencapremio.dtInicial AND LAST_DAY("'.$data.'") > ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)))';

       # lotacao
       if(!is_null($this->lotacao)){
           # Verifica se o que veio é numérico
           if(is_numeric($this->lotacao)){
               $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
           }else{ # senão é uma diretoria genérica
               $select .= ' AND (tblotacao.DIR = "'.$this->lotacao.'")';
           }
       }

       $select .= ') UNION (
                  SELECT ';

                  if($this->idFuncional){
                    $select .= 'tbservidor.idfuncional,';
                  }

      $select .= '       tbpessoa.nome,
                         tbferias.dtInicial,
                         tbferias.numDias,
                         ADDDATE(tbferias.dtInicial,tbferias.numDias-1),
                         CONCAT("Férias ",tbferias.anoExercicio),
                         tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                    LEFT JOIN tbferias USING (idServidor)
                   WHERE tbservidor.situacao = 1
                     AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND (("'.$data.'" BETWEEN tbferias.dtInicial AND ADDDATE(tbferias.dtInicial,tbferias.numDias-1))
                      OR  (LAST_DAY("'.$data.'") BETWEEN tbferias.dtInicial AND ADDDATE(tbferias.dtInicial,tbferias.numDias-1))
                      OR  ("'.$data.'" < tbferias.dtInicial AND LAST_DAY("'.$data.'") > ADDDATE(tbferias.dtInicial,tbferias.numDias-1)))';
       # lotacao
       if(!is_null($this->lotacao)){
           # Verifica se o que veio é numérico
           if(is_numeric($this->lotacao)){
               $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
           }else{ # senão é uma diretoria genérica
               $select .= ' AND (tblotacao.DIR = "'.$this->lotacao.'")';
           }
       }

       $select .= ') UNION (
                  SELECT ';

                  if($this->idFuncional){
                    $select .= 'tbservidor.idfuncional,';
                  }

      $select .= '       tbpessoa.nome,
                         tbatestado.dtInicio,
                         tbatestado.numDias,
                         ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1),
                         "Falta Abonada",
                         tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                    LEFT JOIN tbatestado USING (idServidor)
                   WHERE tbservidor.situacao = 1
                     AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND (("'.$data.'" BETWEEN tbatestado.dtInicio AND ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1))
                      OR  (LAST_DAY("'.$data.'") BETWEEN tbatestado.dtInicio AND ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1))
                      OR  ("'.$data.'" < tbatestado.dtInicio AND LAST_DAY("'.$data.'") > ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1)))';
       # lotacao
       if(!is_null($this->lotacao)){
           # Verifica se o que veio é numérico
           if(is_numeric($this->lotacao)){
               $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
           }else{ # senão é uma diretoria genérica
               $select .= ' AND (tblotacao.DIR = "'.$this->lotacao.'")';
           }
       }

       $select .= ') UNION (
                  SELECT ';

                  if($this->idFuncional){
                    $select .= 'tbservidor.idfuncional,';
                  }

        $select .= '     tbpessoa.nome,
                         tbtrabalhotre.data,
                         tbtrabalhotre.dias,
                         ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1),
                         "Trabalhando no TRE",
                         tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                    LEFT JOIN tbtrabalhotre USING (idServidor)
                   WHERE tbservidor.situacao = 1
                     AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND (("'.$data.'" BETWEEN tbtrabalhotre.data AND ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1))
                      OR  (LAST_DAY("'.$data.'") BETWEEN tbtrabalhotre.data AND ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1))
                      OR  ("'.$data.'" < tbtrabalhotre.data AND LAST_DAY("'.$data.'") > ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1)))';
       # lotacao
       if(!is_null($this->lotacao)){
           # Verifica se o que veio é numérico
           if(is_numeric($this->lotacao)){
               $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
           }else{ # senão é uma diretoria genérica
               $select .= ' AND (tblotacao.DIR = "'.$this->lotacao.'")';
           }
       }

       $select .= ') UNION (
                  SELECT ';

                  if($this->idFuncional){
                    $select .= 'tbservidor.idfuncional,';
                  }

      $select .= '       tbpessoa.nome,
                         tbfolga.data,
                         tbfolga.dias,
                         ADDDATE(tbfolga.data,tbfolga.dias-1),
                         "Folga TRE",
                         tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                    LEFT JOIN tbfolga USING (idServidor)
                   WHERE tbservidor.situacao = 1
                     AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND (("'.$data.'" BETWEEN tbfolga.data AND ADDDATE(tbfolga.data,tbfolga.dias-1))
                      OR  (LAST_DAY("'.$data.'") BETWEEN tbfolga.data AND ADDDATE(tbfolga.data,tbfolga.dias-1))
                      OR  ("'.$data.'" < tbfolga.data AND LAST_DAY("'.$data.'") > ADDDATE(tbfolga.data,tbfolga.dias-1)))';
       # lotacao
       if(!is_null($this->lotacao)){
           # Verifica se o que veio é numérico
           if(is_numeric($this->lotacao)){
               $select .= ' AND (tblotacao.idlotacao = "'.$this->lotacao.'")';
           }else{ # senão é uma diretoria genérica
               $select .= ' AND (tblotacao.DIR = "'.$this->lotacao.'")';
           }
       }

       if($this->idFuncional){
         $select .= ') ORDER BY 2, 3';
       }else{
         $select .= ') ORDER BY 1, 2';
       }

       $result = $pessoal->select($select);
       $cont = $pessoal->count($select);

       $tabela = new Tabela();
       $tabela->set_titulo('Servidores com Afastamentos');

       if($this->idFuncional){

         $tabela->set_label(array('IdFuncional','Nome','Data Inicial','Dias','Data Final','Descrição'));
         $tabela->set_align(array('center','left','center','center','center','left'));

         if($this->nomeSimples){
           $tabela->set_funcao(array(NULL,"get_nomeSimples","date_to_php",NULL,"date_to_php"));
         }else{
           $tabela->set_funcao(array(NULL,NULL,"date_to_php",NULL,"date_to_php"));
         }

         $tabela->set_rowspan(1);
         $tabela->set_grupoCorColuna(1);

       }else{

         $tabela->set_label(array('Nome','Data Inicial','Dias','Data Final','Descrição'));
         $tabela->set_align(array('left','center','center','center','left'));

         if($this->nomeSimples){
           $tabela->set_funcao(array("get_nomeSimples","date_to_php",NULL,"date_to_php"));
         }else{
           $tabela->set_funcao(array(NULL,"date_to_php",NULL,"date_to_php"));
         }


         $tabela->set_rowspan(0);
         $tabela->set_grupoCorColuna(0);
       }

       if(!vazio($this->linkEditar)){
           $tabela->set_idCampo('idServidor');
           $tabela->set_editar($this->linkEditar);
       }

       $tabela->set_conteudo($result);

       if($cont>0){
           $tabela->show();
       }else{
           titulotable('Servidores com Afastamentos');
           callout("Nenhum valor a ser exibido !","secondary");
       }
  }

  ###########################################################


}
