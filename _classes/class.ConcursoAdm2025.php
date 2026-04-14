<?php

class ConcursoAdm2025 {

    /**
     * Abriga as várias rotina específicasd do Concurso Administrativo de 2025
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    function get_arrayCotas() {
        /**
         * Fornece e padroniza o array com as cotas
         */
        $array = [
            ["Ac", "Ampla Concorrência"],
            ["Pcd", "PCD"],
            ["Ni", "Negros e Indígenas"],
            ["Hipo", "Hipossuficiente Econômico"],
        ];
        return $array;
    }

    ###########################################################

    function get_idConcurso() {
        /**
         * informa o idConcurso 
         */
        return 96;
    }

    ###########################################################

    function get_obsCargo($cargoConcurso = null) {
        /**
         * Informa a obs do cargo
         */
        if (empty($cargoConcurso)) {
            return null;
        } else {
            $select = "SELECT obs 
                     FROM tbconcursovagadetalhada
                     WHERE cargoConcurso = '{$cargoConcurso}'";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);
            return $row["obs"];
        }
    }

    ###########################################################
}
