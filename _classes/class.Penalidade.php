<?php

class Penalidade {

    /**
     * Abriga as várias rotina referentes ao cadastro de atestado do servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    ###########################################################

    function get_dados($idProgressao) {

        /**
         * Fornece os todos os dados de um idProgressao
         */
        # Pega os dados
        $select = "SELECT *
                   FROM tbpenalidade
                  WHERE idPenalidade = {$idProgressao}";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados;
    }

    ###########################################################

    public function exibePublicacao($id) {

        /**
         * Exibe a data de publicação e a página
         */
        # Verifica se o $id tem valor
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);

            # Exibe os dados
            pLista(
                    date_to_php($dados["dtPublicacao"]),
                    $dados["pgPublicacao"]
            );
        }
    }

###########################################################

    public function exibeProcessoPublicacao($id) {

        /**
         * Exibe a data de publicação e a página
         */
        # Verifica se o $id tem valor
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);

            # Exibe os dados
            pLista(
                    $dados["processo"],
                    "DO: " . date_to_php($dados["dtPublicacao"]),
                    "Pág.: {$dados["pgPublicacao"]}"
            );
        }
    }

###########################################################
}
