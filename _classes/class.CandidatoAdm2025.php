<?php

class CandidatoAdm2025 {
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
//            if (!empty($dados["classifAc"])) {
//
//                # Pega a situação desta vaga
//                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifAc"], "Ac", $cargo);
//
//                if ($situacaoVaga == "V") {
//                    $return .= "<span class='label success' title='Dentro do Número de Vagas'>Ac - {$dados["classifAc"]}</span>";
//                } elseif ($situacaoVaga == "R") {
//                    $return .= "<span class='label warning' title='No Cadastro de Reserva'>Ac - {$dados["classifAc"]}</span>";
//                } else {
//                    $return .= "Ac - {$dados["classifPcd"]}";
//                }
//
//                $marcador = true;
//            }
            # PCD
            if (!empty($dados["classifPcd"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifPcd"], "Pcd", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/><hr id='geral1'/>";
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
                    $return .= "<br/><hr id='geral1'/>";
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
                    $return .= "<br/><hr id='geral1'/>";
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

    public function exibeClassific($id = null) {
        /**
         * Exibe a classificação geral desse candidato
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
                    $return .= "Ac - {$dados["classifAc"]}";
                }

                $marcador = true;
            }

            # PCD
            if (!empty($dados["classifPcd"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifPcd"], "Pcd", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/><hr id='geral1'/>";
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
                    $return .= "<br/><hr id='geral1'/>";
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
                    $return .= "<br/><hr id='geral1'/>";
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

            $tabela->set_classe([null, null, "CandidatoAdm2025", "CandidatoAdm2025"]);
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

            $tabela->set_classe([null, null, "CandidatoAdm2025", "CandidatoAdm2025"]);
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

            $tabela->set_classe([null, null, "CandidatoAdm2025", "CandidatoAdm2025"]);
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

            $tabela->set_classe([null, null, "CandidatoAdm2025", "CandidatoAdm2025"]);
            $tabela->set_metodo([null, null, "get_numCandidatoHipo", "get_numCandidatoHipoNaVaga"]);

            $tabela->set_align(["left"]);
            #$tabela->set_totalRegistro(false);

            $tabela->set_colunaSomatorio([1, 2, 3]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $tab->fechaConteudo();
            $tab->show();
        } elseif ($cargo == "*") {
            return null;
        } else {

            /*
             * Somente um cargo
             */

            $classeConcursoAdm2025 = new ConcursoAdm2025();
            $obs = $classeConcursoAdm2025->get_obsCargo($cargo);

            # Função extra

            function ficaVermelho($valor) {
                if ($valor == 0) {
                    return "<span class = 'label alert'>0</span>";
                } else {
                    return $valor;
                }
            }

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

            $tabela->set_funcao(["ficaVermelho", "trataNuloZero", "trataNuloZero", "ficaVermelho", "trataNuloZero", "trataNuloZero", "ficaVermelho", "trataNuloZero", "trataNuloZero", "ficaVermelho", "trataNuloZero", "trataNuloZero"]);
            $tabela->set_classe([null, "CandidatoAdm2025", "CandidatoAdm2025", null, "CandidatoAdm2025", "CandidatoAdm2025", null, "CandidatoAdm2025", "CandidatoAdm2025", null, "CandidatoAdm2025", "CandidatoAdm2025"]);
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
     * Método get_nomeECargo
     * fornece o nome e cargo de um candidato
     * 
     * @param	string $idCandidato $id do candidato
     */
    function get_nomeECargo($idCandidato) {
        if (empty($idCandidato)) {
            return null;
        } else {

            # Pega os Dados
            $dados = $this->get_dados($idCandidato);
            $pessoal = new Pessoal();

            pLista(
                    plm($dados["nome"]),
                    plm($dados["cargo"])
            );
        }
    }

    ###########################################################

    /**
     * Método get_nomeEInscrição
     * fornece o nome e inscrição do Candidato
     * 
     * @param	string $idCandidato $id do candidato
     */
    function get_nomeEInscricao($idCandidato) {
        if (empty($idCandidato)) {
            return null;
        } else {

            # Pega os Dados
            $dados = $this->get_dados($idCandidato);

            pLista(
                    plm($dados["nome"]),
                    plm($dados["inscricao"])
            );
        }
    }

    ###########################################################

    /**
     * Método get_nomeECargoEInscricao
     * fornece o nome e inscrição do Candidato
     * 
     * @param	string $idCandidato $id do candidato
     */
    function get_nomeECargoEInscricao($idCandidato) {
        if (empty($idCandidato)) {
            return null;
        } else {

            # Pega os Dados
            $dados = $this->get_dados($idCandidato);

            pLista(
                    plm($dados["nome"]),
                    plm($dados["cargo"]),
                    plm($dados["inscricao"])
            );
        }
    }

    ###########################################################

    /**
     * Método get_nomeELotacao
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

    /**
     * Método get_nomeECargoELotacaoEAtivo
     * fornece o nome, cargo e lotacao de um candidato
     * 
     * @param	string $idCandidato $id do candidato
     */
    function get_nomeECargoELotacaoESituacao($idCandidato) {
        if (empty($idCandidato)) {
            return null;
        } else {

            # Define as variávels de retorno
            $retorno = null;

            # Pega os Dados
            $dados = $this->get_dados($idCandidato);
            $pessoal = new Pessoal();

            # Verifica se tem alguem com esse cpf
            $idPessoa = $pessoal->get_idPessoaCPF($dados["cpf"]);

            if (!empty($idPessoa)) {
                # Pega o idServidor
                $idServidor = $pessoal->get_idServidoridPessoa($idPessoa);

                # Pega o perfil
                $perfil = $pessoal->get_perfil($idServidor);

                # Pega o idFuncional
                $idFuncional = $pessoal->get_idFuncional($idServidor);

                # Verifica se é ativo
                $situacao = $pessoal->get_situacao($idServidor);

                # Pega a Admissão
                $dtAdm = $pessoal->get_dtAdmissao($idServidor);

                # Pega a Saída
                $dtSai = $pessoal->get_dtSaida($idServidor);

                # Mescla as Duas
                if (empty($dtSai)) {
                    $datas = "Admissão: {$dtAdm}";
                } else {
                    $datas = "Admissão: {$dtAdm}<br/>Saída: {$dtSai}";
                }

                # Pega o Cargo
                $cargoEfetivo = $pessoal->get_cargoSimples($idServidor);

                # Retorno
                if (empty($idFuncional)) {
                    $retorno = "<b>Servidor {$perfil} {$pessoal->get_situacao($idServidor)}<br/>{$datas}<br/>{$cargoEfetivo}</b>";
                } else {
                    $retorno = "<b>Servidor {$perfil} {$pessoal->get_situacao($idServidor)}<br/>ID Funcional: {$idFuncional}<br/>{$datas}<br/>{$cargoEfetivo}</b>";
                }
            }

            # Retorna
            pLista(
                    plm($dados["nome"]),
                    $pessoal->get_nomeLotacao($dados["idLotacao"]),
                    null,
                    $retorno
            );
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
        $select = "SELECT obs
                     FROM tbcandidato
                    WHERE idCandidato = {$id}";

        $row = $pessoal->select($select, false);

        # Pega a obs
        if (empty($row["obs"])) {
            echo "---";
        } else {
            toolTip("Obs", $row["obs"]);
        }
    }

    ###########################################################

    public function exibeNumOficio($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = "SELECT numOficio, 
                          anoOficio
                     FROM tbcandidato
                    WHERE idCandidato = {$id}";

        $row = $pessoal->select($select, false);

        # Pega a obs
        if (empty($row["numOficio"])) {
            return "---";
        } else {
            return str_pad($row["numOficio"], 3, "0", STR_PAD_LEFT) . " / {$row['anoOficio']}";
        }
    }

    ###########################################################

    /**
     * Método get_idCandidatoCPF
     * fornece o id_pessoa de um CPF
     * 
     * @param	string $cpf cpf do servidor
     */
    public function get_idCandidatoCPF($cpf) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Monta o select com os dados do candidato
        $select = "SELECT idCandidato
                     FROM tbcandidato 
                    WHERE cpf = '{$cpf}'";

        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return null;
        } else {
            return $row[0];
        }
    }

    ##########################################################

    /**
     * Método get_idCargoCargoConcurso
     * fornece o idCargo do sistema de um cargoConcurso do concurso 2025
     * 
     * @param	string $cargoConcurso O cargo do concurso
     */
    public function get_idCargoCargoConcurso($cargoConcurso) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Monta o select com os dados do candidato
        $select = "SELECT idCargo
                     FROM tbconcursovagadetalhada 
                    WHERE cargoConcurso = '{$cargoConcurso}'";

        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return null;
        } else {
            return $row[0];
        }
    }

    ##########################################################

    /**
     * Método get_numOficio
     * fornece o número de ofício à perícia para o exame adimissional
     * 
     * @param	string $ano o ano do ofício
     */
    public function get_numOficio($ano = null) {

        # Trata o ano
        if (empty($ano)) {
            $ano = date("Y");
        }

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Monta o select com os dados do candidato
        $select = "SELECT numOficio
                     FROM tbcandidato
                    WHERE anoOficio = '{$ano}'
                    ORDER BY numOficio DESC
                    LIMIT 1";

        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return "001";
        } else {
            return str_pad($row[0] + 1, 3, "0", STR_PAD_LEFT);
        }
    }

