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

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = 'SELECT obs
                     FROM tbmcf
                    WHERE idMcf = ' . $id;

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno[0]);
        }
    }

###########################################################

}
