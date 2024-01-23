<?php

class AuxilioEducacao {

    /**
     * Abriga as várias rotina do Controle do Auxíliko Educação
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ##############################################################

    public function get_dados($id = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $id integer null O id 
         * 
         * @syntax $dependente->get_dados([$id]);
         */
        # Joga o valor informado para a variável da classe
        if (empty($id)) {
            return null;
        } else {
            $pessoal = new Pessoal();
            return $pessoal->select("SELECT * FROM tbauxeducacao WHERE idAuxEducacao = {$id}", false);
        }
    }

    ##############################################################

    public function get_dadosIdDependente($idDependente = null) {

        /**
         * Informa os dados da base de dados de um idDependente específico
         * 
         * @param $id integer null O id 
         * 
         * @syntax $dependente->get_dados([$id]);
         */
        # Joga o valor informado para a variável da classe
        if (empty($idDependente)) {
            return null;
        } else {
            $pessoal = new Pessoal();
            return $pessoal->select("SELECT * FROM tbauxeducacao WHERE idDependente = {$idDependente}");
        }
    }

    ##############################################################

    public function get_ultimaDataComprovada($idDependente = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $id integer null O id 
         * 
         * @syntax $dependente->get_dados([$id]);
         */
        # Joga o valor informado para a variável da classe
        if (empty($idDependente)) {
            return null;
        } else {
            $pessoal = new Pessoal();
            $data = $pessoal->select("SELECT dttermino FROM tbauxeducacao WHERE idDependente = {$idDependente} ORDER BY dtTermino desc LIMIT 1", false);

            if (empty($data[0])) {
                return null;
            } else {
                return date_to_php($data[0]);
            }
        }
    }

    ###########################################################

    public function exibeComprovante($id) {
        /**
         * Exibe um link para exibir o pdf do certificado
         * 
         * @param $idFormacao integer null O id
         * 
         * @syntax $formacao->exibeCertificado($idFormacao);
         */
        # Monta o arquivo
        $arquivo = PASTA_COMP_AUX_EDUCA . $id . ".pdf";

        # Verifica se ele existe
        if (file_exists($arquivo)) {

            # Monta o link
            $link = new Link(null, $arquivo, "Exibe o cOMPROVANTE");
            $link->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
            $link->set_target("_blank");
            $link->show();
        } else {
            echo "-";
        }
    }

###########################################################

    public function temPendencia($id) {
        /**
         * Informa Sim ou Não se tem pendência do auxEducação de um idDependente
         * 
         * @param $idFormacao integer null O idDependente
         */
        return $this->fazAnalise($id, true);
    }

###########################################################

    public function exibeTemPendencia($id) {
        /**
         * Informa Sim ou Não se tem pendência do auxEducação de um idDependente
         * 
         * @param $idFormacao integer null O idDependente
         */
        # Pega a informação
        $dado = $this->temPendencia($id);

        if ($dado == "Sim") {
            label("Sim", "alert");
        } elseif ($dado == "Não") {
            p("Não", "pAvisoRegularizarAzul");
        } elseif ($dado == "N/D") {
            p("N/D", "vermelho", "center");
        } else {
            echo "---";
        }
    }

