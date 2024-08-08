<?php

class TipoCargo {
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

    function get_dados($id) {

        /**
         * fornece a próxima tarefa a ser realizada
         */
        # Pega os dados
        $select = "SELECT *
                   FROM tbtipocargo
                  WHERE idTipoCargo = {$id}";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados;
    }

    ###########################################################
    
    function get_cargo($id){
        /**
         * fornece a descrição simples do cargo em comissao
         */
        # Pega os dados
        $select = "SELECT cargo
                   FROM tbtipocargo
                  WHERE idTipoCargo = {$id}";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados['cargo'];
    }
}