    ###########################################################

    /**
     * Método get_nomeECargoELotacaoEAtivo
     * fornece o nome, cargo e lotacao de um candidato
     * 
     * @param	string $idCandidato $id do candidato
     */
    function get_cargoELotacaoESituacao($idCandidato) {
        if (empty($idCandidato)) {
            return null;
        } else {

            # Define as variávels de retorno
            $retorno = null;

            # Pega os Dados
            $dados = $this->get_dados($idCandidato);
            $pessoal = new Pessoal();

            # Verifica se tem alguem com esse cpf
            $idPessoa = $pessoal->get_idPessoaCPF($dados["cpf"]);

            # Monta as variáveis de saída
            $linha1 = null;
            $linha2 = null;

            if (!empty($idPessoa)) {

                # Pega o idServidor
                $idServidor = $pessoal->get_idServidoridPessoa($idPessoa);

                # Pega o perfil
                $perfil = $pessoal->get_perfil($idServidor);

                # Pega o idFuncional
                $idFuncional = $pessoal->get_idFuncional($idServidor);

                # Verifica se é ativo
                $situacao = $pessoal->get_situacao($idServidor);

                # Pega a Admissão
                $dtAdm = $pessoal->get_dtAdmissao($idServidor);

                # Pega a Saída
                $dtSai = $pessoal->get_dtSaida($idServidor);

                # Pega a Situação
                $situacao = $pessoal->get_situacao($idServidor);

                # Exibe a Situação
                if ($situacao == "Ativo") {
                    echo "<span id='verde'>{$perfil} {$situacao}</span>";
                } else {
                    echo "<span id='vermelho'>{$perfil} {$situacao}</span>";
                }

                # Datas de entreda e Saída
                if (empty($dtSai)) {
                    $linha2 = "Admissão: {$dtAdm}";
                } else {
                    $linha2 = "Admissão: {$dtAdm}<br/>Saída: {$dtSai}";
                }

                # Verifica se o cargo é diferente e alerta
                if ($this->get_idCargoCargoConcurso($dados["cargo"]) <> $pessoal->get_idCargo($idServidor)) {
                    echo "<br/><span id='vermelho'>Cargo Diferente</span>";
                }

                # Retorna
                pLista(
                        "ID Funcional: {$idFuncional}",
                        $linha2,
                        $pessoal->get_cargoSimples($idServidor),
                        $pessoal->get_nomeLotacao($dados["idLotacao"])
                );
            }
        }
    }