###########################################################

    public function exibeQuadroLista($id) {
        /**
         * Exibe um quadro para ser exibida na rotina de controle dos comprovantes na área lateral
         * 
         * @param $id integer null O idDependente
         */
        # Pega os dados do dependente
        $dependente = new Dependente();
        $dados = $dependente->get_dados($id);
        $cpfDependente = $dependente->get_cpf($id);

        # Pega os parentescos com direito au auxEducação
        $tipos = $this->get_arrayTipoParentescoAuxEduca();

        # Verifica se tem direito
        if (in_array($dados["idParentesco"], $tipos)) {

            # Exibe Situação
            tituloTable("Situação");
            $painel = new Callout("warning");
            $painel->abre();

            $this->exibeSituacao($id);

            if (empty($cpfDependente)) {
                br();
                p("Dependente SEM CPF Cadastrado!", "vermelho", "center");
            }

            $painel->fecha();

            # Pega as datas limites
            $dtNasc = date_to_php($dados["dtNasc"]);
            $anos21 = get_dataIdade($dtNasc, 21);
            $anos24 = get_dataIdade($dtNasc, 24);
            $idade = idade($dtNasc);

            # Dados do Servidor
            $idPessoa = $dados["idPessoa"];
            $pessoal = new Pessoal();
            $idServidor = $pessoal->get_idServidoridPessoa($idPessoa);
            $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

            $intra = new Intra();
            $dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');

            # monta a tabela
            $array = array(
                array("Data de Nascimento:", $dtNasc),
                array("Admissão do Servidor:", $dtAdmissao),
                array("Publicação da Portaria", $dataHistoricaInicial));

            $tabela = new Tabela();
            #$tabela->set_titulo("Datas Inicial");
            $tabela->set_conteudo($array);
            $tabela->set_label(["Descrição", "Data"]);
            $tabela->set_width([50, 50]);
            $tabela->set_align(["left"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 1,
                    'valor' => $this->get_dtInicialAuxEducacao($id),
                    'operador' => '=',
                    'id' => 'alerta')));

            $tabela->show();

            /*
             * Exibe o período sem obrigação de 
             * enviar a declaração de escolaridade
             */

            tituloTable("Sem Precisar da Declaração de Escolaridade");
            $painel = new Callout("warning");
            $painel->abre();

            # Verifica se teve período sem precisar comprovar
            if (dataMenor($dataHistoricaInicial, $anos21) == $anos21) {
                p("Dependente já tinha mais de 21 anos quando adquiriu o direito!", "center", "f14");
            } else {
                p($this->get_dtInicialAuxEducacao($id) . " a " . $anos21, "center", "f14");
            }
            $painel->fecha();

            /*
             * Informa a data em que faz 21 anos
             */

            if (idade(date_to_php($dados["dtNasc"])) >= 21) {
                titulotable("Fez 21 anos em:");
            } else {
                titulotable("Fará 21 anos em:");
            }
            $painel = new Callout("warning");
            $painel->abre();
            p($anos21, "center", "f14");
            $painel->fecha();

            /*
             * Exibe período com obrigatoriedade
             * da entrega da declaração
             */

            # Verifica a data de início
            if (dataMenor($dataHistoricaInicial, $anos21) == $anos21) {
                $array = [$dataHistoricaInicial, $anos24];
            } else {
                $array = [$anos21, $anos24];
            }

            titulotable("Com Declaração de Escolaridade");
            $painel = new Callout("warning");
            $painel->abre();
            p($array[0] . " a " . $array[1], "center", "f14");
            $painel->fecha();

            /*
             * Informa a data em que faz 24 anos
             * Término do Direito
             */

            if (idade(date_to_php($dados["dtNasc"])) >= 24) {
                titulotable("Fez 24 anos em:");
            } else {
                titulotable("Fará 24 anos em:");
            }
            $painel = new Callout("warning");
            $painel->abre();
            p($anos24, "pAviso24Anos");
            p("Encerra o Direito!", "pAvisoEncerramento");
            $painel->fecha();
        }
    }

###########################################################

    public function exibeSituacao($id) {
        /**
         * Exibe a situação detalhada do aux educação do dependente informado
         * 
         * @param $idFormacao integer null O idDependente
         */
        return $this->fazAnalise($id, false);
    }

###########################################################

    public function exibeBotaoControle($id) {

        # Pega os dados do dependente
        $dependente = new Dependente();
        $dados = $dependente->get_dados($id);

        # Pega os parentescos com direito au auxEducação
        $tipos = $this->get_arrayTipoParentescoAuxEduca();

        # Verifica se tem direito
        if (in_array($dados["idParentesco"], $tipos)) {

            if ($dados["auxEducacao"] == "Sim") {
                # botão
                $link = new Link(null, "?fase=comprovante&id={$id}");
                $link->set_id("btnAuxEduc");
                $link->set_imagem(PASTA_FIGURAS . 'declaracao.png', 20, 20);
                $link->set_title("Controle do envio de comprovante de escolaridade");
                $link->show();
            } else {
                echo "---";
            }
        } else {
            echo "---";
        }
    }

