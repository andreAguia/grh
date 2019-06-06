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
        
        $retorno = NULL;
        
        # Escolaridade
        #$retorno = '<span title="Nível do Curso" id="orgaoCedido">['.$pessoal->get_escolaridade($dados['idEscolaridade']).']</span><br/>';
        
        # Nome do Curso
        $retorno .= $dados['habilitacao'];
        
        # Ano de Término
        if(!vazio($dados['anoTerm'])){
            $retorno .= '<br/><span title="Ano de Conclusão" id="orgaoCedido">['.$dados['anoTerm'].']</span)';
        }
        
        return $retorno;
    }
    
    ###########################################################
}