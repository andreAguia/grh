<?php
class ReducaoCargaHoraria{
 /**
  * Exibe as informações sobre a Redução da Carga Horária 
  * 
  * @author André Águia (Alat) - alataguia@gmail.com
  * 
  */
    
    private $idServidor = NULL;
    
    ###########################################################
    
    public function __construct($idServidor = NULL){
                
    /**
     * Inicia a classe e preenche o idServidor
     */    
        
        if(!is_null($idServidor)){
            $this->idServidor = $idServidor;
        }
    }
        
    ###########################################################
    
    public function set_idServidor($idServidor){
    /**
     * Informa o idServidor quando não se pode informar no instanciamento da classe
     * 
     * @param $idServidor string NULL O idServidor
     * 
     * @syntax $input->set_id($id);  
     */
        
        $this->set_idServidor = $idServidor;
    }
    
    ###########################################################

    function get_dados($idReducao){

    /**
     * Informe os dados de uma redução
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if(vazio($idReducao)){
            alert("É necessário informar o id da Redução.");
            return;
        }

        # Pega os dados
        $select = 'SELECT * ,
                          DATE_SUB((ADDDATE(dtInicio, INTERVAL periodo MONTH)),INTERVAL 1 DAY) dtTermino
                     FROM tbreducao
                    WHERE idReducao = '.$idReducao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);

        # Retorno
        return $row;
    }

    ###########################################################

    function get_dadosAnterior($idReducao){

    /**
     * Informe os dados de uma redução imediatamente anterior cronológicamente
     * 
     * @note Usado para para pegar os dados da solicitação anterior quando for renovação
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if(vazio($idReducao)){
            alert("É necessário informar o id da Redução.");
            return;
        }
        
        $dados = $this->get_dados($idReducao);
        $idServidor = $dados["idServidor"];
        
        # Cria um array para encontrar o anterior
        $select = "SELECT idReducao
                     FROM tbreducao
                    WHERE idServidor = $idServidor
                    ORDER BY dtInicio DESC";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Percorre o array para encontrar o anterior
        foreach($row as $redux){
            
        }
        

        # Pega os dados
        $select = 'SELECT * ,
                          DATE_SUB((ADDDATE(dtInicio, INTERVAL periodo MONTH)),INTERVAL 1 DAY) dtTermino
                     FROM tbreducao
                    WHERE idReducao = '.$idReducao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);

        # Retorno
        return $row;
    }

    ###########################################################
    
    function get_numProcesso($idServidor = NULL){

    /**
     * Informe o número do processo de solicitação de redução de carga horária de um servidor
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Verifica se foi informado
        if(vazio($idServidor)){
            $idServidor = $this->idServidor;
        }
        
        # Pega os dias publicados
        $select = 'SELECT processoReducao
                     FROM tbservidor
                    WHERE idServidor = '.$idServidor;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Retorno
        return $row[0];
    }
    
    ###########################################################
    
    function get_numProcessoAntigo($idServidor = NULL){

    /**
     * Informe o número do processo Antigo de solicitação de redução de carga horária de um servidor
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Verifica se foi informado
        if(vazio($idServidor)){
            $idServidor = $this->idServidor;
        }
        
        # Pega os dias publicados
        $select = 'SELECT processoAntigoReducao
                     FROM tbservidor
                    WHERE idServidor = '.$idServidor;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Retorno
        return $row[0];
    }
    
    ###########################################################
    
    function get_numeroSolicitacoes(){

    /**
     * Informe o número de solicitações de redução de carga horária de um servidor
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT idReducao
                     FROM tbreducao
                    WHERE idServidor = '.$this->idServidor;
        
        $pessoal = new Pessoal();
        $row = $pessoal->count($select,FALSE);
        
        # Retorno
        return $row[0];
    }
    
    ###########################################################
    
    function get_dataAtoReitor($idReducao){

    /**
     * Informe a data do ato do reitor
     */
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT dtAtoReitor
                     FROM tbreducao
                    WHERE idReducao = '.$idReducao;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Retorno
        return date_to_php($row[0]);
    }
    