###########################################################

    public function get_arrayTipoParentescoAuxEduca() {

        /**
         * Retorna um array com todos os tipos de parentescos que tem direito ao auxílio Educação
         *          * 
         * @syntax $dependente->get_arrayTipoParentescoAuxEduca();
         */
        # Pega o array multi do banco de dados
        $pessoal = new Pessoal();
        $arrayM = $pessoal->select("SELECT idParentesco FROM tbparentesco WHERE auxEducacao = 'Sim'");

        # Transforma em uni
        foreach ($arrayM as $item) {
            $arrayU[] = $item[0];
        }

        return $arrayU;
    }

    ###########################################################

    public function get_dtFinalAuxEducacaoControle($id) {

        /**
         * fornece a data final de um lançamento do controle de declaração escolar para o Auxílio educação
         * 
         * @param $id integer null O id 
         * 
         * @syntax $dependente->get_dtInicialAuxEducacaoControle([$id]);
         */
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados do dependente
            $dependente = new Dependente();
            $dados = $dependente->get_dados($id);

            # Pega as datas limites
            $anos24 = get_dataIdade(date_to_php($dados["dtNasc"]), 24);

            # Pega a data Inicial
            $dtInicial = $this->get_dtInicialAuxEducacaoControle($id);

            if (month($dtInicial) < 6) {
                return dataMenor("30/06/" . year($dtInicial), $anos24);
            } else {
                return dataMenor("31/12/" . year($dtInicial), $anos24);
            }
        }
    }

