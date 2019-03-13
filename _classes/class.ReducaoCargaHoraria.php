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
    
    function get_numProcesso(){

    /**
     * Informe o número do processo de solicitação de redução de carga horária de um servidor
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT processoReducao, processoAntigoReducao
                     FROM tbservidor
                    WHERE idServidor = '.$this->idServidor;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Retorno
        return $row[0];
    }
    
    ###########################################################
    
    function get_numProcessoAntigo(){

    /**
     * Informe o número do processo Antigo de solicitação de redução de carga horária de um servidor
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT processoAntigoReducao
                     FROM tbservidor
                    WHERE idServidor = '.$this->idServidor;
        
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
    
    function get_tarefas(){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT dtSolicitacao,
                        dtEnvioPericia,
                        dtChegadaPericia,
                        dtAgendadaPericia,
                        resultado,
                        pendencia,
                        dtEnvioPendencia,
                        dtPublicacao,
                        dtInicio,
                        periodo,
                        numCiInicio,
                        numCiTermino
                   FROM tbreducao
                  WHERE idServidor = $this->idServidor
                    AND arquivado <> 1
                  ORDER BY dtSolicitacao DESC LIMIT 1";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        $numero = $pessoal->count($select);
        $mensagem = NULL;

        # Quando Já enviou a CI de Término e não arquivou o processo
        if(!is_null($dados[7])){
            $mensagem = "- Arquivar processo.<br/>";
        }

        # Resultado Deferido
        if($dados[4] == 1){
            # Quando não enviou ci de término e a data atual já passou ou é inferior a 90 dias
            if(is_null($dados[7])){

                if((!is_null($dados[8])) AND (!is_null($dados[9]))){
                    # Variáveis para calculo das datas
                    $dtHoje = date("Y-m-d");
                    $dtInicio = date_to_php($dados[8]);
                    $periodo = $dados[9];
                    $dtTermino = addMeses($dtInicio,$periodo);
                    $dtAlerta = addDias($dtTermino,-90);

                    # Verifica se a data do alerta já passou
                    if(jaPassou($dtAlerta)){
                        $mensagem = "- Perguntar ao servidor se há interesse em renovação;<br/>"
                                  . "- Enviar CI para o setor do servidor informando o término do benefício;<br/>"
                                  . "- Cadastrar a data de envio da CI de término no sistema.<br/>"
                                  . "- Arquivar processo.<br/>";
                    }
                }
            }

            # Quando ainda não enviou a CI de início para a chefia do servidor
            if(is_null($dados[10])){
                $mensagem = "- Enviar CI para o setor do servidor informando a chefia imediata sobre o benefício concedido;<br/>"
                          . "- Cadastrar o número da CI Inicial no sistema.<br/>";
            }

            # Quando ainda não preencheu o período
            if(is_null($dados[9])){
                $mensagem = "- Cadastrar no sistema o período (em meses).<br/>";
            }

            # Quando ainda não preencheu o início do benefício
            if(is_null($dados[8])){
                $mensagem = "- Cadastrar no sistema o início do benefício.<br/>";
            }

            # Quando ainda não foi publicado 
            if(is_null($dados[7])){
                $mensagem = "- Enviar o processo para o setor de publicação;<br/>"
                          . "- Enviar email ao servidor informando do benefício concedido.<br/>";
            }
        
        # Resultado indeferido
        }elseif($dados[4] == 2){
            $mensagem = "- Avisar o servidor da negativa;<br/>"
                      . "- Arquivar processo.<br/>";
        }

        # Quando ainda não foi informado o resultado 
        if(is_null($dados[4])){
            
            $mensagem = "- Aguardar o retorno do processo com o resultado;<br/>"
                      . "- Assim que chegar, cadastrar no sistema o resultado.<br/>";
                        
            # Quando tem pendências
            if($dados[5] == 1){
                $mensagem = "- Resolver as pendências<br/>"
                          . "- Cadastrar a data do envio das pendências no sistema.<br/>";
            }
            
            # Verifica a data agendada
            if(is_null($dados[3])){
                $mensagem = "- Obter com a SPMSO/SES a data agendada e cadastrar no sistema.<br/>";
            }
            
            # Verifica a data de chegada à perícia
            if(is_null($dados[2])){
                $mensagem = "- Verificar pelo UPO quando o processo chegar na SPMSO/SES;<br/>"
                          . "- Assim que chegar, cadastrar no sistema a data de chegada;<br/>"
                          . "- E avisar o servidor para enviar email marcando a perícia;<br/>";
            }
            
            # Verifica a data de envio à perícia
            if(is_null($dados[1])){
                $mensagem = "- Assim que enviar o processo à$mensagem perícia cadastrar a data no sistema.";
            }
        }

        if($numero == 0){
            return NULL;
        }else{
            return $mensagem;
        }          
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
        $select = 'SELECT dtInicio, periodo, ADDDATE(dtInicio, INTERVAL periodo MONTH), resultado
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
                $periodo = $row[1]." meses";
            }

            # Trata a data de término
            if(vazio($row[2])){
                $dttermino = "---";
            }else{
                $dttermino = date_to_php($row[2]);
            }
        
            $retorno = "Início  : ".$dtInicio."<br/>"
                     . "Período : ".$periodo."<br/>"
                     . "Término : ".$dttermino;
        }else{
            $retorno = NULL;
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
            $retorno = "CI de Início  : ".trataNulo($row[0])."<br/>"
                     . "CI de Término : ".trataNulo($row[1]);
        }else{
            $retorno = NULL;
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
        $select = 'SELECT arquivado, pendencia
                     FROM tbreducao
                    WHERE idReducao = '.$idReducao;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Verifica se há pendências
        if($row[1] == 1){
            $retorno = "Em Pendência";
        }else{
            if($row[0] == 0){
                $retorno = "Em Aberto";
            }else{
                $retorno = "Arquivado";
            }
        }
                                
        return $retorno;
    }
    
    ###########################################################
}