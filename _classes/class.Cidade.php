<?php

class Cidade {

    /**
     * Classe que abriga as várias rotina do Cadastro de cidades
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ##############################################################

    public function get_dados($id = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $id integer null O id 
         * 
         * @syntax $cidade->get_dados([$id]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (vazio($id)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $select = "SELECT * 
                     FROM tbcidade
                    WHERE idCidade = {$id}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row;
    }

###########################################################

    public function getCidade($id = null) {
        /**
         * Retorna o nome e o estado da Cidade
         * 
         * @syntax $cidade->getCidade($id);
         */
        if (empty($id)) {
            return null;
        } else {
            $dadosCidade = $this->get_dados($id);

            $estado = new Estado();
            $dadosEstado = $estado->get_dados($dadosCidade["idEstado"]);

            return "{$dadosCidade["nome"]} - {$dadosEstado["uf"]}";
        }
    }

    ###########################################################

    public function get_idCidade($cidade = null) {
        /**
         * Retorna o nome e o estado da Cidade
         * 
         * @syntax $cidade->getCidade($id);
         */
        if (empty($cidade)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT idCidade 
                         FROM tbcidade
                        WHERE nome = '{$cidade}'";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);
            
            return $row[0];
        }
    }

    ###########################################################
}
