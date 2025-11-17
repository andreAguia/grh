<?php

class PublicacaoPremio {

    /**
     * Abriga as várias rotina referentes as publicações
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $idConcursoPublicacao integer null O id da publicação
     */
##############################################################

    public function get_dados($id = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $id integer null O id 
         * 
         * @syntax $publicacao->get_dados([$id]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (empty($id)) {
            alert("É necessário informar o id da Publicação.");
            return;
        }

        # Pega os dados
        $select = "SELECT * 
                     FROM tbpublicacaopremio
                    WHERE idPublicacaoPremio = {$id}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row;
    }

###########################################################
}
