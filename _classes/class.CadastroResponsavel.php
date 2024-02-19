<?php

class CadastroResponsavel {
    /**
     * Abriga as várias rotina para o cadastro de Responsaveis
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    /**
     * Método exibeAnexo
     * 
     * Método que exibe o o ato de investidura
     * 
     * @param	string	$var	-> o nome da variável
     */
    public function exibeAnexo($id) {

        $link = new Link(null, "../grhRelatorios/cadastroResponsavel.php?id={$id}","Exibe o Anexo");
        $link->set_imagem(PASTA_FIGURAS . "print.png", 20, 20);
        $link->set_target("_blank");
        $link->show();
    }

###########################################################
}
