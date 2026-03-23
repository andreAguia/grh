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

    public function get_numCandidatoAc($cargo = null) {
        /**
         * retorna o idCandidato do candiodato pelo número de inscrição
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        if (empty($cargo)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT count(idCandidato) 
                         FROM tbcandidato
                        WHERE cargo = '{$cargo}'";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);

            # Retorno
            return $row[0];
        }
    }

    ###########################################################

    public function get_numCandidatoPcd($cargo = null) {
        /**
         * retorna o idCandidato do candiodato pelo número de inscrição
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        if (empty($cargo)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT count(idCandidato) 
                         FROM tbcandidato
                        WHERE cargo = '{$cargo}'
                          AND classifPcd IS NOT NULL";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);

            # Retorno
            return $row[0];
        }
    }

    ###########################################################

    public function get_numCandidatoNi($cargo = null) {
        /**
         * retorna o idCandidato do candiodato pelo número de inscrição
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        if (empty($cargo)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT count(idCandidato) 
                         FROM tbcandidato
                        WHERE cargo = '{$cargo}'
                          AND classifNi IS NOT NULL";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);

            # Retorno
            return $row[0];
        }
    }

    ###########################################################

    public function get_numCandidatoHipo($cargo = null) {
        /**
         * retorna o idCandidato do candiodato pelo número de inscrição
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        if (empty($cargo)) {
            return null;
        } else {
            # Pega os dados
            $select = "SELECT count(idCandidato) 
                         FROM tbcandidato
                        WHERE cargo = '{$cargo}'
                          AND classifHipo IS NOT NULL";

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
                              cargoConcurso,
                              vagasPcd,
                              cargoConcurso,
                              vagasNi,
                              cargoConcurso,
                              vagasHipo,
                              cargoConcurso
                     FROM tbconcursovagadetalhada
                 ORDER BY cargoConcurso";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select);

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Tabela de Vagas");
            $tabela->set_conteudo($row);
            $tabela->set_label(["Cargo", "Ampla Concorrência<br/> Vg | Ap.", "", "PCD<br/><br/> Vg | Ap.", "", "Negros e Índios<br/> Vg | Ap.", "", "Hipossuficiente Econômico<br/> Vg | Ap.", ""]);
            $tabela->set_colspanLabel([null, 2, null, 2, null, 2, null, 2]);
            $tabela->set_width([60, 5, 5, 5, 5, 5, 5, 5, 5]);
            $tabela->set_funcao(["plm", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero"]);

            $tabela->set_classe([null, null, "Candidato", null, "Candidato", null, "Candidato", null, "Candidato"]);
            $tabela->set_metodo([null, null, "get_numCandidatoAc", null, "get_numCandidatoPcd", null, "get_numCandidatoNi", null, "get_numCandidatoHipo"]);

            $tabela->set_align(["left"]);
            $tabela->set_totalRegistro(false);

            $tabela->set_colunaSomatorio([1,3,5,7]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();
        } else {
            # Pega os dados
            $select = "SELECT vagas,
                              cargoConcurso,
                              vagasPcd,
                              cargoConcurso,
                              vagasNi,
                              cargoConcurso,
                              vagasHipo,
                              cargoConcurso
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
            $tabela->set_label(["Ampla Concorrência<br/> Vagas | Aprov.", "", "PCD<br/> Vagas | Aprov.", "", "Negros e Índios<br/> Vagas | Aprov.", "", "Hipossuficiente Econômico<br/> Vagas | Aprov.", ""]);
            $tabela->set_colspanLabel([2, null, 2, null, 2, null, 2]);
            $tabela->set_width([12, 12, 12, 12, 12, 12, 12, 12]);
            $tabela->set_funcao(["trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero"]);

            $tabela->set_classe([null, "Candidato", null, "Candidato", null, "Candidato", null, "Candidato"]);
            $tabela->set_metodo([null, "get_numCandidatoAc", null, "get_numCandidatoPcd", null, "get_numCandidatoNi", null, "get_numCandidatoHipo"]);

            $tabela->set_totalRegistro(false);
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();
        }
    }

    ###########################################################
}
