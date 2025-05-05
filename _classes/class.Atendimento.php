<?php

class Atendimento {

    /**
     * Abriga as várias rotina referentes ao cadastro de atendimento do servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    ###########################################################

    public function exibeDataAtendente($id) {

        /**
         * Exibe A data e o atendente
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Select
        $select = "SELECT data,
                          idUsuario
                     FROM tbatendimento
                    WHERE idAtendimento = {$id}";

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            pLista(date_to_php($retorno[0]), $intra->get_nickUsuario($retorno[1]));
        }
    }

###########################################################

    public function exibeAssuntoAtendimento($id) {

        /**
         * Exibe o assunto e o atendimento
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Define a quantidade de caracteres
        $quantidade = 100;

        # Select
        $select = "SELECT assunto,
                          atendimento
                     FROM tbatendimento
                    WHERE idAtendimento = {$id}";

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            if (strlen($retorno[1]) > $quantidade) {
                p($retorno[0], "pLinha1");
                p(substr($retorno[1], 0, $quantidade) . " ...", "pLinha2", null, $retorno[1]);
            } else {
                pLista($retorno[0], $retorno[1]);
            }
        }
    }

###########################################################
}
