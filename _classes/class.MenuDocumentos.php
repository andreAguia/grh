<?php

class MenuDocumentos {

    /**
     * Abriga as várias rotina do Menu de Documentos
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    public function exibeBotaoUpload($id) {
        /**
         * Exibe um botão de upload
         * 
         * @param $idFormacao integer null O id 
         * 
         * @syntax $formacao->exibeBotaoUpload($id);
         */
        # Verifica se tem id
        if (empty($id)) {
            return null;
        } else {
            # Exibe o botão
            $link = new Link(null, "?fase=upload&id={$id}", "Upload do documento");
            $link->set_imagem(PASTA_FIGURAS . "upload.png", 20, 20);
            #$link->set_target("_blank");
            $link->show();
        }
    }

    ###########################################################

    public function exibeDocumento($id) {
        /**
         * Exibe um link para exibir o pdf do documento
         * 
         * @param $idFormacao integer null O id
         * 
         * @syntax $formacao->exibeDocumento($id);
         */
        # Monta o arquivo
        $arquivo = PASTA_DOCUMENTOS . $id . ".pdf";

        # Verifica se ele existe
        if (file_exists($arquivo)) {

            # Monta o link
            $link = new Link(null, $arquivo, "Exibe o documento");
            $link->set_imagem(PASTA_FIGURAS . "olho.png", 20, 20);
            $link->set_target("_blank");
            $link->show();
        } else {
            echo "-";
        }
    }

###########################################################
}