    ###########################################################

    public function exibeSituacao($id) {

        /**
         * Exibea situação
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = "SELECT situacao
                            FROM tbcandidato JOIN tbconcursovagadetalhada ON (tbcandidato.cargo = tbconcursovagadetalhada. cargoConcurso)
                            LEFT JOIN tbcandidatosituacao USING (idCandidatoSituacao)
                            WHERE idCandidato = {$id}";

        $row = $pessoal->select($select, false);

        # Pega a obs
        if (empty($row["situacao"])) {
            return null;
        } else {
            p($row["situacao"], "vermelho"
            );
        }
    }

    ###########################################################

    public function exibeClassificPcd($id = null) {
        /**
         * Exibe a classificação geral desse candidato
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

            # PCD
            if (!empty($dados["classifPcd"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifPcd"], "Pcd", $cargo);

                if ($situacaoVaga == "V") {
                    $return .= "<span class = 'label success' title = 'Dentro do Número de Vagas'>Pcd - {$dados["classifPcd"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class = 'label warning' title = 'No Cadastro de Reserva'>Pcd - {$dados["classifPcd"]}</span>";
                } else {
                    $return .= "Pcd - {$dados["classifPcd"]}";
                }

                $marcador = true;
            }

            # AC
            if (!empty($dados["classifAc"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifAc"], "Ac", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/><hr id = 'geral1'/>";
                }

                if ($situacaoVaga == "V") {
                    $return .= "<span class = 'label success' title = 'Dentro do Número de Vagas'>Ac - {$dados["classifAc"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class = 'label warning' title = 'No Cadastro de Reserva'>Ac - {$dados["classifAc"]}</span>";
                } else {
                    $return .= "Ac - {$dados["classifAc"]}";
                }

                $marcador = true;
            }

            # Negros e Indígenas
            if (!empty($dados["classifNi"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifNi"], "Ni", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/><hr id = 'geral1'/>";
                }

                if ($situacaoVaga == "V") {
                    $return .= "<span class = 'label success' title = 'Dentro do Número de Vagas'>Ni - {$dados["classifNi"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class = 'label warning' title = 'No Cadastro de Reserva'>Ni - {$dados["classifNi"]}</span>";
                } else {
                    $return .= "Ni - {$dados["classifNi"]}";
                }

                $marcador = true;
            }

            # Hipossuficiente Econômic
            if (!empty($dados["classifHipo"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifHipo"], "Hipo", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/><hr id = 'geral1'/>";
                }

                if ($situacaoVaga == "V") {
                    $return .= "<span class = 'label success' title = 'Dentro do Número de Vagas'>Hipo - {$dados["classifHipo"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class = 'label warning' title = 'No Cadastro de Reserva'>Hipo - {$dados["classifHipo"]}</span>";
                } else {
                    $return .= "Hipo - {$dados["classifHipo"]}";
                }

                $marcador = true;
            }

            return $return;
        }
    }

    ###########################################################

    public function exibeClassificNi(
            $id = null) {
        /**
         * Exibe a classificação Ni desse candidato
         * 
         * @syntax Candidato->exibeClassificNi($id);
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

            # Negros e Indígenas
            if (!empty($dados["classifNi"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifNi"], "Ni", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/><hr id = 'geral1'/>";
                }

                if ($situacaoVaga == "V") {
                    $return .= "<span class = 'label success' title = 'Dentro do Número de Vagas'>Ni - {$dados["classifNi"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class = 'label warning' title = 'No Cadastro de Reserva'>Ni - {$dados["classifNi"]}</span>";
                } else {
                    $return .= "Ni - {$dados["classifNi"]}";
                }

                $marcador = true;
            }

            # AC
            if (!empty($dados["classifAc"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifAc"], "Ac", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/><hr id = 'geral1'/>";
                }

                if ($situacaoVaga == "V") {
                    $return .= "<span class = 'label success' title = 'Dentro do Número de Vagas'>Ac - {$dados["classifAc"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class = 'label warning' title = 'No Cadastro de Reserva'>Ac - {$dados["classifAc"]}</span>";
                } else {
                    $return .= "Ac - {$dados["classifAc"]}";
                }

                $marcador = true;
            }

            # PCD
            if (!empty($dados["classifPcd"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifPcd"], "Pcd", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/><hr id = 'geral1'/>";
                }

                if ($situacaoVaga == "V") {
                    $return .= "<span class = 'label success' title = 'Dentro do Número de Vagas'>Pcd - {$dados["classifPcd"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class = 'label warning' title = 'No Cadastro de Reserva'>Pcd - {$dados["classifPcd"]}</span>";
                } else {
                    $return .= "Pcd - {$dados["classifPcd"]}";
                }

                $marcador = true;
            }

            # Hipossuficiente Econômic
            if (!empty($dados["classifHipo"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifHipo"], "Hipo", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/><hr id = 'geral1'/>";
                }

                if ($situacaoVaga == "V") {
                    $return .= "<span class = 'label success' title = 'Dentro do Número de Vagas'>Hipo - {$dados["classifHipo"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class = 'label warning' title = 'No Cadastro de Reserva'>Hipo - {$dados["classifHipo"]}</span>";
                } else {
                    $return .= "Hipo - {$dados["classifHipo"]}";
                }

                $marcador = true;
            }

            return $return;
        }
    }

    ###########################################################

    public function exibeClassifiHipo(
            $id = null) {
        /**
         * Exibe a classificação Hipo desse candidato
         * 
         * @syntax Candidato->exibeClassifiHipo($id);
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

            # Hipossuficiente Econômic
            if (!empty($dados["classifHipo"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifHipo"], "Hipo", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/><hr id = 'geral1'/>";
                }

                if ($situacaoVaga == "V") {
                    $return .= "<span class = 'label success' title = 'Dentro do Número de Vagas'>Hipo - {$dados["classifHipo"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class = 'label warning' title = 'No Cadastro de Reserva'>Hipo - {$dados["classifHipo"]}</span>";
                } else {
                    $return .= "Hipo - {$dados["classifHipo"]}";
                }

                $marcador = true;
            }

            # AC
            if (!empty($dados["classifAc"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifAc"], "Ac", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/><hr id = 'geral1'/>";
                }

                if ($situacaoVaga == "V") {
                    $return .= "<span class = 'label success' title = 'Dentro do Número de Vagas'>Ac - {$dados["classifAc"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class = 'label warning' title = 'No Cadastro de Reserva'>Ac - {$dados["classifAc"]}</span>";
                } else {
                    $return .= "Ac - {$dados["classifAc"]}";
                }

                $marcador = true;
            }

            # PCD
            if (!empty($dados["classifPcd"])) {

                # Pega a situação desta vaga
                $situacaoVaga = $concursoAdm->get_situacaoClassifVaga($dados["classifPcd"], "Pcd", $cargo);

                # Salta linha se necessário
                if ($marcador) {
                    $return .= "<br/><hr id = 'geral1'/>";
                }

                if ($situacaoVaga == "V") {
                    $return .= "<span class = 'label success' title = 'Dentro do Número de Vagas'>Pcd - {$dados["classifPcd"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class = 'label warning' title = 'No Cadastro de Reserva'>Pcd - {$dados["classifPcd"]}</span>";
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
                    $return .= "<br/><hr id = 'geral1'/>";
                }

                if ($situacaoVaga == "V") {
                    $return .= "<span class = 'label success' title = 'Dentro do Número de Vagas'>Ni - {$dados["classifNi"]}</span>";
                } elseif ($situacaoVaga == "R") {
                    $return .= "<span class = 'label warning' title = 'No Cadastro de Reserva'>Ni - {$dados["classifNi"]}</span>";
                } else {
                    $return .= "Ni - {$dados["classifNi"]}";
                }

                $marcador = true;
            }

            return $return;
        }
    }

    ###########################################################

    public function exibeDtConvocacao($id) {

        /**
         * Exibea situação
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Variável de retorno
        $retorno = null;

        # Pega os dados do candidato
        $row = $this->get_dados($id);

        # Define array de Cotas
        $concurso = new Concurso();
        $arrayCotas = $concurso->get_arrayCotas();

        if (empty($row["cotaConvocacao"])) {
            $retorno = date_to_php($row["dtConvocacao"]);
        } else {
            $retorno = date_to_php($row["dtConvocacao"]) . "<br/><span id = 'azul'>" . $row["cotaConvocacao"] . "</span>";
        }

        if ($row["subJudice"] == 1) {
            $retorno .= "<br/><span id = 'vermelho'>SUB JUDICE</span>";
        }


        return $retorno;
    }

    ###########################################################

    public function get_tceTipoAto($id) {

        /**
         * Planilha TCE - Exibe o tipo do ato
         */
        # Pega os dados
        $dados = $this->get_dados($id);

        # Pega a Situação
        switch ($dados["idCandidatoSituacao"]) {
            case 1 :
                return "ATO3";
                break;

            case 2 :
                return "ATO3";
                break;

            case 3 :
                return "ATO4";
                break;

            case 4 :
                return "ATO3";
                break;

            case 5 :
                return "ATO3";
                break;
            
            case 6 :
                return "ATO2";
                break;

            case null :
                if (!empty($dados["dtConvocacao"])) {
                    return "ATO1";
                } else {
                    return null;
                }
                break;
        }
    }

