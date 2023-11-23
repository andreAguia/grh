<?php

class suspensao {

    /**
     * Abriga as várias rotina referentes a suspensão de servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
##############################################################

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
}
