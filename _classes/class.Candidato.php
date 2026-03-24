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

    public function get_numCandidatoAcNaVaga($cargo = null, $idTipoCargo = null) {
        /**
         * retorna o número de candidatos na vaga
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        if (empty($cargo) AND empty($idTipoCargo)) {
            return null;
        } else {
            if (empty($idTipoCargo)) {
                # Pega o número de vagas do Concurso
                $concurso = new Concurso();
                $numVagas = $concurso->get_numVagasAcAprovadas(96, $cargo);

                # Pega os candidatos aprovados
                $numCandidatos = $this->get_numCandidatoAc($cargo);

                # Analisa os dados
                if ($numCandidatos >= $numVagas) {
                    return intval($numVagas);
                } else {
                    return intval($numCandidatos);
                }
            } else {
                # Pega os cargos deste nível
                $select = "SELECT cargoConcurso
                         FROM tbconcursovagadetalhada JOIN tbcargo USING (idCargo)
                        WHERE tbcargo.idTipoCargo = {$idTipoCargo}";

                # Pega os dados
                $pessoal = new Pessoal();
                $row = $pessoal->select($select);

                # Inicia a variável de soma
                $soma = 0;

                # Percorre o array
                foreach ($row as $item) {
                    $soma += $this->get_numCandidatoAcNaVaga($item[0]);
                }

                return $soma;
            }
        }
    }

    ###########################################################

    public function get_numCandidatoPcdNaVaga($cargo = null, $idTipoCargo = null) {
        /**
         * retorna o número de candidatos na vaga
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        if (empty($cargo) AND empty($idTipoCargo)) {
            return null;
        } else {
            if (empty($idTipoCargo)) {
                # Pega o número de vagas do Concurso
                $concurso = new Concurso();
                $numVagas = $concurso->get_numVagasPcdAprovadas(96, $cargo);

                # Pega os candidatos aprovados
                $numCandidatos = $this->get_numCandidatoPcd($cargo);

                # Analisa os dados
                if ($numCandidatos >= $numVagas) {
                    return intval($numVagas);
                } else {
                    return intval($numCandidatos);
                }
            } else {
                # Pega os cargos deste nível
                $select = "SELECT cargoConcurso
                         FROM tbconcursovagadetalhada JOIN tbcargo USING (idCargo)
                        WHERE tbcargo.idTipoCargo = {$idTipoCargo}";

                # Pega os dados
                $pessoal = new Pessoal();
                $row = $pessoal->select($select);

                # Inicia a variável de soma
                $soma = 0;

                # Percorre o array
                foreach ($row as $item) {
                    $soma += $this->get_numCandidatoPcdNaVaga($item[0]);
                }

                return $soma;
            }
        }
    }

    ###########################################################

    public function get_numCandidatoNiNaVaga($cargo = null, $idTipoCargo = null) {
        /**
         * retorna o número de candidatos na vaga
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        if (empty($cargo) AND empty($idTipoCargo)) {
            return null;
        } else {
            if (empty($idTipoCargo)) {
                # Pega o número de vagas do Concurso
                $concurso = new Concurso();
                $numVagas = $concurso->get_numVagasNiAprovadas(96, $cargo);

                # Pega os candidatos aprovados
                $numCandidatos = $this->get_numCandidatoNi($cargo);

                # Analisa os dados
                if ($numCandidatos >= $numVagas) {
                    return intval($numVagas);
                } else {
                    return intval($numCandidatos);
                }
            } else {
                # Pega os cargos deste nível
                $select = "SELECT cargoConcurso
                         FROM tbconcursovagadetalhada JOIN tbcargo USING (idCargo)
                        WHERE tbcargo.idTipoCargo = {$idTipoCargo}";

                # Pega os dados
                $pessoal = new Pessoal();
                $row = $pessoal->select($select);

                # Inicia a variável de soma
                $soma = 0;

                # Percorre o array
                foreach ($row as $item) {
                    $soma += $this->get_numCandidatoNiNaVaga($item[0]);
                }

                return $soma;
            }
        }
    }

    ###########################################################

    public function get_numCandidatoHipoNaVaga($cargo = null, $idTipoCargo = null) {
        /**
         * retorna o número de candidatos na vaga
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        if (empty($cargo) AND empty($idTipoCargo)) {
            return null;
        } else {
            if (empty($idTipoCargo)) {
                # Pega o número de vagas do Concurso
                $concurso = new Concurso();
                $numVagas = $concurso->get_numVagasHipoAprovadas(96, $cargo);

                # Pega os candidatos aprovados
                $numCandidatos = $this->get_numCandidatoHipo($cargo);

                # Analisa os dados
                if ($numCandidatos >= $numVagas) {
                    return intval($numVagas);
                } else {
                    return intval($numCandidatos);
                }
            } else {
                # Pega os cargos deste nível
                $select = "SELECT cargoConcurso
                         FROM tbconcursovagadetalhada JOIN tbcargo USING (idCargo)
                        WHERE tbcargo.idTipoCargo = {$idTipoCargo}";

                # Pega os dados
                $pessoal = new Pessoal();
                $row = $pessoal->select($select);

                # Inicia a variável de soma
                $soma = 0;

                # Percorre o array
                foreach ($row as $item) {
                    $soma += $this->get_numCandidatoHipoNaVaga($item[0]);
                }

                return $soma;
            }
        }
    }

    ###########################################################

    public function get_numCandidatoNaVagaTotal($cargo = null, $idTipoCargo = null) {
        /**
         * retorna o número de candidatos na vaga
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        if (empty($cargo) AND empty($idTipoCargo)) {
            return null;
        } else {
            if (empty($idTipoCargo)) {
                # Pega o número de vagas do Concurso
                $concurso = new Concurso();
                $numVagas = $concurso->get_numVagasAprovadasTotal(96, $cargo);

                # Pega os candidatos aprovados
                $numCandidatos = $this->get_numCandidatoHipo($cargo);

                # Analisa os dados
                if ($numCandidatos >= $numVagas) {
                    return intval($numVagas);
                } else {
                    return intval($numCandidatos);
                }
            } else {
                # Pega os cargos deste nível
                $select = "SELECT cargoConcurso
                         FROM tbconcursovagadetalhada JOIN tbcargo USING (idCargo)
                        WHERE tbcargo.idTipoCargo = {$idTipoCargo}";

                # Pega os dados
                $pessoal = new Pessoal();
                $row = $pessoal->select($select);

                # Inicia a variável de soma
                $soma = 0;

                # Percorre o array
                foreach ($row as $item) {
                    $soma += $this->get_numCandidatoAcNaVaga($item[0]);
                    $soma += $this->get_numCandidatoPcdNaVaga($item[0]);
                    $soma += $this->get_numCandidatoNiNaVaga($item[0]);
                    $soma += $this->get_numCandidatoHipoNaVaga($item[0]);
                }

                return $soma;
            }
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

            /*
             * Todos os Cargos
             */

            # Menu de Abas
            $tab = new Tab([
                "Resumo Geral",
                "Nível Médio",
                "Nível Superior"
            ]);

            /*
             * Geral
             */

            $tab->abreConteudo();

            # Vagas do Concurso
            # Inicias as Classes
            $concurso = new Concurso();

            # Monta o array
            $array = [
                ["Nível Médio",
                    $concurso->get_numVagasAcAprovadas(96, null, 4),
                    $concurso->get_numVagasPcdAprovadas(96, null, 4),
                    $concurso->get_numVagasNiAprovadas(96, null, 4),
                    $concurso->get_numVagasHipoAprovadas(96, null, 4),
                    $concurso->get_numVagasAprovadasTotal(96, null, 4),
                ],
                ["Nível Superior",
                    $concurso->get_numVagasAcAprovadas(96, null, 3),
                    $concurso->get_numVagasPcdAprovadas(96, null, 3),
                    $concurso->get_numVagasNiAprovadas(96, null, 3),
                    $concurso->get_numVagasHipoAprovadas(96, null, 3),
                    $concurso->get_numVagasAprovadasTotal(96, null, 3),
                ],
            ];

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Vagas do Concurso");
            $tabela->set_conteudo($array);
            $tabela->set_label(["Nível do Cargo", "Ampla Concorrência", "Pcd", "Negros e Índios", "Hipossuficiente Econômico", "Total"]);
            $tabela->set_width([20, 15, 15, 15, 15, 15]);
            $tabela->set_align(["left"]);
            $tabela->set_totalRegistro(false);

            $tabela->set_colunaSomatorio([1, 2, 3, 4, 5]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            br();

            # Candidato nas Vagas
            # Monta o array
            $array = [
                ["Nível Médio",
                    $this->get_numCandidatoAcNaVaga(null, 4),
                    $this->get_numCandidatoPcdNaVaga(null, 4),
                    $this->get_numCandidatoNiNaVaga(null, 4),
                    $this->get_numCandidatoHipoNaVaga(null, 4),
                    $this->get_numCandidatoNaVagaTotal(null, 4),
                ],
                ["Nível Superior",
                    $this->get_numCandidatoAcNaVaga(null, 3),
                    $this->get_numCandidatoPcdNaVaga(null, 3),
                    $this->get_numCandidatoNiNaVaga(null, 3),
                    $this->get_numCandidatoHipoNaVaga(null, 3),
                    $this->get_numCandidatoNaVagaTotal(null, 3),
                ],
            ];

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Vagas Preenchidas");
            $tabela->set_conteudo($array);
            $tabela->set_label(["Nível do Cargo", "Ampla Concorrência", "Pcd", "Negros e Índios", "Hipossuficiente Econômico", "Total"]);
            $tabela->set_width([20, 15, 15, 15, 15, 15]);
            $tabela->set_align(["left"]);
            $tabela->set_totalRegistro(false);

            $tabela->set_colunaSomatorio([1, 2, 3, 4, 5]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $tab->fechaConteudo();

            /*
             * Nível médio
             */

            $tab->abreConteudo();

            # Pega os dados
            $select = "SELECT cargoConcurso,
                              vagas,
                              cargoConcurso,
                              cargoConcurso,
                              '',
                              vagasPcd,
                              cargoConcurso,
                              cargoConcurso,
                              '',
                              vagasNi,
                              cargoConcurso,
                              cargoConcurso,
                              '',
                              vagasHipo,
                              cargoConcurso,
                              cargoConcurso,
                              ''
                     FROM tbconcursovagadetalhada JOIN tbcargo USING (idCargo)
                     WHERE tbcargo.idTipoCargo = 4
                 ORDER BY cargoConcurso";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select);

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Tabela de Vagas");
            $tabela->set_subtitulo("Nível Médio");
            $tabela->set_conteudo($row);
            $tabela->set_label(["Cargo", "Ampla Concorrência", "", "", '', "PCD", "", "", '', "Negros e Índios", "", "", '', "Hipossuficiente Econômico", "", ""]);
            $tabela->set_label2(["", "Vagas", "Aprov.", "Na Vaga", '', "Vagas", "Aprov.", "Na Vaga", '', "Vagas", "Aprov.", "Na Vaga", '', "Vagas", "Aprov.", "Na Vaga"]);
            $tabela->set_colspanLabel([null, 3, null, null, null, 3, null, null, null, 3, null, null, null, 3, null, null, null]);
            #$tabela->set_width([37, 5, 5, 5, 1, 5, 5, 5, 1, 5, 5, 5, 1, 5, 5, 5]);
            $tabela->set_funcao(["plm", "trataNuloZero", "trataNuloZero", "trataNuloZero", "", "trataNuloZero", "trataNuloZero", "trataNuloZero", "", "trataNuloZero", "trataNuloZero", "trataNuloZero", "", "trataNuloZero", "trataNuloZero", "trataNuloZero"]);

            $tabela->set_classe([null, null, "Candidato", "Candidato", null, null, "Candidato", "Candidato", null, null, "Candidato", "Candidato", null, null, "Candidato", "Candidato"]);
            $tabela->set_metodo([null, null, "get_numCandidatoAc", "get_numCandidatoAcNaVaga", null, null, "get_numCandidatoPcd", "get_numCandidatoPcdNaVaga", null, null, "get_numCandidatoNi", "get_numCandidatoNiNaVaga", null, null, "get_numCandidatoHipo", "get_numCandidatoHipoNaVaga"]);

            $tabela->set_align(["left"]);
            $tabela->set_totalRegistro(false);

            $tabela->set_colunaSomatorio([1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $tab->fechaConteudo();

            /*
             * Nível Superior
             */

            $tab->abreConteudo();

            # Pega os dados
            $select = "SELECT cargoConcurso,
                              vagas,
                              cargoConcurso,
                              cargoConcurso,
                              '',
                              vagasPcd,
                              cargoConcurso,
                              cargoConcurso,
                              '',
                              vagasNi,
                              cargoConcurso,
                              cargoConcurso,
                              '',
                              vagasHipo,
                              cargoConcurso,
                              cargoConcurso
                     FROM tbconcursovagadetalhada JOIN tbcargo USING (idCargo)
                     WHERE tbcargo.idTipoCargo = 3
                 ORDER BY cargoConcurso";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select);

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Tabela de Vagas");
            $tabela->set_subtitulo("Nível Superior");
            $tabela->set_conteudo($row);
            $tabela->set_label(["Cargo", "Ampla Concorrência", "", "", '', "PCD", "", "", '', "Negros e Índios", "", "", '', "Hipossuficiente Econômico", "", ""]);
            $tabela->set_label2(["", "Vagas", "Aprov.", "Na Vaga", '', "Vagas", "Aprov.", "Na Vaga", '', "Vagas", "Aprov.", "Na Vaga", '', "Vagas", "Aprov.", "Na Vaga"]);
            $tabela->set_colspanLabel([null, 3, null, null, null, 3, null, null, null, 3, null, null, null, 3, null, null, null]);
            #$tabela->set_width([37, 5, 5, 5, 1, 5, 5, 5, 1, 5, 5, 5, 1, 5, 5, 5]);
            $tabela->set_funcao(["plm", "trataNuloZero", "trataNuloZero", "trataNuloZero", "", "trataNuloZero", "trataNuloZero", "trataNuloZero", "", "trataNuloZero", "trataNuloZero", "trataNuloZero", "", "trataNuloZero", "trataNuloZero", "trataNuloZero"]);

            $tabela->set_classe([null, null, "Candidato", "Candidato", null, null, "Candidato", "Candidato", null, null, "Candidato", "Candidato", null, null, "Candidato", "Candidato"]);
            $tabela->set_metodo([null, null, "get_numCandidatoAc", "get_numCandidatoAcNaVaga", null, null, "get_numCandidatoPcd", "get_numCandidatoPcdNaVaga", null, null, "get_numCandidatoNi", "get_numCandidatoNiNaVaga", null, null, "get_numCandidatoHipo", "get_numCandidatoHipoNaVaga"]);

            $tabela->set_align(["left"]);
            $tabela->set_totalRegistro(false);

            $tabela->set_colunaSomatorio([1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $tab->fechaConteudo();
            $tab->show();
        } else {

            /*
             * Somente um cargo
             */

            # Pega os dados
            $select = "SELECT vagas,
                              cargoConcurso,
                              cargoConcurso,
                              '',
                              vagasPcd,
                              cargoConcurso,
                              cargoConcurso,
                              '',
                              vagasNi,
                              cargoConcurso,
                              cargoConcurso,
                              '',
                              vagasHipo,
                              cargoConcurso,
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

            $tabela->set_label(["Ampla Concorrência", "", "", '', "PCD", "", "", '', "Negros e Índios", "", "", '', "Hipossuficiente Econômico", "", ""]);
            $tabela->set_label2(["Vagas", "Aprov.", "Na Vaga", '', "Vagas", "Aprov.", "Na Vaga", '', "Vagas", "Aprov.", "Na Vaga", '', "Vagas", "Aprov.", "Na Vaga"]);
            $tabela->set_colspanLabel([3, null, null, null, 3, null, null, null, 3, null, null, null, 3, null, null, null]);
            #$tabela->set_width([37, 5, 5, 5, 1, 5, 5, 5, 1, 5, 5, 5, 1, 5, 5, 5]);
            $tabela->set_funcao(["trataNuloZero", "trataNuloZero", "trataNuloZero", "", "trataNuloZero", "trataNuloZero", "trataNuloZero", "", "trataNuloZero", "trataNuloZero", "trataNuloZero", "", "trataNuloZero", "trataNuloZero", "trataNuloZero"]);

            $tabela->set_classe([null, "Candidato", "Candidato", null, null, "Candidato", "Candidato", null, null, "Candidato", "Candidato", null, null, "Candidato", "Candidato"]);
            $tabela->set_metodo([null, "get_numCandidatoAc", "get_numCandidatoAcNaVaga", null, null, "get_numCandidatoPcd", "get_numCandidatoPcdNaVaga", null, null, "get_numCandidatoNi", "get_numCandidatoNiNaVaga", null, null, "get_numCandidatoHipo", "get_numCandidatoHipoNaVaga"]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();
        }
    }

    ###########################################################
}
