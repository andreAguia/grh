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

    /**
    * Método Construtor
    */
    public function __construct(){

    }

    ###########################################################

    function get_dados($idLicencaSemVencimentos){

    /**
     * Informe o número do processo de solicitação de redução de carga horária de um servidor
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if(vazio($idLicencaSemVencimentos)){
            alert("É necessário informar o id da Licença Sem Vencimentos.");
        }else{
            # Pega os dados
            $select = 'SELECT * ,
                              DATE_SUB((ADDDATE(dtInicial, INTERVAL periodo DAY)),INTERVAL 1 DAY) as dtTermino
                         FROM tblicencasemvencimentos
                        WHERE idLicencaSemVencimentos = '.$idLicencaSemVencimentos;

            $pessoal = new Pessoal();
            $row = $pessoal->select($select,FALSE);

            # Retorno
            return $row;
        }
    }

    ###########################################################

    function exibeStatus($idLicencaSemVencimentos){

    /**
     * Informe o status de uma solicitação de redução de carga horária específica
     *
     * @obs Usada na tabela inicial do cadastro de redução
     */
        # Pega os dados
        $dados = $this->get_dados($idLicencaSemVencimentos);
        
        # Pega os campos necessários
        $dtPublicacao = $dados["dtPublicacao"];
        $crp = $dados["crp"];
        $dtRetorno = $dados["dtRetorno"];
        
        $retorno = NULL;
        
        if(vazio($dtPublicacao)){
            $retorno = "Em Aberto";
        }else{
            if(vazio($dtRetorno)){
                $retorno = "Vigente";
            }else{
                if(vazio($crp)){
                    $retorno = "Aguardando CRP";
                }else{
                    $retorno = "Arquivado";
                }
            }
        }
        
        return $retorno;
    }

    ###########################################################

    function exibePeriodo($idLicencaSemVencimentos){

    /**
     * Informe os dados da período de uma solicitação de redução de carga horária específica
     *
     * @obs Usada na tabela inicial do cadastro de redução
     */
        # Pega os dados
        $dados = $this->get_dados($idLicencaSemVencimentos);
        
        # Pega os campos necessários
        $dtInicial = $dados["dtInicial"];
        $periodo = $dados["periodo"];
        $dtTermino = $dados["dtTermino"];
        $dtRetorno = $dados["dtRetorno"];

        # Retorno
        # Trata a data de Início
        if(!vazio($dtInicial)){
            $dtInicial = date_to_php($dtInicial);
        }

        # Trata o período
        if(!vazio($periodo)){
            $periodo = $periodo." dias";
        }

        # Trata a data de término
        if(!vazio($dtTermino)){
            $dtTermino = date_to_php($dtTermino);
        }
        
        # Trata a data de retorno
        if(!vazio($dtRetorno)){
            $dtRetorno = date_to_php($dtRetorno);
        }

        $retorno = "Início : ".trataNulo($dtInicial)."<br/>"
                 . "Período: ".trataNulo($periodo)."<br/>"
                 . "Término: ".trataNulo($dtTermino)."<br/>"
                 . "Retornou: ".trataNulo($dtRetorno);

        # Verifica se estamos a 90 dias da data Termino
        if((!vazio($dtTermino)) AND (vazio($dtRetorno))){
            $hoje = date("d/m/Y");
            $dias = dataDif($hoje, $dtTermino);

            if(($dias > 0) AND ($dias < 90)){
                if($dias == 1){
                    $retorno.= "<br/><span title='Falta Apenas $dias dia para o término do benefício. Entrar em contato com o servidor para avaliar renovação do benefício!' class='warning label'>Faltam $dias dias</span>";
                }else{
                    $retorno.= "<br/><span title='Faltam $dias dias para o término do benefício. Entrar em contato com o servidor para avaliar renovação do benefício!' class='warning label'>Faltam $dias dias</span>";
                }
            }elseif($dias == 0){
                $retorno.= "<br/><span title='Hoje Termina o benefício!' class='warning label'>Termina Hoje!</span>";
            }
        }
        
        return $retorno;
    }

     ###########################################################

    function exibeProcessoPublicacao($idLicencaSemVencimentos){

    /**
     * Informe o número do processo e a data da publicação de uma licença sem vencimentos
     *
     * @obs Usada na tabela inicial do cadastro de LSV
     */
        # Pega os dados
        $dados = $this->get_dados($idLicencaSemVencimentos);
        
        # Pega os campos necessários
        $processo = $dados["processo"];
        $dtPublicacao = $dados["dtPublicacao"];
        
        # Trata a data de retorno
        if(!vazio($dtPublicacao)){
            $dtPublicacao = date_to_php($dtPublicacao);
        }

        $retorno = "Processo : ".trataNulo($processo)."<br/>"
                 . "Publicação: ".trataNulo($dtPublicacao);
        
        return $retorno;
    }

     ###########################################################

    function exibeCrp($idLicencaSemVencimentos){

    /**
     * Informe se o servidor entregou o CRp e o prazo de entrega
     *
     * @obs Usada na tabela inicial do cadastro de LSV
     */
        # Pega os dados
        $dados = $this->get_dados($idLicencaSemVencimentos);
        
        # Pega os campos necessários
        $crp = $dados["crp"];
        $dtRetorno = $dados["dtRetorno"];
        
        # Verifica p CRP
        if($crp){
            echo "Sim";
        }else{
            echo "Não";
            
            # Trata a data de retorno
            if(!vazio($dtRetorno)){
                $dtRetorno = date_to_php($dtRetorno);
            }
            
            # Verifica se estamos a 90 dias da data Termino
            if(!vazio($dtRetorno)){
                
                # Calcula a data limite da entrega
                $dtLimite = addDias($dtRetorno,90);
                
                if(jaPassou($dtLimite)){
                    echo "<br/><br/><span title='Já passou a data da entrega do CRP' class='warning label'>Data já Passou!</span>";
                }else{
                    p("Entregar até: $dtLimite","plsvPassou");
                }
                
                # Calcula quantos dias faltam para essa data
                $hoje = date("d/m/Y");
                $dias = dataDif($hoje, $dtLimite);

                if(($dias > 0) AND ($dias < 90)){
                    if($dias == 1){
                        echo "<span title='Falta Apenas $dias dia para o término do prazo para entregar o CRP.' class='warning label'>Falta $dias dia</span>";
                    }else{
                        echo "<span title='Faltam $dias dias para o término do prazo para entregar o CRP!' class='warning label'>Faltam $dias dias</span>";
                    }
                }elseif($dias == 0){
                    echo "<span title='Hoje Termina o benefício!' class='warning label'>Termina Hoje!</span>";
                }
            }
        }
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
