<?php

class Frequencia {

    /**
     * Abriga as várias rotina referentes a cessão de servidor da Uenf para outro órgão
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
##############################################################

    public function getDados($id = null) {
        # Verifica se o id foi informado
        if (vazio($id)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT *
                     FROM tbfrequencia
                    WHERE idFrequencia = {$id}";

        # Retorno
        return $servidor->select($select, false);
    }

###########################################################

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = "SELECT obs
                     FROM tbfrequencia
                    WHERE idFrequencia = {$id}";

        $retorno = $pessoal->select($select, false);

        if (empty($retorno["obs"])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno["obs"]);
        }
    }

###########################################################
}
