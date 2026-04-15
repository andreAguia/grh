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
            $cargo = $dados["cargo"];

            # Inicia a classe do concursoAdm
            $concursoAdm = new ConcursoAdm2025();

            # Inicia as variáveis
            $return = null;
            $marcador = false;

            # AC
            if (!empty($dados["classifAc"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifAc"], "Ac", $cargo);

                if ($situacaoVaga == "V") {
                    $return .= "<span class='label success' title='Dentro do Número de Vagas'>Ac - {$dados["classifAc"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class='label warning' title='No Cadastro de Reserva'>Ac - {$dados["classifAc"]}</span>";
                } else {
                    $return .= "Ac - {$dados["classifPcd"]}";
                }

                $marcador = true;
            }

            # PCD
            if (!empty($dados["classifPcd"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifPcd"], "Pcd", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/>";
                }
                $marcador = true;

                if ($situacaoVaga == "V") {
                    $return .= "<span class='label success' title='Dentro do Número de Vagas'>Pcd - {$dados["classifPcd"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class='label warning' title='No Cadastro de Reserva'>Pcd - {$dados["classifPcd"]}</span>";
                } else {
                    $return .= "Pcd - {$dados["classifPcd"]}";
                }

                $marcador = true;
            }

            # Negros e Indígenas
            if (!empty($dados["classifNi"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifNi"], "Ni", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/>";
                }
                $marcador = true;

                if ($situacaoVaga == "V") {
                    $return .= "<span class='label success' title='Dentro do Número de Vagas'>Ni - {$dados["classifNi"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class='label warning' title='No Cadastro de Reserva'>Ni - {$dados["classifNi"]}</span>";
                } else {
                    $return .= "Ni - {$dados["classifNi"]}";
                }
            }

            # Hipossuficiente Econômic
            if (!empty($dados["classifHipo"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifHipo"], "Hipo", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/>";
                }
                $marcador = true;

                if ($situacaoVaga == "V") {
                    $return .= "<span class='label success' title='Dentro do Número de Vagas'>Hipo - {$dados["classifHipo"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class='label warning' title='No Cadastro de Reserva'>Hipo - {$dados["classifHipo"]}</span>";
                } else {
                    $return .= "Hipo - {$dados["classifHipo"]}";
                }
            }

            return $return;
        }
    }

    ###########################################################

    public function exibeCotasRelatorio($id = null) {
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
            $cargo = $dados["cargo"];

            # Inicia a classe do concursoAdm
            $concursoAdm = new ConcursoAdm2025();

            # Inicia as variáveis
            $return = null;

            # AC
            if (!empty($dados["classifAc"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifAc"], "Ac", $cargo);

                if ($situacaoVaga == "V") {
                    $return .= "AC - {$dados["classifAc"]}";
                }
            }

            # PCD
            if (!empty($dados["classifPcd"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifPcd"], "Pcd", $cargo);

                if ($situacaoVaga == "V") {
                    $return .= "Pcd - {$dados["classifPcd"]}";
                }
            }

            # Negros e Indígenas
            if (!empty($dados["classifNi"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifNi"], "Ni", $cargo);

                if ($situacaoVaga == "V") {
                    $return .= "Ni - {$dados["classifNi"]}";
                }
            }

            # Hipossuficiente Econômic
            if (!empty($dados["classifHipo"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifHipo"], "Hipo", $cargo);

                if ($situacaoVaga == "V") {
                    $return .= "Hipo - {$dados["classifHipo"]}";
                }
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
            $select = "SELECT count(idCandidato) 
                         FROM tbcandidato";
        } else {
            # Pega os dados
            $select = "SELECT count(idCandidato) 
                         FROM tbcandidato
                        WHERE cargo = '{$cargo}'";
        }

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row[0];
    }

    ###########################################################    

    public function get_numCandidatoPcd($cargo = null) {
        /**
         * retorna o idCandidato do candiodato pelo número de inscrição
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        if (empty($cargo)) {
            # Pega os dados
            $select = "SELECT count(idCandidato) 
                         FROM tbcandidato
                        WHERE classifPcd IS NOT NULL";
        } else {
            # Pega os dados
            $select = "SELECT count(idCandidato) 
                         FROM tbcandidato
                        WHERE cargo = '{$cargo}'
                          AND classifPcd IS NOT NULL";
        }
        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row[0];
    }

    ###########################################################

    public function get_numCandidatoNi($cargo = null) {
        /**
         * retorna o idCandidato do candiodato pelo número de inscrição
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        if (empty($cargo)) {
            # Pega os dados
            $select = "SELECT count(idCandidato) 
                         FROM tbcandidato
                        WHERE classifNi IS NOT NULL";
        } else {
            # Pega os dados
            $select = "SELECT count(idCandidato) 
                         FROM tbcandidato
                        WHERE cargo = '{$cargo}'
                          AND classifNi IS NOT NULL";
        }

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row[0];
    }

    ###########################################################

    public function get_numCandidatoHipo($cargo = null) {
        /**
         * retorna o idCandidato do candiodato pelo número de inscrição
         * 
         * @syntax Candidato->exibeCotas($id);
         */
        if (empty($cargo)) {
            # Pega os dados
            $select = "SELECT count(idCandidato) 
                         FROM tbcandidato
                        WHERE classifHipo IS NOT NULL";
        } else {
            # Pega os dados
            $select = "SELECT count(idCandidato) 
                         FROM tbcandidato
                        WHERE cargo = '{$cargo}'
                          AND classifHipo IS NOT NULL";
        }
        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row[0];
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
//        tituloTable("Vagas do Edital do Concurso");
//        br();
        # Verifica se tem o cargo
        if (empty($cargo)) {

            /*
             * Todos os Cargos
             */

            # Menu de Abas
            $tab = new Tab([
                "Resumo Geral",
                "Ampla Concorrência",
                "Pcd",
                "Negros e Indígenas",
                "Hipossuficiente Econômico"
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
                ["Ampla Concorrência",
                    $concurso->get_numVagasAcAprovadas(96, null, 3) + $concurso->get_numVagasAcAprovadas(96, null, 4),
                    $this->get_numCandidatoAc(),
                    $this->get_numCandidatoAcNaVaga(null, 3) + $this->get_numCandidatoAcNaVaga(null, 4),
                ],
                ["Pcd",
                    $concurso->get_numVagasPcdAprovadas(96, null, 3) + $concurso->get_numVagasPcdAprovadas(96, null, 4),
                    $this->get_numCandidatoPcd(),
                    $this->get_numCandidatoPcdNaVaga(null, 3) + $this->get_numCandidatoPcdNaVaga(null, 4),
                ],
                ["Negros e Indígenas",
                    $concurso->get_numVagasNiAprovadas(96, null, 3) + $concurso->get_numVagasNiAprovadas(96, null, 4),
                    $this->get_numCandidatoNi(),
                    $this->get_numCandidatoNiNaVaga(null, 3) + $this->get_numCandidatoNiNaVaga(null, 4),
                ],
                ["Hipossuficiente Econômico",
                    $concurso->get_numVagasHipoAprovadas(96, null, 3) + $concurso->get_numVagasHipoAprovadas(96, null, 4),
                    $this->get_numCandidatoHipo(),
                    $this->get_numCandidatoHipoNaVaga(null, 3) + $this->get_numCandidatoHipoNaVaga(null, 4),
                ],
            ];

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Vagas do Concurso");
            $tabela->set_conteudo($array);
            $tabela->set_label(["Cotas", "Vagas", "Candidatos Aprovados", "Vagas Preenchidas"]);
            $tabela->set_width([20, 15, 15, 15, 15, 15]);
            $tabela->set_align(["left"]);
            $tabela->set_totalRegistro(false);

            $tabela->set_colunaSomatorio([1, 3]);

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
            $tabela->set_label(["Nível do Cargo", "Ampla Concorrência", "Pcd", "Negros e Indígenas", "Hipossuficiente Econômico", "Total"]);
            $tabela->set_width([20, 15, 15, 15, 15, 15]);
            $tabela->set_align(["left"]);
            $tabela->set_totalRegistro(false);

            $tabela->set_colunaSomatorio([1, 2, 3, 4, 5]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $tab->fechaConteudo();

            /*
             * Ampla Concorrêcia
             */

            $tab->abreConteudo();

            $titulo = "Ampla Concorrência";

            # Totais
            $arrayTotais = [
                [$titulo,]
            ];

            # Pega os dados
            $select = "SELECT cargoConcurso,
                              vagas,
                              cargoConcurso,
                              cargoConcurso
                     FROM tbconcursovagadetalhada JOIN tbcargo USING (idCargo)
                 ORDER BY cargoConcurso";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select);

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo($titulo);
            $tabela->set_conteudo($row);
            $tabela->set_label(["Cargo", "Vagas", "Aprovados", "Na Vaga"]);
            $tabela->set_width([40, 10, 10, 10, 10]);
            $tabela->set_funcao(["plm", "trataNuloZero", "trataNuloZero", "trataNuloZero"]);

            $tabela->set_classe([null, null, "Candidato", "Candidato"]);
            $tabela->set_metodo([null, null, "get_numCandidatoAc", "get_numCandidatoAcNaVaga"]);

            $tabela->set_align(["left"]);
            #$tabela->set_totalRegistro(false);

            $tabela->set_colunaSomatorio([1, 2, 3]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $tab->fechaConteudo();

            /*
             * Pcd
             */

            $tab->abreConteudo();

            # Pega os dados
            $select = "SELECT cargoConcurso,
                              vagasPcd,
                              cargoConcurso,
                              cargoConcurso
                     FROM tbconcursovagadetalhada JOIN tbcargo USING (idCargo)
                 ORDER BY cargoConcurso";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select);

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Pcd");
            $tabela->set_conteudo($row);
            $tabela->set_label(["Cargo", "Vagas", "Aprovados", "Na Vaga"]);
            $tabela->set_width([40, 10, 10, 10, 10]);
            $tabela->set_funcao(["plm", "trataNuloZero", "trataNuloZero", "trataNuloZero"]);

            $tabela->set_classe([null, null, "Candidato", "Candidato"]);
            $tabela->set_metodo([null, null, "get_numCandidatoPcd", "get_numCandidatoPcdNaVaga"]);

            $tabela->set_align(["left"]);
            #$tabela->set_totalRegistro(false);

            $tabela->set_colunaSomatorio([1, 2, 3]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $tab->fechaConteudo();

            /*
             * Negros e Indígenas
             */

            $tab->abreConteudo();

            # Pega os dados
            $select = "SELECT cargoConcurso,
                              vagasNi,
                              cargoConcurso,
                              cargoConcurso
                     FROM tbconcursovagadetalhada JOIN tbcargo USING (idCargo)
                 ORDER BY cargoConcurso";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select);

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Negros e Indígenas");
            $tabela->set_conteudo($row);
            $tabela->set_label(["Cargo", "Vagas", "Aprovados", "Na Vaga"]);
            $tabela->set_width([40, 10, 10, 10, 10]);
            $tabela->set_funcao(["plm", "trataNuloZero", "trataNuloZero", "trataNuloZero"]);

            $tabela->set_classe([null, null, "Candidato", "Candidato"]);
            $tabela->set_metodo([null, null, "get_numCandidatoNi", "get_numCandidatoNiNaVaga"]);

            $tabela->set_align(["left"]);
            #$tabela->set_totalRegistro(false);

            $tabela->set_colunaSomatorio([1, 2, 3]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $tab->fechaConteudo();

            /*
             * Hipossuficiente Econômico
             */

            $tab->abreConteudo();

            # Pega os dados
            $select = "SELECT cargoConcurso,
                              vagasHipo,
                              cargoConcurso,
                              cargoConcurso
                     FROM tbconcursovagadetalhada JOIN tbcargo USING (idCargo)
                 ORDER BY cargoConcurso";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select);

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Hipossuficiente Econômico");
            $tabela->set_conteudo($row);
            $tabela->set_label(["Cargo", "Vagas", "Aprovados", "Na Vaga"]);
            $tabela->set_width([40, 10, 10, 10, 10]);
            $tabela->set_funcao(["plm", "trataNuloZero", "trataNuloZero", "trataNuloZero"]);

            $tabela->set_classe([null, null, "Candidato", "Candidato"]);
            $tabela->set_metodo([null, null, "get_numCandidatoHipo", "get_numCandidatoHipoNaVaga"]);

            $tabela->set_align(["left"]);
            #$tabela->set_totalRegistro(false);

            $tabela->set_colunaSomatorio([1, 2, 3]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $tab->fechaConteudo();
            $tab->show();
        } else {

            /*
             * Somente um cargo
             */

            $classeConcursoAdm2025 = new ConcursoAdm2025();
            $obs = $classeConcursoAdm2025->get_obsCargo($cargo);

            # Pega os dados
            $select = "SELECT vagas,
                              cargoConcurso,
                              cargoConcurso,
                              vagasPcd,
                              cargoConcurso,
                              cargoConcurso,
                              vagasNi,
                              cargoConcurso,
                              cargoConcurso,
                              vagasHipo,
                              cargoConcurso,
                              cargoConcurso,
                              obs
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

            $tabela->set_label(["Ampla Concorrência", "", "", "PCD", "", "", "Negros e Indígenas", "", "", "Hipossuficiente Econômico", "", ""]);
            $tabela->set_label2(["Vagas", "Aprovados", "Na Vaga", "Vagas", "Aprovados", "Na Vaga", "Vagas", "Aprovados", "Na Vaga", "Vagas", "Aprovados", "Na Vaga"]);
            $tabela->set_colspanLabel([3, null, null, 3, null, null, 3, null, null, 3, null, null]);
            #$tabela->set_width([37, 5, 5, 5,  5, 5, 5,  5, 5, 5,  5, 5, 5]);
            $tabela->set_funcao(["trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero", "trataNuloZero"]);

            $tabela->set_classe([null, "Candidato", "Candidato", null, "Candidato", "Candidato", null, "Candidato", "Candidato", null, "Candidato", "Candidato"]);
            $tabela->set_metodo([null, "get_numCandidatoAc", "get_numCandidatoAcNaVaga", null, "get_numCandidatoPcd", "get_numCandidatoPcdNaVaga", null, "get_numCandidatoNi", "get_numCandidatoNiNaVaga", null, "get_numCandidatoHipo", "get_numCandidatoHipoNaVaga"]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_mensagemPosTabela($obs);
            $tabela->set_totalRegistro(false);

            $tabela->show();
        }
    }

    ###########################################################

    /**
     * Método get_nomeECargoELotacao
     * fornece o nome, cargo e lotacao de um candidato
     * 
     * @param	string $idCandidato $id do candidato
     */
    function get_nomeECargoELotacao($idCandidato) {
        if (empty($idCandidato)) {
            return null;
        } else {

            # Pega os Dados
            $dados = $this->get_dados($idCandidato);
            $pessoal = new Pessoal();

            pLista(
                    plm($dados["nome"]),
                    plm($dados["cargo"]),
                    $pessoal->get_nomeLotacao($dados["idLotacao"])
            );
        }
    }

    ###########################################################

    /**
     * Método get_nomeECargoELotacao
     * fornece o nome, cargo e lotacao de um candidato
     * 
     * @param	string $idCandidato $id do candidato
     */
    function get_nomeELotacao($idCandidato) {
        if (empty($idCandidato)) {
            return null;
        } else {

            # Pega os Dados
            $dados = $this->get_dados($idCandidato);
            $pessoal = new Pessoal();

            pLista(
                    plm($dados["nome"]),
                    $pessoal->get_nomeLotacao($dados["idLotacao"])
            );
        }
    }

    ###########################################################
}
