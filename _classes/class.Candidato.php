<?php

class Candidato {
    ##############################################################

    public function get_dados($id = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $id integer null O id 
         * 
         * @syntax Candidato->get_dados([$id]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (empty($id)) {
            return null;
        } else {

            # Pega os dados
            $select = "SELECT * 
                     FROM tbcandidato
                    WHERE idCandidato = {$id}";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);

            # Retorno
            return $row;
        }
    }

###########################################################

    function apagaTabelaCsv() {

        # Apaga a tabela 
        $select = 'SELECT idCandidatoImporta FROM tbcandidatoimporta';

        $pessoal = new Pessoal();
        $row = $pessoal->select($select);

        $pessoal->set_tabela("tbcandidatoimporta");
        $pessoal->set_idCampo("idCandidatoImporta");

        foreach ($row as $tt) {
            $pessoal->excluir($tt[0]);
        }
    }

    ###########################################################

    /*
     * Retorna o número de registros da tabela temporária do upload
     */

    function get_numRegistrosTabelaUpload() {


        $select = "SELECT idCandidatoImporta FROM tbcandidatoimporta";

        $pessoal = new Pessoal();
        return $pessoal->count($select);
    }

    ###########################################################

    /*
     * Retorna o número de registros da tabela temporária do upload
     */

    function get_numRegistrosTabelaUploadComErro() {


        $select = "SELECT idCandidatoImporta FROM tbcandidatoimporta WHERE erro IS NOT NULL";

        $pessoal = new Pessoal();
        return $pessoal->count($select);
    }

    ###########################################################

    public function exibeCotas($id = null) {
        /**
         * Exibe as cotas desse candidato para uso na tabela
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        //arthur tava aqui <(O-O<) <(O-O)> (>O-O)> filho do andre :DDDD

        if (empty($id)) {
            return null;
        } else {
            # Pega os Dados
            $dados = $this->get_dados($id);

            # Inicia as variáveis
            $return = null;
            $marcador = false;

            # PCD
            if (!empty($dados["classifPcd"])) {
                $return .= "PCD";
                $marcador = true;
            }

            # Negros e Índios
            if (!empty($dados["classifNi"])) {
                if ($marcador) {
                    $return .= "<br/>";
                }
                $marcador = true;
                $return .= "NI";
            }

            # Hipossuficiente Econômic
            if (!empty($dados["classifHipo"])) {
                if ($marcador) {
                    $return .= "<br/>";
                }
                $return .= "HIPO";
            }

            return $return;
        }
    }

    ###########################################################

    public function get_candidato($inscricao = null) {
        /**
         * retorna o nome do candiodato pelo número de inscrição
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        //arthur tava aqui <(O-O<) <(O-O)> (>O-O)> filho do andre :DDDD

        if (empty($inscricao)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT nome 
                     FROM tbcandidato
                    WHERE inscricao = {$inscricao}";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);

            # Retorno
            return $row[0];
        }
    }

    ###########################################################

    public function get_idCandidato($inscricao = null) {
        /**
         * retorna o idCandidato do candiodato pelo número de inscrição
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        //arthur tava aqui <(O-O<) <(O-O)> (>O-O)> filho do andre :DDDD

        if (empty($inscricao)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT idCandidato 
                     FROM tbcandidato
                    WHERE inscricao = {$inscricao}";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);

            # Retorno
            return $row[0];
        }
    }

    ###########################################################

    public function exibeTabelaVagasCargo($cargo = null) {
        /**
         * exibe uma tabela com as vagas de um determinado cargo
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        # Verifica se tem o cargo
        if (empty($cargo)) {
            # Pega os dados
            $select = "SELECT cargoConcurso,
                              vagas,
                              vagasPcd,
                              vagasNi,
                              vagasHipo
                     FROM tbconcursovagadetalhada
                 ORDER BY cargoConcurso";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select);

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Tabela de Vagas");
            $tabela->set_conteudo($row);
            $tabela->set_label(["Cargo", "Ampla Concorrência", "PCD", "Negros e Índios", "Hipossuficiente Econômico"]);
            $tabela->set_width([60, 10, 10, 10, 10]);
            $tabela->set_funcao(["plm"]);
            $tabela->set_align(["left"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();
        } else {
            # Pega os dados
            $select = "SELECT vagas,
                              vagasPcd,
                              vagasNi,
                              vagasHipo
                     FROM tbconcursovagadetalhada";

            if (!empty($cargo)) {
                $select .= " WHERE cargoConcurso = '{$cargo}'";
            }

            $select .= " ORDER BY cargoConcurso";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select);

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo(plm($cargo));
            $tabela->set_conteudo($row);
            $tabela->set_label(["Ampla Concorrência", "PCD", "Negros e Índios", "Hipossuficiente Econômico"]);
            $tabela->set_width([25, 25, 25, 25]);
            $tabela->set_funcao(["trataNulo", "trataNulo", "trataNulo", "trataNulo"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();
        }
    }

    ###########################################################
}
