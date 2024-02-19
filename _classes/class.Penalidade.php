<?php

class Penalidade {

    /**
     * Abriga as várias rotina referentes ao cadastro de atestado do servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    ###########################################################

    function get_dados($id) {

        /**
         * Fornece os todos os dados de um $id
         */
        # Pega os dados
        $select = "SELECT *
                   FROM tbpenalidade
                  WHERE idPenalidade = {$id}";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados;
    }

    ###########################################################

    public function exibePublicacao($id) {

        /**
         * Exibe a data de publicação e a página
         */
        # Verifica se o $id tem valor
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);

            # trata a página
            if (!is_null($dados["pgPublicacao"])) {
                $dados["pgPublicacao"] = "Pag, " . $dados["pgPublicacao"];
            }

            # Exibe os dados
            pLista(
                    date_to_php($dados["dtPublicacao"]),
                    $dados["pgPublicacao"]
            );
        }
    }

###########################################################

    public function exibeProcessoPublicacao($id) {

        /**
         * Exibe a data de publicação e a página
         */
        # Verifica se o $id tem valor
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);

            # trata os daados
            if (empty($dados["dtPublicacao"])) {
                $dtPublicacao = null;
                $pagina = null;
            } else {
                $dtPublicacao = "DO: " . date_to_php($dados["dtPublicacao"]);
                if (empty($dados["pgPublicacao"])) {
                    $pagina = null;
                } else {
                    $pagina = "Pág.: {$dados["pgPublicacao"]}";
                }
            }

            # Exibe os dados
            pLista(
                    $dados["processo"],
                    $dtPublicacao,
                    $pagina
            );
        }
    }

###########################################################

    public function temPADFaltas($idServidor) {

        /**
         * Verifica se tem allguma penalidade (PADS) por faltas
         */
        # Verifica se o $id tem valor
        if (empty($idServidor)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT idPenalidade
                         FROM tbpenalidade
                        WHERE idServidor = {$idServidor}
                          AND falta = 'Sim'";

            $pessoal = new Pessoal();

            if ($pessoal->count($select) > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

###########################################################

    public function exibePDF($id) {
        /**
         * Exibe um link para exibir o pdf 
         * 
         * @param $id integer null O id
         */
        # Monta o arquivo
        $arquivo = PASTA_PENALIDADES . $id . ".pdf";

        # Verifica se ele existe
        if (file_exists($arquivo)) {

            # Monta o link
            $link = new Link(null, $arquivo, "Exibe o PDF");
            $link->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
            $link->set_target("_blank");
            $link->show();
        } else {
            echo "-";
        }
    }

###########################################################

    public function exibeProcessoPublicacaoGeral($id) {

        /**
         * Exibe a data de publicação e a página
         */
        # Verifica se o $id tem valor
        if (empty($id)) {
            return null;
        } else {
            # Pega a primeira letra do $id
            $indicador = substr($id, 0, 1);
            $novoId = substr($id, 1);

            if ($indicador == "p") {
                $this->exibeProcessoPublicacao($novoId);
            } else {
                $suspensao = new Suspensao();
                $suspensao->exibeProcessoPublicacao($novoId);
            }
        }
    }

###########################################################

    public function editarGeral($id) {

        /**
         * Exibe a data de publicação e a página
         */
        # Verifica se o $id tem valor
        if (empty($id)) {
            return null;
        } else {
            # Pega a primeira letra do $id
            $indicador = substr($id, 0, 1);
            $novoId = substr($id, 1);

            if ($indicador == "p") {
                $link = new Link(null, "areaPenalidades.php?fase=editaServidor&id={$novoId}", "Editar Servidor");
                $link->set_imagem(PASTA_FIGURAS_GERAIS . "bullet_edit.png", 20, 20);
                $link->show();
            } else {
                $link = new Link(null, "areaPenalidades.php?fase=editaServidorSuspensao&id={$novoId}", "Editar Servidor");
                $link->set_imagem(PASTA_FIGURAS_GERAIS . "bullet_edit.png", 20, 20);
                $link->show();
            }
        }
    }

###########################################################
}