    ###########################################################

    public function get_tceTipoVaga($id) {

        /**
         * Planilha TCE - Exibe o tipo do ato
         */
        # Pega os dados
        $dados = $this->get_dados($id);

        # Pega a Situação
        switch ($dados["cotaConvocacao"]) {
            case "Ni" :
                return "ETNICA";
                break;

            case null :
                return null;
                break;

            default:
                return maiuscula($dados["cotaConvocacao"]);
                break;
        }
    }

    ###########################################################

    public function get_tceForaOrdem($id) {

        /**
         * Planilha TCE - Exibe se foi nomeado fora da Ordem
         */
        # Pega os dados
        $dados = $this->get_dados($id);

        if ($dados["nomeacaoForaOrdem"] == 1) {
            return "SIM";
        } else {
            return "NÃO";
        }
    }

    ###########################################################

    public function get_tceSubJudice($id) {

        /**
         * Planilha TCE - Exibe se foi nomeado Sub Judice
         */
        # Pega os dados
        $dados = $this->get_dados($id);

        if ($dados["subJudice"] == 1) {
            return "SIM";
        } else {
            return "NÃO";
        }
    }

    ###########################################################

    public function get_tceSubJudiceForaOrdem($id) {

        /**
         * Planilha TCE - Exibe se foi nomeado Sub Judice fora da Ordem
         */
        # Pega os dados
        $dados = $this->get_dados($id);

        if ($dados["subJudiceForaOrdem"] == 1) {
            return "SIM";
        } else {
            return "NÃO";
        }
    }