    ###########################################################
    
    
    function get_ultimaSolicitacaoAberto(){

    /**
     * Informe o número de solicitações de redução de carga horária de um servidor
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT idReducao
                     FROM tbreducao
                    WHERE NOT arquivado AND idServidor = '.$this->idServidor;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        $quantidade = $pessoal->count($select,FALSE);
        
        # Retorno
        if($quantidade > 0){
            return $row[0];
        }else{
            return NULL;
        }
    }
    
    ###########################################################
    
    function get_dadosCiInicio($idReducao){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT numCiInicio,
                        dtCiInicio,
                        dtInicio,
                        dtPublicacao,
                        pgPublicacao,
                        periodo
                   FROM tbreducao
                  WHERE idReducao = $idReducao";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        return $dados;
    }
    
    ###########################################################
    
    function get_dadosCi90($idReducao){
        
    /**
     * Informa os dados da ci de 90 dias
     */
        
        # Pega os dados
        $select="SELECT numCi90,
                        dtCi90,
                        dtPublicacao,
                        pgPublicacao,
                        DATE_SUB((ADDDATE(dtInicio, INTERVAL periodo MONTH)),INTERVAL 1 DAY)
                   FROM tbreducao
                  WHERE idReducao = $idReducao";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        return $dados;
    }
    
    ###########################################################
    
    function get_dadosReducao($idReducao){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT *
                   FROM tbreducao
                  WHERE idReducao = $idReducao";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        return $dados;
    }
    
    ###########################################################
    
    function get_dadosCiTermino($idReducao){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT numCitermino,
                        dtCiTermino,
                        dtInicio,
                        dtPublicacao,
                        pgPublicacao,
                        periodo,
                        ADDDATE(dtInicio,INTERVAL periodo MONTH)
                   FROM tbreducao
                  WHERE idReducao = $idReducao";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        return $dados;
    }
    
    ###########################################################
    
