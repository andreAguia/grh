<?php

class Cedido {

    /**
     * Abriga as várias rotina referentes a cessão de servidor de outros órgão para a a Uenf - CEDIDOS
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
##############################################################

    public function getDados($idServidor = null) {
        # Verifica se o id foi informado
        if (empty($idServidor)) {
            return null;
        } else {

            # Pega os dados
            $servidor = new Pessoal();
            $select = "SELECT *
                         FROM tbcedido
                        WHERE idServidor = {$idServidor}";

            # Retorno
            return $servidor->select($select, false);
        }
    }

###########################################################

    public function get_orgaoOrigem($idServidor) {

        if (empty($idServidor)) {
            return null;
        } else {

            # Conecta com o banco de dados
            $servidor = new Pessoal();

            $select = "SELECT orgaoOrigem 
                     FROM tbcedido
                    WHERE idServidor = {$idServidor}";

            $row = $servidor->select($select, false);

            # Retorno
            if (empty($row[0])) {
                return null;
            } else {
                return $row[0];
            }
        }
    }

###########################################################
}
