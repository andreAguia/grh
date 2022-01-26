<?php

class FolgaTre {

    /**
     * Abriga as várias rotina referentes a folga do tre de um servidor
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

        # Pega array com os dias publicados
        $select = 'SELECT obs
                     FROM tbfolga
                    WHERE idFolga = ' . $id;

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno[0]);
        }
    }

###########################################################
}
