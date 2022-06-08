<?php

class Mcf {

    /**
     * Abriga as várias rotina referentes ao controle de MCF
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    ###########################################################
    /*
     * retorna o Ultimo mês cadastrado no banco de dados
     */
    function getUltimoMesCadastrado() {

        # Monta o select
        $select = "SELECT mes 
                     FROM tbmcf
                 ORDER BY ano DESC, mes DESC LIMIT 1";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (empty($row)) {
            return date("m") -1;
        } else {
            return $row["mes"];
        }
    }

    ###########################################################
    /*
     * retorna o Ultimo ano cadastrado no banco de dados
     */

    function getUltimoAnoCadastrado() {

        # Monta o select
        $select = "SELECT ano 
                     FROM tbmcf
                 ORDER BY ano DESC, mes DESC LIMIT 1";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (empty($row)) {
            return date("Y");
        } else {
            return $row["ano"];
        }
    }

    ###########################################################
}