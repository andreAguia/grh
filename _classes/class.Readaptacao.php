<?php
class Readaptacao{
 /**
  * Exibe as informações sobre a Readaptação de servidor
  * 
  * @author André Águia (Alat) - alataguia@gmail.com
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
    
    function get_dados($idReadaptacao){

    /**
     * Informe o número do processo de solicitação de redução de carga horária de um servidor
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Verifica se foi informado
        if(vazio($idReadaptacao)){
            alert("É necessário informar o id da Redução.");
            return;
        }
        
        # Pega os dados
        $select = 'SELECT *
                     FROM tbreadaptacao
                    WHERE idReadaptacao = '.$idReadaptacao;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Retorno
        return $row;
    }
    
    ###########################################################
    
    function exibeStatus($idReadaptacao){

    /**
     * Informe o status de uma solicitação de redução de carga horária específica
     * 
     * @obs Usada na tabela inicial do cadastro de redução
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT status, pendencia
                     FROM tbreadaptacao
                    WHERE idReadaptacao = '.$idReadaptacao;
        
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
    
    function exibeSolicitacao($idReadaptacao){

    /**
     * Informe a data da solicitação
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT dtSolicitacao, tipo
                     FROM tbreadaptacao
                    WHERE idReadaptacao = '.$idReadaptacao;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Verifica se é solicitado
        if($row[1] == 2){
            $retorno = date_to_php($row[0]);
        }else{
            $retorno = "";
        }
                                
        return $retorno;
    }
    
    ###########################################################
    
    function exibeDadosPericia($idReadaptacao){

    /**
     * Informe os dados da perícia de uma solicitação de redução de carga horária específica
     * 
     * @obs Usada na tabela inicial do cadastro de redução
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT dtEnvioPericia, dtChegadaPericia, dtAgendadaPericia, tipo
                     FROM tbreadaptacao
                    WHERE idReadaptacao = '.$idReadaptacao;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Verifica se é solicitado
        if($row[3] == 2){
        
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
        }else{
            $retorno = '';
        }
                                
        return $retorno;
    }
    
     ###########################################################
    
    function exibeResultado($idReadaptacao){

    /**
     * Informe os dados do resultado de uma solicitação de redução de carga horária específica
     * 
     * @obs Usada na tabela inicial do cadastro de redução
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT resultado, pendencia
                     FROM tbreadaptacao
                    WHERE idReadaptacao = '.$idReadaptacao;
        
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
        }
        
        # Verifica se há pendências
        if($row[1] == 1){
            $retorno.= "<br/><span title='Existem pendências nessa solicitação de redução de carga horária!' class='warning label'>Pendências</span>";
        }
                                
        return $retorno;
    }
    
    ###########################################################
    
    function exibePublicacao($idReadaptacao){

    /**
     * Informe os dados da Publicação de uma solicitação de redução de carga horária específica
     * 
     * @obs Usada na tabela inicial do cadastro de redução
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT dtPublicacao, pgPublicacao, resultado
                     FROM tbreadaptacao
                    WHERE idReadaptacao = '.$idReadaptacao;
        
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
    
    function exibePeriodo($idReadaptacao){

    /**
     * Informe os dados da período de uma solicitação de redução de carga horária específica
     * 
     * @obs Usada na tabela inicial do cadastro de redução
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT dtInicio, periodo, ADDDATE(dtInicio, INTERVAL periodo MONTH), resultado
                     FROM tbreadaptacao
                    WHERE idReadaptacao = '.$idReadaptacao;
        
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
                $retorno.= "<br/><span title='Faltam $dias dias para o término do benefício. Entrar em contato com o servidor para avaliar renovação do benefício!' class='warning label'>Faltam $dias dias</span>";
            }
        }
                                
        return $retorno;
    }
    
     ###########################################################
    
    function exibeBotaoDocumentos($idReadaptacao){

    /**
     * Exibe o botão de imprimir os documentos de uma solicitação de redução de carga horária específica
     * 
     * @obs Usada na tabela inicial do cadastro de redução
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT resultado,
                          numCiInicio,
                          numCiTermino
                     FROM tbreadaptacao
                    WHERE idReadaptacao = '.$idReadaptacao;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Nome do botão de início
        $nomeBotaoInicio = "CI Início";
        if(!is_null($row[1])){
            $nomeBotaoInicio = "CI Início<br/>n° ".$row[1];
        }
        
        # Nome do botão de Término
        $nomeBotaotermino = "CI Término";
        if(!is_null($row[2])){
            $nomeBotaotermino = "CI Término<br/>n° ".$row[2];
        }
        
        # Retorno
        if($row[0] == 1){
            
            $tamanhoImage = 20;
            $menu = new MenuGrafico(2);
            
            # Ci Início
            $botao = new BotaoGrafico();
            $botao->set_url('?fase=ciInicio&id='.$idReadaptacao);
            $botao->set_label($nomeBotaoInicio);
            $botao->set_imagem(PASTA_FIGURAS.'print.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Imprime a Ci de início');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_url('?fase=ciTermino&id='.$idReadaptacao);
            $botao->set_label($nomeBotaotermino);
            $botao->set_imagem(PASTA_FIGURAS.'print.png',$tamanhoImage,$tamanhoImage);
            $botao->set_title('Imprime a Ci de término');
            $menu->add_item($botao);
            
            $menu->show();
            
        }
    }
    
    ###########################################################
}