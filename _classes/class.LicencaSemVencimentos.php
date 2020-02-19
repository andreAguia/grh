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
            $select = 'SELECT *
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
        $dtTermino = $dados["dtTermino"];
        $dtSolicitacao = $dados["dtSolicitacao"];
        
        $retorno = NULL;
       
        # Se estiver vazio a data de publicação -> Em aberto
        if(vazio($dtPublicacao)){
            $retorno = "Em Aberto";
        }else{ 
            $retorno = "Vigente";
            
            # Se não tiver retorno 
            if(vazio($dtRetorno)){
                
                # Verifica se tem data de termino
                if(!vazio($dtTermino)) {
                    
                    $dtTermino = date_to_php($dtTermino);
                    
                    # Verifica se já passou
                    if(jaPassou($dtTermino)){
                        
                        # Se já passou, verifica se já enteogou o crp
                        if(vazio($crp)){
                            $retorno = "Aguardando CRP";
                        }else{
                            # Se entregou então fica arquivado
                            $retorno = "Arquivado";
                        }
                    }else{
                        # Se a data de término não acabou, é vigente
                        $retorno = "Vigente";
                    }   
                }
            }else{
                # Se já passou a data de retorno
                $dtRetorno = date_to_php($dtRetorno);
                if(jaPassou($dtRetorno)){
                    
                    # E não entregou o crp o status é aguardando o crp
                    if(vazio($crp)){
                        $retorno = "Aguardando CRP";
                    }else{
                        # Se entregou é arquivado
                        $retorno = "Arquivado";
                    }
                }else{
                    # Se não passou é vigente
                    $retorno = "Vigente";
                }
            }    
        }
        
        if(vazio($dtSolicitacao)){
            $retorno = "INCOMPLETO";
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
        $crp = $dados["crp"];

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
        $dtSolicitacao = $dados["dtSolicitacao"];
        
        # Trata a data de retorno
        if(!vazio($dtPublicacao)){
            $dtPublicacao = date_to_php($dtPublicacao);
        }
        
        # Trata a data de $dtSolicitacao
        if(!vazio($dtSolicitacao)){
            $dtSolicitacao = date_to_php($dtSolicitacao);
        }

        $retorno = "Solicitado em : ".trataNulo($dtSolicitacao)."<br/>"
                 . "Processo : ".trataNulo($processo)."<br/>"
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
        $dttermino = $dados["dtTermino"];
        
        # Verifica p CRP
        if($crp){
            echo "Sim";
        }else{
            echo "Não";
            
            # Verifica se estamos a 90 dias da data Termino
            if(!vazio($dtRetorno)){
                # Passa para o formato brasileiro
                $dtRetorno = date_to_php($dtRetorno);
                
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
            }else{
                if(!vazio($dttermino)){
                    # Passa para o formato brasileiro
                    $dttermino = date_to_php($dttermino);
                    
                    # Verifica se já passou 
                    if(jaPassou($dttermino)){
                        
                        # Calcula a data limite da entrega
                        $dtLimite = addDias($dttermino,90);
                        
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

    public function get_nomeLicenca($idTpLicenca){
    /**
     * Informa o nome da licença de forma compacta para ser exibida na tabela
     *
     * @param $idTpLicenca integer NULL O id do tipo de licença
     *
     * @syntax $input->get_nomeLicenca($idTpLicenca);
     */
        
        # Inicia o banco de Dados
        $pessoal = new Pessoal();
        
        # Pega o nome da Licença
        $tipo = $pessoal->get_nomeTipoLicenca($idTpLicenca);
        
        # Acha a posição do -
        $trac = stripos($tipo, "-");
        
        # Retira a primeira parte da string
        $pedaco = substr($tipo, $trac+1);
        
        return plm($pedaco);
    }

    ###########################################################

    public function exibeLista(){

    /**
     * Exibe uma tabela com a relação dos servidores comafastamento
     *
     * @syntax $input->exibeTabela();
     */

       # Inicia o banco de Dados
       $pessoal = new Pessoal();
       
       $data = date("Y-m-d");

       # Licença
       $select = 'SELECT idLicencaSemVencimentos,
                         CASE tipo
                             WHEN 1 THEN "Inicial"
                             WHEN 2 THEN "Renovação"
                             ELSE "--"
                         END,
                         idServidor,
                         idTpLicenca,
                         idLicencaSemVencimentos,
                         idLicencaSemVencimentos, 
                         idLicencaSemVencimentos,
                         idServidor
                    FROM tblicencasemvencimentos
           ORDER BY dtSolicitacao desc';
       
       $result = $pessoal->select($select);
       $count = $pessoal->count($select);
       
       $titulo = 'Licença Sem Vencimentos';

       $tabela = new Tabela();
       $tabela->set_titulo($titulo);
       $tabela->set_conteudo($result);
       
       $tabela->set_label(array("Status","Tipo","Nome","Licença Sem Vencimentos","Dados","Período","Entregou CRP?"));
       #$tabela->set_width(array(10,10,30,10,20,15));	
       $tabela->set_align(array("center","center","left","left","left","left"));
       #$tabela->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));

       $tabela->set_classe(array("LicencaSemVencimentos",NULL,"Pessoal","LicencaSemVencimentos","LicencaSemVencimentos","LicencaSemVencimentos","LicencaSemVencimentos"));
       $tabela->set_metodo(array("exibeStatus",NULL,"get_nome","get_nomeLicenca","exibeProcessoPublicacao","exibePeriodo","exibeCrp"));
       
       $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                                       'valor' => 'Em Aberto',
                                                       'operador' => '=',
                                                       'id' => 'emAberto'),  
                                                 array('coluna' => 0,
                                                       'valor' => 'Arquivado',
                                                       'operador' => '=',
                                                       'id' => 'arquivado'),
                                                 array('coluna' => 0,
                                                       'valor' => 'Vigente',
                                                       'operador' => '=',
                                                       'id' => 'vigenteReducao')   
                                                       ));
        
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
       $select = 'SELECT idLicencaSemVencimentos,
                         CASE tipo
                             WHEN 1 THEN "Inicial"
                             WHEN 2 THEN "Renovação"
                             ELSE "--"
                         END,
                         idServidor,
                         idTpLicenca,
                         idLicencaSemVencimentos,
                         idLicencaSemVencimentos, 
                         idLicencaSemVencimentos,
                         idServidor
                    FROM tblicencasemvencimentos
           ORDER BY dtSolicitacao desc';

        $result = $pessoal->select($select);
        $count = $pessoal->count($select);

        $titulo = 'Servidores Em Licença Sem vencimentos';

        # Monta o Relatório
        $relatorio = new Relatorio();
        $relatorio->set_titulo($titulo);
        
        $relatorio->set_label(array("Status","Tipo","Nome","Licença Sem Vencimentos","Dados","Período","Entregou CRP?"));
        $relatorio->set_align(array("center","center","left","left","left","left"));

        $relatorio->set_classe(array("LicencaSemVencimentos",NULL,"Pessoal","LicencaSemVencimentos","LicencaSemVencimentos","LicencaSemVencimentos","LicencaSemVencimentos"));
        $relatorio->set_metodo(array("exibeStatus",NULL,"get_nome","get_nomeLicenca","exibeProcessoPublicacao","exibePeriodo","exibeCrp"));
        
        $relatorio->set_conteudo($result);
        
        $relatorio->show();
  }

  ###########################################################
    
    function exibeBotaoDocumentos($idLicencaSemVencimentos){

    /**
     * Exibe o botão de imprimir os documentos
     * 
     * @obs Usada na tabela inicial do cadastro de LSV
     */
        
        # Pega os dados
        $dados = $this->get_dados($idLicencaSemVencimentos);
        
        # Pega os dados
        $ciInicio = $dados["numCiInicio"];
        $ciTermino = $dados["numCiTermino"];
        $ci90 = $dados["numCi90"];
        $dtTermino = date_to_php($dados["dtTermino"]);
        
        $dias = NULL;
        
        # Calcula os dias
        if(!is_null($dtTermino)){
            $hoje = date("d/m/Y");
            $dias = dataDif($hoje, $dtTermino);
        }        
        
        # Nome do botão de início
        $nomeBotaoInicio = "CI Início";
        if(!is_null($ciInicio)){
            $nomeBotaoInicio = "CI Início n° ".$ciInicio;
        }
        
        # Nome do botão de 90 Dias
        $nomeBotao90 = "CI 90 Dias";
        if(!is_null($ci90)){
            $nomeBotao90 = "CI 90 Dias n° ".$ci90;
        }
        
        # Nome do botão de Término
        $nomeBotaotermino = "CI Término";
        if(!is_null($ciTermino)){
            $nomeBotaotermino = "CI Término n° ".$ciTermino;
        }
        
        $menu = new Menu("menuBeneficios");
        
        # Despachos
        $menu->add_item('linkWindow',"\u{1F5A8} Despacho Para Perícia",'?fase=despachoPerícia&id='.$idReducao);
        
        # Ci Início
        $menu->add_item('link',"\u{1F5A8} ".$nomeBotaoInicio,'?fase=ciInicioForm&id='.$idReducao);

        # Ci 90 dias
        if(($dias >= 0) AND($dias <= 90)){
            $menu->add_item('link',"\u{1F5A8} ".$nomeBotao90,'?fase=ci90Form&id='.$idReducao);
        }

        # Ci Término    
        $menu->add_item('link',"\u{1F5A8} ".$nomeBotaotermino,'?fase=ciTerminoForm&id='.$idReducao);
        
        $menu->show();
    }
    
    ###########################################################


}
