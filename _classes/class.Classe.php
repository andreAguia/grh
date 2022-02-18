<?php

class Classe {

    /**
     * Classe que abriga as várias rotina do Cadastro de Classe (Salário)
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
         * @syntax $classe->get_dados([$id]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se o id foi informado
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT * 
                         FROM tbclasse
                        WHERE idClasse = {$id}";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);

            # Retorno
            return $row;
        }
    }

###########################################################
}
