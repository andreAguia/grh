<?php
class VagaDocente{
 /**
  * Abriga as várias rotina do Controle de Vagas de Docentes
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
    
    function get_dados($idVagaDocente){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT *
                   FROM tbvagadocente
                  WHERE idVagaDocente = $idVagaDocente";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        return $dados;
    }
    
    ###########################################################        
}