    ###########################################################

    /**
     * Método get_dtNomeacao
     * fornece a data da nmomeação, que nesse caso,
     * é a data de admissão para quando o candidato já foi admitido
     * 
     * @param	string $idCandidato $id do candidato
     */
    function get_dtNomeacao($idCandidato) {
        if (empty($idCandidato)) {
            return null;
        } else {

            # Pega os Dados
            $dados = $this->get_dados($idCandidato);
            $pessoal = new Pessoal();

            # Verifica se tem alguem com esse cpf
            $idPessoa = $pessoal->get_idPessoaCPF($dados["cpf"]);

            if (empty($idPessoa)) {
                return null;
            } else {
                # Pega o idServidor (prioridade se for ativo)
                $idServidor = $pessoal->get_idServidoridPessoa($idPessoa);

                # Exibe somente os Estatutários
                if ($pessoal->get_perfil($idServidor) == "Estatutário") {

                    # Só continua se estamos falando do mesmo cargo
                    if ($this->get_idCargoCargoConcurso($dados["cargo"]) == $pessoal->get_idCargo($idServidor)) {

                        # Retorna a data de admissão
                        return $pessoal->get_dtAdmissao($idServidor);
                    } else {
                        return null;
                    }
                }
            }
        }
    }

    ###########################################################

