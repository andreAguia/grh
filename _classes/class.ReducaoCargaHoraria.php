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
    
    public function __construct($idServidor){
                
    /**
     * Inicia a classe e preenche o idServidor
     */    
        
        $this->idServidor = $idServidor;
        
    }
        
    ###########################################################
    
    function get_numProcesso(){

    /**
     * Informe o número do processo de solicitação de redução de carga horária de um servidor
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT processoReducao
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
}