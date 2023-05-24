<?php

class AtoInvestidura {
    /**
     * Abriga as várias rotina para o cadastro do Ato de Investidura dos Servidores
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    /**
     * Método exibeAto
     * 
     * Método que exibe o o ato de investidura
     * 
     * @param	string	$var	-> o nome da variável
     */
    public function exibeAto($idServidor) {
        
        # Monta o arquivo
        $arquivo = PASTA_ATOINVESTIDURA . $idServidor . ".pdf";

        # Verifica se ele existe
        if (file_exists($arquivo)) {

            # Monta o link
            $link = new Link(null, "?fase=exibeAto&id={$idServidor}", "Exibe o Ato de investidura");
            $link->set_imagem(PASTA_FIGURAS . "doc.png", 20, 20);
            #$link->set_target("_blank");
            $link->show();
        } else {
           # Monta o link
            $link = new Link(null, "?fase=upload&id={$idServidor}", "Faz o Upload do Ato de investidura");
            $link->set_imagem(PASTA_FIGURAS . "upload.png", 20, 20);
            #$link->set_target("_blank");
            $link->show();
        }
    }

###########################################################
}
