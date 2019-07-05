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
}