<?php

class CargoArea {

    /**
     * Classe que abriga as várias rotina da área do cargo
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    public function get_dados($id = null) {
        /**
         * Retorna todos os dados
         * 
         * @syntax $this->get_dados($idArea);
         */
        if (vazio($id)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT *
                       FROM tbarea
                      WHERE idArea = $id";

            $pessoal = new Pessoal();
            return $pessoal->select($select, false);
        }
    }

    ###########################################################

    public function get_numCargoArea($id = null) {
        /**
         * Retorna o número de cargos com essa área
         * 
         * @syntax $this->get_numCargoArea($idArea);
         */
        if (vazio($id)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT idCargo
                       FROM tbcargo
                      WHERE idArea = $id";

            $pessoal = new Pessoal();
            return $pessoal->count($select, false);
        }
    }

    ###########################################################

    /**
     * Método get_servidoresArea
     * 
     * Exibe o número de servidores ativos em uma determinada area
     */
    public function get_numServidoresAtivos($id) {
        if (vazio($id)) {
            return null;
        } else {
            $select = 'SELECT idServidor                             
                     FROM tbservidor LEFT JOIN tbcargo USING (idCargo)
                    WHERE situacao = 1
                      AND tbcargo.idArea = ' . $id;

            $pessoal = new Pessoal();
            return $pessoal->count($select, false);
        }
    }

    ###########################################################

    /**
     * Método get_servidoresArea
     * 
     * Exibe o número de servidores inativos em uma determinada area
     */
    public function get_numServidoresInativos($id) {
        if (vazio($id)) {
            return null;
        } else {
            $select = 'SELECT idServidor                             
                     FROM tbservidor LEFT JOIN tbcargo USING (idCargo)
                    WHERE situacao <> 1
                      AND tbcargo.idArea = ' . $id;

            $pessoal = new Pessoal();
            return $pessoal->count($select, false);
        }
    }

    ###########################################################
}
