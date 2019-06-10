<?php
class Aposentadoria{
 /**
  * Abriga as várias rotina referentes a aposentadoria do servidor
  * 
  * @author André Águia (Alat) - alataguia@gmail.com  
  */
    
    
    ###########################################################
    
    /**
    * Método Construtor
    */
    public function __construct(){
        
    }
    
    ###########################################################

    function get_numServidoresAposentados(){

    /**
     * informa o número de Servidores Ativos
     * 
     * @param integer $idPessoa do servidor
     */
        
        $select = 'SELECT idServidor
                     FROM tbservidor
                    WHERE situacao = 2';
        
        $pessoal = new Pessoal();
        $count = $pessoal->count($select);
        return $count;
    }

    ###########################################################

}