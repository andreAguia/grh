<?php

class ConcursoProva {

    /**
     * Abriga as várias rotina referentes as publicações
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $idConcursoPublicacao integer null O id da publicação
     */
##############################################################

    public function exibeProva($id) {
        /**
         * Exibe um link para o pdf
         * 
         * @param $id integer null O id
         * 
         * @syntax $publicacao->exibePdf($id);
         */
        # Monta o arquivo
        $arquivo = PASTA_PROVAS . $id . ".pdf";

        # Verifica se ele existe
        if (file_exists($arquivo)) {

            # Monta o link
            $link = new Link(null, $arquivo, "Exibe a Prova");
            $link->set_imagem(PASTA_FIGURAS . "doc.png", 20, 20);
            $link->set_target("_blank");
            $link->show();
        } else {
            echo "---";
        }
    }

###########################################################

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        if (empty($id)) {
            echo "---";
        } else {

            # Pega array com os dias publicados
            $select = "SELECT obs
                         FROM tbconcursoprova
                    WHERE idConcursoProva = {$id}";

            $retorno = $pessoal->select($select, false);
            if (empty($retorno[0])) {
                echo "---";
            } else {
                toolTip("Obs", $retorno[0]);
            }
        }
    }

###########################################################

    public function get_numeroProvas($idConcurso) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        if (empty($idConcurso)) {
            echo "---";
        } else {

            # Pega array com os dias publicados
            $select = "SELECT idConcursoProva
                         FROM tbconcursoprova
                    WHERE idConcurso = {$idConcurso}";

            return $pessoal->count($select);
        }
    }

###########################################################
}