    function exibeDadosPericia($idReducao){

    /**
     * Informe os dados da perícia de uma solicitação de redução de carga horária específica
     * 
     * @obs Usada na tabela inicial do cadastro de redução
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT dtEnvioPericia, dtChegadaPericia, dtAgendadaPericia
                     FROM tbreducao
                    WHERE idReducao = '.$idReducao;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Trata a data de envio a perícia
        if(vazio($row[0])){
            $dtEnvioPericia = "---";
        }else{
            $dtEnvioPericia = date_to_php($row[0]);
        }
        
        # Trata a data de chegada a perícia
        if(vazio($row[1])){
            $dtChegadaPericia = "---";
        }else{
            $dtChegadaPericia = date_to_php($row[1]);
        }
        
        # Trata a data de agendamento da perícia
        if(vazio($row[2])){
            $dtAgendadaPericia = "---";
        }else{
            $dtAgendadaPericia = date_to_php($row[2]);
        }
        
        # Retorno
        $retorno = "Enviado em:    ".$dtEnvioPericia."<br/>"
                 . "Chegou  em:    ".$dtChegadaPericia."<br/>"
                 . "Agendado para: ".$dtAgendadaPericia;
                                
        return $retorno;
    }
    
    ###########################################################
    
    function exibePeriodo($idReducao){

    /**
     * Informe os dados da período de uma solicitação de redução de carga horária específica
     * 
     * @obs Usada na tabela inicial do cadastro de redução
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT dtInicio, 
                          periodo,
                          DATE_SUB((ADDDATE(dtInicio, INTERVAL periodo MONTH)),INTERVAL 1 DAY),
                          resultado
                     FROM tbreducao
                    WHERE idReducao = '.$idReducao;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Retorno
        if($row[3] == 1){
        
            # Trata a data de Início
            if(vazio($row[0])){
                $dtInicio = "---";
            }else{
                $dtInicio = date_to_php($row[0]);
            }

            # Trata o período
            if(vazio($row[1])){
                $periodo = "---";
            }else{
                $periodo = $row[1]." m";
            }

            # Trata a data de término
            if(vazio($row[2])){
                $dttermino = "---";
            }else{
                $dttermino = date_to_php($row[2]);
            }
        
            $retorno = "Início : ".$dtInicio."<br/>"
                     . "Período: ".$periodo."<br/>"
                     . "Término: ".$dttermino;
        }else{
            $retorno = NULL;
        }
        
        # Verifica se estamos a 90 dias da data Termino
        if(!vazio($row[2])){
            $hoje = date("d/m/Y");
            $dias = dataDif($hoje, $dttermino);

            if(($dias > 0) AND ($dias < 90)){
                if($dias == 1){
                    $retorno.= "<br/><span title='Falta apenas $dias dia para o término do benefício. Entrar em contato com o servidor para avaliar renovação do benefício!' class='warning label'>Falta apenas $dias dia</span>";
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
    
    function exibeCi($idReducao){

    /**
     * Informe os dados da CI de uma solicitação de redução de carga horária específica
     * 
     * @obs Usada na tabela inicial do cadastro de redução
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT numCiInicio, numCiTermino, resultado
                     FROM tbreducao
                    WHERE idReducao = '.$idReducao;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Retorno
        if($row[2] == 1){
            $retorno = "CI Início  : ".trataNulo($row[0])."<br/>"
                     . "CI Término : ".trataNulo($row[1]);
        }else{
            $retorno = NULL;
        }
        
        return $retorno;
    }
    
    ###########################################################
    
    function exibeBotaoDocumentos($idReducao){

    /**
     * Exibe o botão de imprimir os documentos de uma solicitação de redução de carga horária específica
     * 
     * @obs Usada na tabela inicial do cadastro de redução
     */
        
        # Pega os dados
        $dados = $this->get_dados($idReducao);
        
        # Pega os dados
        $resultado = $dados["resultado"];
        $ciInicio = $dados["numCiInicio"];
        $ciTermino = $dados["numCiTermino"];
        $atoReitor = date_to_php($dados["dtAtoReitor"]);
        $ci90 = $dados["numCi90"];
        $dtTermino = date_to_php("dtTermino");
        $tipo = $dados["tipo"];
        
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
        
        # Nome do botão do Ato
        $nomeBotaoAto = "Ato do Reitor";
        if(!is_null($atoReitor)){
            $nomeBotaoAto = "Ato do Reitor ".$atoReitor;
        }
        
        $menu = new Menu("menuDocumentos");
        
        # Despachos
        if($tipo == 2){ // Somente Renovação
            $menu->add_item('link',"\u{1F5A8} Despacho Para Perícia",'?fase=despachoPerícia&id='.$idReducao);
        }
            
        # Retorno
        if($resultado == 1){
            
            # Ci Início
            $menu->add_item('link',"\u{1F5A8} ".$nomeBotaoInicio,'?fase=ciInicioForm&id='.$idReducao);
            
            # Ci 90 dias
            if(($dias >= 0) AND($dias <= 90)){
                $menu->add_item('link',"\u{1F5A8} ".$nomeBotao90,'?fase=ci90Form&id='.$idReducao);
            }
                
            # Ci Término    
            $menu->add_item('link',"\u{1F5A8} ".$nomeBotaotermino,'?fase=ciTerminoForm&id='.$idReducao);
            
            # Ato do Reitor
            $menu->add_item('link',"\u{1F5A8} ".$nomeBotaoAto,'?fase=atoReitorForm&id='.$idReducao);
            /*
            
            $tamanhoImage = 20;
            if(($dias >= 0) AND($dias <= 90)){
                $menu = new MenuGrafico(4);
            }else{
                $menu = new MenuGrafico(3);
            }
            
            # Ci Início
            $botao = new BotaoGrafico();
            $botao->set_url('?fase=ciInicioForm&id='.$idReducao);
            $botao->set_label($nomeBotaoInicio);
            $botao->set_imagem(PASTA_FIGURAS.'print.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Imprime a Ci de início');
            $menu->add_item($botao);
            
            # Ci 90 dias
            if(($dias >= 0) AND($dias <= 90)){
                $botao = new BotaoGrafico();
                $botao->set_url('?fase=ci90Form&id='.$idReducao);
                $botao->set_label($nomeBotao90);
                $botao->set_imagem(PASTA_FIGURAS.'print.png',$tamanhoImage,$tamanhoImage);
                $botao->set_title('Imprime a Ci de 90 Dias');
                $menu->add_item($botao);
            }

            # Ci Término
            $botao = new BotaoGrafico();
            $botao->set_url('?fase=ciTerminoForm&id='.$idReducao);
            $botao->set_label($nomeBotaotermino);
            $botao->set_imagem(PASTA_FIGURAS.'print.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Imprime a Ci de término');
            $menu->add_item($botao);
            
            # Ato do Reitor
            $botao = new BotaoGrafico();
            $botao->set_label($nomeBotaoAto);
            $botao->set_url('?fase=atoReitorForm&id='.$idReducao);
            $botao->set_imagem(PASTA_FIGURAS.'print.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Imprime o Ato do Reitor');
            $menu->add_item($botao);
            
            $menu->show();
             * 
             */
            
        }
        
        $menu->show();
    }
    
    ###########################################################
    
