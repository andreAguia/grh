<?php

class TipoNomeacao {

    /**
     * Abriga as várias rotina referente ao tipo de nomeação
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    public $tiposVisibilidade = [
        [1, 'Em todas as listagens'],
        [2, 'Somente na cadastro do servidor'],
    ];

    ##############################################################

    public function get_dados($id) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $id integer null O id
         * 
         * @syntax $nomeacao->get_dados([$id]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (empty($id)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $select = "SELECT * 
                     FROM tbtiponomeacao
                    WHERE idTipoNomeacao = {$id}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row;
    }

##############################################################

    public function get_tipos() {

        /**
         * Retorna um array com os dados para uma tabela
         * 
         * @param $id integer null O id
         * 
         * @syntax $nomeacao->get_dados([$id]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dados
        $select = "SELECT idTipoNomeacao,
                          nome,
                          descricao,
                          IF(remunerado = 1,'Sim','Não'),
                          CASE";
        
        foreach($this->tiposVisibilidade as $tt){
            $select .= " WHEN visibilidade = {$tt[0]} THEN '{$tt[1]}'";
        }
        
        $select .= "      ELSE '---'
                          END    
                     FROM tbtiponomeacao
                    WHERE idTipoNomeacao";

        $pessoal = new Pessoal();
        return $pessoal->select($select);
    }

##############################################################

    public function get_visibilidade($id) {

        /**
         * Informa a visibilidade de nomeação
         * 
         * @param $id integer null O id
         * 
         * @syntax $nomeacao->get_dados([$id]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (empty($id)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $select = "SELECT visibilidade 
                     FROM tbtiponomeacao
                    WHERE idTipoNomeacao = {$id}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row[0];
    }

##############################################################

    public function get_nome($id) {

        /**
         * Informa o nome do tipo de nomeação
         * 
         * @param $id integer null O id
         * 
         * @syntax $nomeacao->get_dados([$id]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (empty($id)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $select = "SELECT nome 
                     FROM tbtiponomeacao
                    WHERE idTipoNomeacao = {$id}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row[0];
    }

##############################################################

    public function get_descricao($id) {

        /**
         * Informa a descricao do tipo de nomeação
         * 
         * @param $id integer null O id
         * 
         * @syntax $nomeacao->get_dados([$id]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (empty($id)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $select = "SELECT descricao 
                     FROM tbtiponomeacao
                    WHERE idTipoNomeacao = {$id}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row[0];
    }

##############################################################
}
