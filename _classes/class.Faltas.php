<?php

class Faltas {

    /**
     * Abriga as várias rotina referentes as faltas do servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    ###########################################################
    function getFaltasServidor($idServidor) {

        # Verifica se foi informado
        if (empty($idServidor)) {
            alert("É necessário informar o id do Servidor.");
            return;
        }

        $select = "SELECT * 
                     FROM tblicenca
                    WHERE idServidor = {$idServidor}
                      AND idTpLicenca = 25 
                 ORDER BY dtInicial";

        $pessoal = new Pessoal();
        return $pessoal->select($select);
    }

    ###########################################################

    function getNumFaltasServidor($idServidor) {

        # Verifica se foi informado
        if (empty($idServidor)) {
            alert("É necessário informar o id do Servidor.");
            return;
        }

        $select = "SELECT idLicenca
                     FROM tblicenca
                    WHERE idServidor = {$idServidor}
                      AND idTpLicenca = 25 
                 ORDER BY dtInicial";

        $pessoal = new Pessoal();
        return $pessoal->count($select);
    }

    ###########################################################

    function getNumDiasFaltasServidor($idServidor) {

        # Verifica se foi informado
        if (empty($idServidor)) {
            alert("É necessário informar o id do Servidor.");
            return;
        }

        $select = "SELECT SUM(numDias)
                     FROM tblicenca
                    WHERE idServidor = {$idServidor}
                      AND idTpLicenca = 25 
                 ORDER BY dtInicial";

        $pessoal = new Pessoal();
        $retorno = $pessoal->select($select, false);
        return $retorno[0];
    }

    ##############################################################

    public function exibeDoc($id = null) {
        # Verifica se o id foi informado
        if (empty($id)) {
            return "---";
        } else {
            # Pega o tipo de licença
            $pessoal = new Pessoal();
            $tipo = $pessoal->get_tipoLicenca($id);

            # Verifica se é Faltas
            if ($tipo == 25) {
                # Monta o arquivo
                $arquivo = PASTA_FALTAS . "{$id}.pdf";

                # Verifica se ele existe
                if (file_exists($arquivo)) {

                    $botao = new BotaoGrafico();
                    $botao->set_url($arquivo);
                    $botao->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
                    $botao->set_title("Exibe o documento arquivado");
                    $botao->set_target("_blank");
                    $botao->show();
                } else {
                    return "---";
                }
            } else {
                return "---";
            }
        }
    }

###########################################################

    function exibeTabela($idServidor) {

        # Verifica se foi informado
        if (empty($idServidor)) {
            return;
        }

        $select = "SELECT dtInicial,
                          numdias,
                          obs
                     FROM tblicenca
                    WHERE idServidor = {$idServidor}
                      AND idTpLicenca = 25 
                 ORDER BY dtInicial";

        $pessoal = new Pessoal();
        $array = $pessoal->select($select);

        $tabela = new Tabela();
        $tabela->set_titulo("Faltas");
        $tabela->set_conteudo($array);
        $tabela->set_label(["Data Inicial", "Dias", "obs"]);
        $tabela->set_align(["center", "center", "left"]);
        $tabela->set_funcao(["date_to_php"]);
        $tabela->show();
    }

    ##############################################################
}
