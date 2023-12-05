<?php

class suspensao {

    /**
     * Abriga as várias rotina referentes a suspensão de servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
###########################################################

    function get_dados($id) {

        /**
         * Fornece os todos os dados de um $id
         */
        # Pega os dados
        $select = "SELECT *
                   FROM tblicenca
                  WHERE idLicenca = {$id}";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados;
    }

    ###########################################################

    public function exibePublicacaoPdf($idLicenca = null) {
        # Verifica se o id foi informado
        if (empty($idLicenca)) {
            return "---";
        } else {
            # Monta o arquivo
            $arquivo = PASTA_SUSPENSAO . "{$idLicenca}.pdf";

            # Verifica se ele existe
            if (file_exists($arquivo)) {

                $botao = new BotaoGrafico();
                $botao->set_url($arquivo);
                $botao->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
                $botao->set_title("Exibe a publicação arquivada");
                $botao->set_target("_blank");
                $botao->show();
            } else {
                return "---";
            }
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
