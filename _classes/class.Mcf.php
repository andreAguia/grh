<?php

class Mcf {

    /**
     * Abriga as várias rotina referentes ao controle de MCF
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    ###########################################################
    /*
     * retorna o Ultimo mês cadastrado no banco de dados
     */
    function getUltimoMesCadastrado() {

        # Monta o select
        $select = "SELECT mes 
                     FROM tbmcf
                 ORDER BY ano DESC, mes DESC LIMIT 1";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (empty($row)) {
            return date("m") - 1;
        } else {
            return $row["mes"];
        }
    }

    ###########################################################
    /*
     * retorna o Ultimo ano cadastrado no banco de dados
     */

    function getUltimoAnoCadastrado() {

        # Monta o select
        $select = "SELECT ano 
                     FROM tbmcf
                 ORDER BY ano DESC, mes DESC LIMIT 1";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (empty($row)) {
            return date("Y");
        } else {
            return $row["ano"];
        }
    }

    ###########################################################

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = 'SELECT obs
                     FROM tbmcf
                    WHERE idMcf = ' . $id;

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno[0]);
        }
    }

###########################################################

    public function exibeMcf($id) {
        /**
         * Exibe um link para a publicação
         * 
         * @param $idConcursoPublicacao integer null O id do Concurso
         * 
         * @syntax $ConcursoPublicacao->exibePublicacao($idConcursoPublicacao);
         */
        # Monta o arquivo
        $arquivo = PASTA_MCF . $id . ".pdf";

        # Verifica se ele existe
        if (file_exists($arquivo)) {

            # Monta o link
            $link = new Link(null, $arquivo, "Exibe o MCF");
            $link->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
            $link->set_target("_blank");
            $link->show();
        } else {
            echo "-";
        }
    }

    ###########################################################
}
