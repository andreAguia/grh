<?php
class CargoComissao{
 /**
  * Abriga as várias rotina do Cadastro de cargo em Comissao
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
    
    function get_dados($idComissao){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT *
                   FROM tbcomissao
                  WHERE idComissao = $idComissao";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        return $dados;
    }
    
    ###########################################################
    
    function get_descricaoCargo($idComissao){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT tbdescricaocomissao.descricao
                   FROM tbdescricaocomissao JOIN tbcomissao USING (idDescricaoComissao)
                  WHERE idComissao = $idComissao";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        return $dados[0];
    }
    
    ###########################################################
    
    function get_cargoCompleto($idComissao){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT tbtipocomissao.simbolo,
                        tbtipocomissao.descricao,
                        tbdescricaocomissao.descricao
                   FROM tbcomissao JOIN tbtipocomissao USING (idTipoComisso)
                                   JOIN descricaocomissao USING (idDescricaoComissao)
                  WHERE tbcomissao.idComissao = $idComissao";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        $retorno = $dados[0]." - ".$dados[1]."<br/>".$dados[2];
        return $retorno;
    }
    
    ###########################################################
}