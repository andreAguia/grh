<?php

class Parente {

    /**
     * Abriga as várias rotina do cadastro de Parentes
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    
    
    ###########################################################

    public function get_nomeECpf($id) {

        /**
         * Exibe o nome e o CPF de um parente
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = "SELECT nome,
                          cpf
                     FROM tbdependente
                    WHERE idDependente = {$id}";

        $retorno = $pessoal->select($select, false);
        
        if (empty($retorno[0])) {
            return null;
        } else {
            pLista(
                    $retorno["nome"],
                    $retorno["cpf"]
            );
        }
    }

###########################################################
}