###########################################################

    public function get_dtInicialAuxEducacao($id) {

        # Pega os dados do dependente
        $dependente = new Dependente();
        $dados = $dependente->get_dados($id);
        $dtNasc = date_to_php($dados["dtNasc"]);

        # Dados do Servidor
        $idPessoa = $dados["idPessoa"];
        $pessoal = new Pessoal();
        $idServidor = $pessoal->get_idServidoridPessoa($idPessoa);
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

        $intra = new Intra();
        $dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');

        return dataMaiorArray([$dataHistoricaInicial, $dtAdmissao, $dtNasc]);
    }

    ###########################################################

    public function get_dtInicialAuxEducacaoControle($id) {

        /**
         * fornece a data inicial de um lançamento do controle de declaração escolar para o Auxílio educação
         * 
         * @param $id integer null O id 
         * 
         * @syntax $dependente->get_dtInicialAuxEducacaoControle([$id]);
         */
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados do dependente
            $dependente = new Dependente();
            $dados = $dependente->get_dados($id);

            # Pega as datas limites
            $anos21 = get_dataIdade(date_to_php($dados["dtNasc"]), 21);
            $dtInicioGeral = $this->get_dtInicialAuxEducacao($id);

            # Pega o último comprovante deste dependente
            $pessoal = new Pessoal();
            $comprovantes = $pessoal->select("SELECT * FROM tbauxeducacao WHERE idDependente = {$id} ORDER BY dtInicio desc LIMIT 1", false);

            if (empty($comprovantes[0])) {
                # Verifica se fez 21 anos apos a data inicial limite
                if (dataMaior($dtInicioGeral, $anos21) == $dtInicioGeral) {
                    return $dtInicioGeral;
                } else {
                    return addDias($anos21, 1, false);
                }
            } else {
                return addDias(date_to_php($comprovantes["dtTermino"]), 1, false);
            }
        }
    }

    ###########################################################

    public function get_dtTerminoAuxEducacao($id) {

        # Pega os dados do dependente
        $dependente = new Dependente();
        $dados = $dependente->get_dados($id);
        return get_dataIdade(date_to_php($dados["dtNasc"]), 21);
    }

    ###########################################################


    /*
     * Verifica se o dependente tinha mais de 24 ou não na data da publicação da lei 9450/2021
     */

    public function tinhaDireitoDataHistorica($id) {

        # Pega os dados do dependente
        $dependente = new Dependente();
        $dados = $dependente->get_dados($id);

        # Pega os parentescos com direito au auxEducação
        $tipos = $this->get_arrayTipoParentescoAuxEduca();

        # Verifica se tem direito
        if (in_array($dados["idParentesco"], $tipos)) {

            # Pega as datas limites
            $anos24 = get_dataIdade(date_to_php($dados["dtNasc"]), 24);

            # Data Histórica Inicial
            $intra = new Intra();
            $dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');

            if (dataMenor($dataHistoricaInicial, $anos24) == $anos24) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    ###########################################################

    public function verificaDireitoAuxEduca($idParentesco) {

        /**
         * Verifica se o idinserido tem direito ao auxílio educação
         */
        if (in_array($idParentesco, $this->get_arrayTipoParentescoAuxEduca())) {
            return true;
        } else {
            return false;
        }
    }

    ###########################################################

    public function fazAnalise($id, $pendencia = false) {

        # Pega os dados do dependente
        $dependente = new Dependente();
        $dados = $dependente->get_dados($id);

        # Pega os parentescos com direito au auxEducação
        $tipos = $this->get_arrayTipoParentescoAuxEduca();

        # Verifica se tem direito
        if (in_array($dados["idParentesco"], $tipos)) {

            # Pega as datas limites
            $anos21 = get_dataIdade(date_to_php($dados["dtNasc"]), 21);
            $anos24 = get_dataIdade(date_to_php($dados["dtNasc"]), 24);

            # Data Histórica Inicial
            $intra = new Intra();
            $dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');

            # Verifica se perdeu o direito antes da data histórica
            if (dataMenor($dataHistoricaInicial, $anos24) == $anos24) {
                if ($pendencia) {
                    return "Não";
                } else {
                    p("Estava com mais de 24 anos<br/>na data de Publicação<br/>da Portaria nº95 - {$dataHistoricaInicial}", "pDependenteSDireito");
                }
            } else {

                # Verifica se marcou Sim no aux Educação
                if ($dados["auxEducacao"] == "Sim") {

                    # Verifica se e menor de 21 anos e 
                    # informa a partir de quando fica sem precisar compravar escolaridade
                    if (idade(date_to_php($dados["dtNasc"])) < 21) {
                        if ($pendencia) {
                            return "Não";
                        } else {
                            p("Situação regular até:<br/>{$anos21} (21 anos)", "pAvisoRegularizarAzul");
                        }
                    }

                    # Verifica se tem mais de 21 anos
                    if (idade(date_to_php($dados["dtNasc"])) >= 21) {
                        $dadosComprovantes = $this->get_dadosIdDependente($id);
                        $ultimaDatacomprovada = $this->get_ultimaDataComprovada($id);

                        # Verifica se tem mais que 21 e não comprovou nada
                        if (empty($ultimaDatacomprovada)) {
                            $ultimaDatacomprovada = $anos21;
                        }

                        # Verifica se existe ainda algum período possível
                        if (strtotime(date_to_bd($ultimaDatacomprovada)) < strtotime(date_to_bd($anos24))) {

                            # Pega a data Inicial e de término desse semestre
                            $dtTermino = $this->get_dtFinalAuxEducacaoControle($id);
                            $dtInicial = $this->get_dtInicialAuxEducacaoControle($id);

                            if (jaPassou($dtInicial)) {
                                if ($pendencia) {
                                    return "Sim";
                                } else {
                                    p("Regularizar o período:<br/>de " . addDias($ultimaDatacomprovada, 1, false) . " até {$dtTermino}", "pAvisoRegularizarVermelho");
                                }
                            } else {
                                if ($pendencia) {
                                    return "Não";
                                } else {
                                    p("Em {$dtInicial}<br/>comprovar o periodo de<br/>{$ultimaDatacomprovada} até {$dtTermino}", "pAvisoRegularizarAzul");
                                    if ($dtTermino == $anos24) {
                                        p("(Quando encerra o direito)", "pAvisoRegularizarVermelho");
                                    }
                                }
                            }
                        } else {
                            if ($pendencia) {
                                return "Não";
                            } else {
                                p("Dependente já encerrou o direito e comprovou todos os período possíveis", "pAvisoRegularizarAzul");
                            }
                        }
                    }
                } else {
                    if ($dados["auxEducacao"] == "Não") {
                        if ($pendencia) {
                            return "Não";
                        } else {
                            p("Não", "vermelho", "center");
                        }
                    } else {
                        if ($pendencia) {
                            return "Não";
                        } else {
                            p("N/D", "vermelho", "center");
                        }
                    }
                }
            }
        } else {
            if ($pendencia) {
                return "Não";
            } else {
                echo "---";
            }
        }
    }

    ###########################################################
}
