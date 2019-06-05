<?php
class Formacao{
 /**
  * Abriga as várias rotina do Cadastro de Formação Escolar do servidor
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
    
    function get_curso($idFormacao){
        
    /**
     * fornece Detalhes do curso
     */
        
        # Pega os dados
        $select="SELECT *
                   FROM tbformacao
                  WHERE idFormacao = $idFormacao";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        
        
        return $dados;
    }
    
    ###########################################################
}