<?php

class Nacionalidade {

    /**
     * Abriga as várias rotina do cadastro de Nacionalidade
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    
    
    ###########################################################
    
    public function get_numServidores($id) {
         /**
         * Informa o número d dependentes com esse tipo de parentesco
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = "SELECT idPessoa
                     FROM tbpessoa
                    WHERE nacionalidade = {$id}";
        
        return $pessoal->count($select);
    }
    
    ###########################################################
    
    public function get_numServidoresAtivos($id) {
         /**
         * Informa o número d dependentes com esse tipo de parentesco
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = "SELECT idPessoa
                     FROM tbpessoa JOIN tbservidor USING (idPessoa)
                    WHERE nacionalidade = {$id} AND situacao = 1";
        
        return $pessoal->count($select);
    }
    
    ###########################################################
    
    public function get_numServidoresInativos($id) {
         /**
         * Informa o número d dependentes com esse tipo de parentesco
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = "SELECT idPessoa
                     FROM tbpessoa JOIN tbservidor USING (idPessoa)
                    WHERE nacionalidade = {$id} AND situacao <> 1";
        
        return $pessoal->count($select);
    }
}
