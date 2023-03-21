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
            
            $row = $pessoal->select($select, false);

            # Retorno
            return $row;
        }
    }

##############################################################

    public function get_numSalarios($idPlano = null) {

        /**
         * Informa o número de salários cadastrado nesse plano
         * 
         * @param $idPlano integer null O idPlano 
         * 
         * @syntax $classe->get_numSalarios([$idPlano]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se o id foi informado
        if (empty($idPlano)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT idClasse 
                         FROM tbclasse
                        WHERE idPlano = {$idPlano}";

            
            $numero = $pessoal->count($select, false);

            # Retorno
            return $numero;
        }
    }

##############################################################

    public function get_idPlano($idClasse = null) {

        /**
         * Informa o idPlano de uma idClasse
         * 
         * @param $idClasse integer null O idClasse 
         * 
         * @syntax $classe->get_idPlano([$idClasse]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se o id foi informado
        if (empty($idClasse)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT idPlano 
                         FROM tbclasse
                        WHERE idClasse = {$idClasse}";

            
            $row = $pessoal->select($select, false);

            # Retorno
            return $row[0];
        }
    }

###########################################################

}
