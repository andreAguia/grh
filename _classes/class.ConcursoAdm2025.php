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
            ["Ni", "Negros e Índios"],
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
}
