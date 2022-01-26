<?php

class Estado {

    /**
     * Classe que abriga as várias rotina do Cadastro de Estado
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ##############################################################

    public function get_dados($id = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $id integer null O id 
         * 
         * @syntax $cidade->get_dados([$id]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (vazio($id)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $select = "SELECT * 
                     FROM tbestado
                    WHERE idEstado = {$id}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row;
    }

###########################################################
}
