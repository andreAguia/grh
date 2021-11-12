<?php

class Licenca {

    /**
     * Abriga as várias rotina referentes a cessão de servidor da Uenf para outro órgão
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
##############################################################

    public function exibeNome($idTpLicenca = null) {
        # Verifica se o id foi informado
        if (vazio($idTpLicenca)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT nome, lei
                     FROM tbtipolicenca
                    WHERE idTpLicenca = {$idTpLicenca}";

        $licenca = $servidor->select($select, false);
        pLista(
                $licenca[0],
                $licenca[1]
        );
    }

##########################################################################################

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = 'SELECT obs
                     FROM tblicenca
                    WHERE idLicenca = ' . $id;

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno[0]);
        }
    }

###########################################################
}
