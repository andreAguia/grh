<?php

class LicencaMaternidade {

    /**
     * Abriga as várias rotina referentes a licença maternidade
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
##############################################################

    public function teveLicenca($idServidor = null) {
        # Verifica se o id foi informado
        if (empty($idServidor)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT idlicenca
                     FROM tblicenca
                    WHERE idServidor = {$idServidor}
                      AND (idtpLicenca = 3 OR idtpLicenca = 18)";

        echo "-> " . $servidor->count($select);
        if ($servidor->count($select) > 0) {
            return true;
        } else {
            return false;
        }
    }

##############################################################

    public function getUltima($idServidor = null) {
        # Verifica se o id foi informado
        if (empty($idServidor)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT *
                     FROM tblicenca
                    WHERE idServidor = {$idServidor}
                      AND (idtpLicenca = 3 OR idtpLicenca = 18)
                    ORDER BY dtInicial DESC LIMIT 1";

        return $servidor->select($select, false);
    }

##############################################################
}
