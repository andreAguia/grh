<?php

class Atestado {

    /**
     * Abriga as várias rotina referentes ao cadastro de atestado do servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    ###########################################################

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # pega os dados
        $select = 'SELECT obs
                     FROM tbatestado
                    WHERE idAtestado = ' . $id;

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno[0]);
        }
    }

###########################################################

    public function exibeAtestado($id) {
        /**
         * Exibe um link para a publicação
         * 
         * @param $idConcursoPublicacao integer null O id do Concurso
         * 
         * @syntax $ConcursoPublicacao->exibePublicacao($idConcursoPublicacao);
         */
        # Monta o arquivo
        $arquivo = PASTA_ATESTADO . $id . ".pdf";

        # Verifica se ele existe
        if (file_exists($arquivo)) {

            # Monta o link
            $link = new Link(null, $arquivo, "Exibe o atestado");
            $link->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
            $link->set_target("_blank");
            $link->show();
        } else {
            echo "---";
        }
    }

    ###########################################################
}