    /**
     * Método get_numAtoNomeacao
     * fornece o número do ato de nomeação
     * até agora 10/2026 temos 3 situações para esse número
     * Preciso melhorar esta rotina para os próximos candidatos
     * 
     * @param	string $idCandidato $id do candidato
     */
    function get_numAtoNomeacao($idCandidato) {
        if (empty($idCandidato)) {
            return null;
        } else {

            # Pega a data de nomeação
            $dtNomeacao = $this->get_dtNomeacao($idCandidato);
            
            if($dtNomeacao == "01/06/2026"){
                return "DORJ 02/06/2026 - Pág 25 e 26";
            }
            
            if($dtNomeacao == "02/06/2026" OR $dtNomeacao == "03/06/2026"){
                return "DORJ 08/06/2026 - Pág 23 e 24";
            }
        }
    }

    ###########################################################

    /**
     * Método get_dtPubicAto
     * fornece a data de publicação do ato de nomeação
     * até agora 10/2026 temos 3 situações para esse número
     * Preciso melhorar esta rotina para os próximos candidatos
     * 
     * @param	string $idCandidato $id do candidato
     */
    function get_dtPubicAto($idCandidato) {
        if (empty($idCandidato)) {
            return null;
        } else {

            # Pega a data de nomeação
            $dtNomeacao = $this->get_dtNomeacao($idCandidato);
            
            if($dtNomeacao == "01/06/2026"){
                return "02/06/2026";
            }
            
            if($dtNomeacao == "02/06/2026" OR $dtNomeacao == "03/06/2026"){
                return "08/06/2026";
            }
        }
    }

    ###########################################################

    /**
     * Método get_cotaConvocacao
     *  
     * @param	string $idCandidato $id do candidato
     */
    function get_cotaConvocacao($idCandidato) {
        if (empty($idCandidato)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($idCandidato);            
            return $dados["cotaConvocacao"];            
        }
    }

    ###########################################################
}
