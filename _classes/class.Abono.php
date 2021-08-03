<?php

class Abono {

    /**
     * Abriga as várias rotina do COntrole Sispatri
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    
    ###########################################################

    /**
     * Método get_textoCi
     * 
     * Método que exibe o conteúdo de uma variável de configuração
     * 
     * @param	string	$var	-> o nome da variável
     */
    public function get_textoCi() {
        $select = 'SELECT textoCi
                     FROM tbabonoconfig
                    WHERE idAbonoConfig = 1';
        $pessoal = new Pessoal();
        $valor = $pessoal->select($select, false);
        if (empty($valor[0])) {
            return null;
        } else {
            return $valor[0];
        }
    }

    ###########################################################

    /**
     * Método set_textoCi
     * 
     * Método que grava um conteúdo em uma variável de configuração
     * 
     * @param	string	$var	-> o nome da variável
     */
    public function set_textoCi($textoCi) {
        #$textoCi = retiraAspas($textoCi);
        $pessoal = new Pessoal();
        $pessoal->set_tabela('tbabonoconfig');
        $pessoal->set_idCampo('idAbonoConfig');
        $pessoal->gravar(['textoCi'], [$textoCi], 1);
    }

    ###########################################################
}
