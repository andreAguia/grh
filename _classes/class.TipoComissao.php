<?php

class TipoComissao {
    /**
     * Abriga as várias rotina do Cadastro de tipos de cargo em Comissao
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    /**
     * Método Construtor
     */
    public function __construct() {
        
    }

    ###########################################################

    function get_dados($idTipoComissao) {

        /**
         * fornece a próxima tarefa a ser realizada
         */
        # Pega os dados
        $select = "SELECT *
                   FROM tbtipocomissao
                  WHERE idTipoComissao = $idTipoComissao";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados;
    }

    ###########################################################
    
    function get_descricao($id){
        /**
         * fornece a descrição simples do cargo em comissao
         */
        # Pega os dados
        $select = "SELECT descricao
                   FROM tbtipocomissao
                  WHERE idTipoComissao = {$id}";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados['descricao'];
    }
}
