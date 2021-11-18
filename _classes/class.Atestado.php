<?php

class Atestado {

    /**
     * Abriga as várias rotina referentes ao cadastro de atestado do servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    ###########################################################

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # pega os dados
        $select = 'SELECT obs
                     FROM tbatestado
                    WHERE idAtestado = ' . $id;

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno[0]);
        }
    }

###########################################################
}