    function exibePublicacao($idReducao){

    /**
     * Informe os dados da Publicação de uma solicitação de redução de carga horária específica
     * 
     * @obs Usada na tabela inicial do cadastro de redução
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT dtPublicacao, pgPublicacao, resultado
                     FROM tbreducao
                    WHERE idReducao = '.$idReducao;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Retorno
        if($row[2] == 1){
            if(is_null($row[0])){
                $retorno = trataNulo($row[0]);
            }else{
                $retorno = date_to_php($row[0])."<br/>Pag.: ".trataNulo($row[1]);
            }
            
        }else{
            $retorno = NULL;
        }
                                
        return $retorno;
    }
    
    ###########################################################
    
    function exibeResultado($idReducao){

    /**
     * Informe os dados do resultado de uma solicitação de redução de carga horária específica
     * 
     * @obs Usada na tabela inicial do cadastro de redução
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT resultado, pendencia
                     FROM tbreducao
                    WHERE idReducao = '.$idReducao;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        $resultado = $row[0];
        $dataCiencia = $row[1];
        
        # Verifica o resultado
        switch ($resultado){
            case NULL:
                $retorno = $resultado;
                break;
            
            case 1:
                $retorno = "Deferido";
                
                # Data da Ciência
                if(!is_null($dataCiencia)){
                    
                }
                break;
            
            case 2:
                $retorno = "Indeferido";
                break;
            
            case 3:
                $retorno = "Interrompido";
                break;
        }
        
        # Verifica se há pendências
        if($row[1] == 1){
            $retorno.= "<br/><span title='Existem pendências nessa solicitação de redução de carga horária!' class='warning label'>Pendências</span>";
        }
                                
        return $retorno;
    }
    
    ###########################################################
    
    function exibeStatus($idReducao){

    /**
     * Informe o status de uma solicitação de redução de carga horária específica
     * 
     * @obs Usada na tabela inicial do cadastro de redução
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT status, pendencia
                     FROM tbreducao
                    WHERE idReducao = '.$idReducao;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        $retorno = NULL;
        
        # Verifica o status
        switch ($row[0]){
            case 1:
                $retorno = "Em Aberto";
                break;
            
            case 2:
                $retorno = "Vigente";
                break;
            
            case 3:
                $retorno = "Arquivado";
                break;
        }
                                
        return $retorno;
    }
    
    ###########################################################
    
    function mudaStatus($idServidor = NULL){

    /**
     * 
     * Percorre a tabela de redução de carga horária alterando o status
     * 
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT resultado, 
                          status,
                          dtInicio,
                          periodo,
                          idReducao
                     FROM tbreducao';
        
        if(!is_null($idServidor)){
            $select .= ' WHERE idServidor = "'.$idServidor.'"';
        } 
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select);
        
        # Percorre o array
        foreach($row as $reducao){
            
            $resultado = $reducao[0];
            $status = $reducao[1];
            $dtInicio = date_to_php($reducao[2]);
            $periodo = $reducao[3];
            $idReducao = $reducao[4];
            
            $statusCerto = NULL;
            
            # Preenche o status de acordo com o resultado            
            switch ($resultado){

                # Resultado: nulo - Ainda não saiu o resultado
                # Status: 1 - Em aberto
                case NULL:
                    $statusCerto = 1;
                    break;

                # Resultado: 1 - Deferido
                # Status:    2 - Vigente até o término do benefício, após essa data passa para 3 - Arquivado
                case 1:

                    # Verifica se já está cadastrada a data de início e o período
                    if((is_null($dtInicio)) OR (is_null($periodo))){
                        $statusCerto = 2;
                    }else{
                        $dtTermino = addMeses($dtInicio,$periodo);

                        # Verifica se a data de término já passou
                        if(jaPassou($dtTermino)){
                            $statusCerto = 3; // Arquivado
                        }else{
                            $statusCerto = 2; // Vigente
                        }
                    }
                    break;

                # Resultado: 2 - Indeferido
                # Status:    3 - Arquivado
                case 2:
                case 3:
                    $statusCerto = 3;
                    break;
                
            }
            
            # Verifica se o status está correto, senão grava o correto
            if($status <> $statusCerto){
                # Define a tabela 
                $pessoal->set_tabela("tbreducao");
                $pessoal->set_idCampo("idReducao");
                $pessoal->gravar("status",$statusCerto,$idReducao);
                
            }
        }        
    }
    
    ###########################################################
}