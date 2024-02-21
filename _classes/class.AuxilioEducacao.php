<?php

class AuxilioEducacao {

    /**
     * Abriga as várias rotina do Controle do Auxíliko Educação
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    # Define as idades de acordo com a lei
    private $idadeInicial = 21;
    private $idadeFinal = 25;

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

    public function get_idadeInicialLei() {

        /**
         * Informa, conforme a lei, a idade inicial 
         * da exigência dos comprovantes
         */
        return $this->idadeInicial;
    }

    ##############################################################

    public function get_idadeFinalLei() {

        /**
         * Informa, conforme a lei, a idade final 
         * da exigência dos comprovantes
         */
        return $this->idadeFinal;
    }

    ##############################################################    

    public function get_data21Anos($id) {

        /**
         * Informa a data em que o dependente faz a idade inicial
         * do direito, ou seja 21 anos pela lei atual
         */
        # Pega os dados do dependente
        $dependente = new Dependente();
        $dados = $dependente->get_dados($id);

        return get_dataIdade(date_to_php($dados["dtNasc"]), $this->idadeInicial);
    }

    ##############################################################

    public function get_data25Anos($id) {

        /**
         * Informa a data em que o dependente faz a idade final do direito
         */
        # Pega os dados do dependente
        $dependente = new Dependente();
        $dados = $dependente->get_dados($id);

        # Retorna a data anterior - um dia antes
        return get_dataIdade(date_to_php($dados["dtNasc"]), $this->idadeFinal);
    }

    ##############################################################

    public function get_data25AnosMenos1Dia($id) {

        /**
         * Informa a data em que o dependente faz a idade final do direito
         * menos um para atender a lei
         */
        # Pega os dados do dependente
        $dependente = new Dependente();
        $dados = $dependente->get_dados($id);

        # Retorna a data anterior - um dia antes
        return addDias($this->get_data25Anos($id), -1, false);
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
        # Verifica se estudou no período
        $dados = $this->get_dados($id);

        if ($dados["estudou"] == "Não") {
            label("Não estudou", "primary");
        } else {


            # Monta o arquivo
            $arquivo = PASTA_COMP_AUX_EDUCA . $id . ".pdf";

            # Verifica se ele existe
            if (file_exists($arquivo)) {

                # Monta o link
                $link = new Link(null, $arquivo, "Exibe o comprovante");
                $link->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
                $link->set_target("_blank");
                $link->show();
            } else {
                label("Sem Comprovação", "alert");
            }
        }
    }

    ###########################################################

    private function temComprovante($id) {
        /**
         * Retorna true se tiver comprovante em pdf para esse cadastro
         * 
         * @param $id integer null O id
         */
        if (empty($id)) {
            return false;
        } else {
            if (file_exists(PASTA_COMP_AUX_EDUCA . $id . ".pdf")) {
                return true;
            } else {
                return false;
            }
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

    public function exibeQuadroEdita($id) {
        /**
         * Exibe um quadro para ser exibida na rotina de controle dos comprovantes na área lateral
         * 
         * @param $id integer null O idDependente
         */
        # Cpf
        $dependente = new Dependente();
        $cpfDependente = $dependente->get_cpf($id);

        # Exibe Situação
        tituloTable("Situação");

        $painel = new Callout("warning");
        $painel->abre();

        $this->exibeSituacao($id);

        if (empty($cpfDependente)) {
            p("Sem CPF Cadastrado!", "pAvisoRegularizarVermelho");
        }

        $painel->fecha();
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
                p("Sem CPF Cadastrado!", "pAvisoRegularizarVermelho");
            }

            $painel->fecha();

            # Pega os dados do dependente
            $dtNasc = date_to_php($dados["dtNasc"]);
            $idade = idade($dtNasc);

            # Exibe a Idade do Dependente
            tituloTable("Idade Atual do Dependente");
            $painel = new Callout("warning");
            $painel->abre();

            p("{$idade} anos", "center", "f16");

            $painel->fecha();

            # Pega as datas limites
            $data21Anos = $this->get_data21Anos($id);
            $data25AnosMenos1Dia = $this->get_data25AnosMenos1Dia($id);

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
            $tabela->set_titulo("Define a Data Inicial");
            $tabela->set_conteudo($array);
            $tabela->set_label(["Descrição", "Data"]);
            $tabela->set_width([50, 50]);
            $tabela->set_align(["left"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 1,
                    'valor' => $this->get_dataInicialDireito($id),
                    'operador' => '=',
                    'id' => 'alerta')));

            $tabela->show();
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

    public function get_dataInicialDireito($id) {

        /*
         * Informa a data em que o servidor passou a ter direito a receber o banefício
         * Independente de ter que comprovar escoladidade ou não
         * Pode ser:
         * - a data de nascinemto do dependente;
         * - a data de admissão do servidor;
         * - a data da lei
         */

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

    public function get_dataInicialControle($idDependente) {

        /**
         * fornece a data inicial do controle e do direito ao aux Educação
         * 
         * Pode ser:
         * - a data do aniversário de 21 anos;
         * - a data de admissão do servidor;
         * - a data da lei
         */
        # Verifica se o id foi fornecido 
        if (empty($idDependente)) {
            return null;
        } else {
            # Pega os dados do dependente
            $dependente = new Dependente();
            $dados = $dependente->get_dados($idDependente);
            $data21Anos = $this->get_data21Anos($idDependente);

            # Dados do Servidor
            $idPessoa = $dados["idPessoa"];
            $pessoal = new Pessoal();
            $idServidor = $pessoal->get_idServidoridPessoa($idPessoa);
            $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

            $intra = new Intra();
            $dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');

            return dataMaiorArray([$dataHistoricaInicial, $dtAdmissao, $data21Anos]);
        }
    }

    ###########################################################

    public function get_dataInicialFormulario($id) {

        /**
         * fornece a data inicial do controle e do direito ao aux Educação
         * 
         * Pode ser:
         * - a data do aniversário de 21 anos;
         * - a data de admissão do servidor;
         * - a data da lei
         */
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados do dependente
            $dependente = new Dependente();
            $dados = $dependente->get_dados($id);

            # Pega as datas limites
            $data21Anos = $this->get_data21Anos($id);
            $dataInicialControle = $this->get_dataInicialControle($id);

            # Pega o último comprovante deste dependente
            $pessoal = new Pessoal();
            $comprovantes = $pessoal->select("SELECT * FROM tbauxeducacao WHERE idDependente = {$id} ORDER BY dtInicio desc LIMIT 1", false);

            if (empty($comprovantes[0])) {
                # Verifica se fez 21 anos apos a data inicial limite
                if (dataMaior($dataInicialControle, $data21Anos) == $dataInicialControle) {
                    return $dataInicialControle;
                } else {
                    return $data21Anos;
                }
            } else {
                return addDias(date_to_php($comprovantes["dtTermino"]), 1, false);
            }
        }
    }

    ###########################################################

    public function get_dataFinalFormulario($id) {

        /**
         * fornece a data final de um lançamento do controle de declaração escolar para o Auxílio educação
         * 
         * @param $id integer null O id 
         * 
         * @syntax $dependente->get_dataFinalFormulario([$id]);
         */
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados do dependente
            $dependente = new Dependente();
            $dados = $dependente->get_dados($id);

            # Pega as datas
            $dtInicial = $this->get_dataInicialFormulario($id);
            $dataFinal = $this->get_data25AnosMenos1Dia($id);

            if (month($dtInicial) <= 6) {
                return dataMenor("30/06/" . year($dtInicial), $dataFinal);
            } else {
                return dataMenor("31/12/" . year($dtInicial), $dataFinal);
            }
        }
    }

###########################################################

    /*
     * Verifica se o dependente tinha mais de 24 ou não na data da publicação da lei 9450/2021
     * 
     */

    public function tinhaDireitoDataHistorica($id) {

        # Pega os dados do dependente
        $dependente = new Dependente();
        $dados = $dependente->get_dados($id);

        # Pega os parentescos com direito au auxEducação
        $tipos = $this->get_arrayTipoParentescoAuxEduca();

        # Verifica se tem direito
        if (in_array($dados["idParentesco"], $tipos)) {

            # Data Histórica Inicial
            $intra = new Intra();
            $dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');

            if (dataMenor($dataHistoricaInicial, $this->get_data25AnosMenos1Dia($id)) == $this->get_data25AnosMenos1Dia($id)) {
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
         * Verifica se o parentesco tem direito ao auxílio educação
         */
        if (in_array($idParentesco, $this->get_arrayTipoParentescoAuxEduca())) {
            return true;
        } else {
            return false;
        }
    }

    ###########################################################

    public function fazAnalise($idDependente, $pendencia = false) {

        # Pega os dados do dependente
        $dependente = new Dependente();
        $dados = $dependente->get_dados($idDependente);

        # Pega os parentescos com direito au auxEducação
        $tipos = $this->get_arrayTipoParentescoAuxEduca();

        # Datas de acordo com a idade
        $data21Anos = $this->get_data21Anos($idDependente);
        $data25AnosMenos1Dia = $this->get_data25AnosMenos1Dia($idDependente);

        # Data Final do pŕoximo semestra
        $hoje = date("d/m/Y");
        $ano = year($hoje);
        $proximoAno = $ano + 1;

        # Define a última data do semestre a ser comprovada
        if (month($hoje) <= 6) {
            $datafinalProximoSemestre = "31/12/{$ano}";
        } else {
            $datafinalProximoSemestre = "30/06/{$proximoAno}";
        }

        # Verifica com a data de 25 anos
        if (strtotime(date_to_bd($datafinalProximoSemestre)) > strtotime(date_to_bd($data25AnosMenos1Dia))) {
            $datafinalProximoSemestre = $data25AnosMenos1Dia;
        }

        # Verifica se tem direito
        if (in_array($dados["idParentesco"], $tipos)) {

            # Datas de acordo com a idade
            $data21Anos = $this->get_data21Anos($idDependente);
            $data25AnosMenos1Dia = $this->get_data25AnosMenos1Dia($idDependente);

            # Datas
            $dataInicialDireito = $this->get_dataInicialDireito($idDependente);
            $dataInicialControle = $this->get_dataInicialControle($idDependente);

            # Data em que iniciou a portaria
            $intra = new Intra();
            $dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');

            # Verifica se perdeu o direito antes da data histórica
            if (dataMenor($dataHistoricaInicial, $data25AnosMenos1Dia) == $data25AnosMenos1Dia) {
                if ($pendencia) {
                    return "Não";
                } else {
                    p("Estava com mais de {$this->idadeFinal} anos<br/>na data de Publicação<br/>da Portaria nº95 - {$dataHistoricaInicial}", "pDependenteSDireito");
                }
            } else {

                # Verifica se marcou Sim no aux Educação
                if ($dados["auxEducacao"] == "Sim") {

                    # Verifica se é menor de 21 anos e 
                    # informa a partir de quando fica sem precisar comprovar escolaridade
                    if (idade(date_to_php($dados["dtNasc"])) < $this->idadeInicial) {
                        if ($pendencia) {
                            return "Não";
                        } else {
                            p("Situação Regular até:<br/>{$data21Anos} ({$this->idadeInicial} anos)", "pAvisoRegularizarAzul");
                        }
                    } else {
                        # Pega os dados de todos os comprovantes entregues
                        $pessoal = new Pessoal();
                        $row = $pessoal->select("SELECT * FROM tbauxeducacao WHERE idDependente = {$idDependente} ORDER BY dtInicio");
                        
                        # flag de ocorrências
                        $ocorrencia = false;

                        if (count($row) > 0) {
                            # Variáveis uteis
                            $contador = 1;

                            # Percorre os dados
                            foreach ($row as $item) {
                                # Acerta os dados
                                $dataInicial = date_to_php($item["dtInicio"]);
                                $dataFinal = date_to_php($item["dtTermino"]);

                                #####################################################################
                                # Verfica se existe no INÍCIO algum período faltando
                                #####################################################################
                                if ($contador == 1 AND $dataInicial <> $dataInicialControle) {
                                    if ($pendencia) {
                                        return "Sim";
                                    } else {
                                        p("Falta CADASTRAR o período Inicial<br/>de {$dataInicialControle} até " . addDias($dataInicial, -1, false), "pAvisoRegularizarVermelho");
                                        hr("alerta");
                                        $ocorrencia = true;
                                    }
                                }

                                #####################################################################
                                # Verfica se existe no MEIO algum período faltando
                                #####################################################################
                                if ($contador == 1) {
                                    # Atualiza a data anterior
                                    $dataFinalAnterior = addDias($dataFinal, 1, false);
                                } else {
                                    if ($dataInicial <> $dataFinalAnterior) {
                                        if ($pendencia) {
                                            return "Sim";
                                        } else {
                                            p("Falta CADASTRAR o período<br/>de " . $dataFinalAnterior . " até " . addDias($dataInicial, -1, false), "pAvisoRegularizarVermelho");
                                            hr("alerta");
                                            $ocorrencia = true;
                                        }
                                    }
                                    # Atualiza a data anterior
                                    $dataFinalAnterior = addDias($dataFinal, 1, false);
                                }

                                #####################################################################
                                # Verifica se informou que estudou e tem comprovante
                                #####################################################################
                                if (!$this->temComprovante($item["idAuxEducacao"]) AND $item["estudou"] <> "Não") {
                                    if ($pendencia) {
                                        return "Sim";
                                    } else {
                                        p("Falta COMPROVAR o período<br/>de {$dataInicial} até {$dataFinal}", "pAvisoRegularizarVermelho");
                                        hr("alerta");
                                        $ocorrencia = true;
                                    }
                                }

                                #####################################################################
                                # Verifica se existe no FINAL algum período
                                #####################################################################
                                # Última data cadastrada
                                $ultimaDataCadastrada = $this->get_ultimaDataComprovada($idDependente);

                                # Informa o aviso
                                if (strtotime(date_to_bd($ultimaDataCadastrada)) < strtotime(date_to_bd($data25AnosMenos1Dia))) {

                                    # Verifica se é o último lançamento
                                    if ($dataFinal == $ultimaDataCadastrada) {

                                        # Verifica se ultima data cadastrada é diferente a do próximo semestre
                                        if ($ultimaDataCadastrada <> $datafinalProximoSemestre) {
                                            if ($pendencia) {
                                                return "Sim";
                                            } else {
                                                $dataInicialTemp = addDias($ultimaDataCadastrada, 1, false);
                                                $dataFinalTemp = $this->get_dataFinalFormulario($idDependente);

                                                p("Falta CADASTRAR o período <br/>de {$dataInicialTemp} até {$dataFinalTemp}", "pAvisoRegularizarVermelho");
                                                $ocorrencia = true;
                                                
                                                # Verifica se tem mais meses
                                                while (strtotime(date_to_bd($dataFinalTemp)) < strtotime(date_to_bd($datafinalProximoSemestre))) {

                                                    # Define a data inicial
                                                    $dataInicialTemp = addDias($dataFinalTemp, 1, false);

                                                    # Define a data final
                                                    $ano = year($dataInicialTemp);
                                                    if (month($dataInicialTemp) <= 6) {
                                                        $dataFinalTemp = "30/06/{$ano}";
                                                    } else {
                                                        $dataFinalTemp = "31/12/{$ano}";
                                                    }

                                                    # Verifica se passou a data final do semestre
                                                    if (dataMenor($dataFinalTemp, $datafinalProximoSemestre) == $datafinalProximoSemestre) {
                                                        $dataFinalTemp = $datafinalProximoSemestre;
                                                    }

                                                    hr("alerta");
                                                    p("Falta CADASTRAR o período <br/>de {$dataInicialTemp} até {$dataFinalTemp}", "pAvisoRegularizarVermelho");
                                                }
                                            }
                                        }
                                    }
                                }

                                # acrescenta o contador
                                $contador++;
                            }
                            
                            if(!$ocorrencia){
                                p("Situação Regular", "pAvisoRegularizarAzul");
                            }
                        } else {

                            if ($pendencia) {
                                return "Sim";
                            } else {
                                $dataInicialTemp = $this->get_dataInicialFormulario($idDependente);
                                $dataFinalTemp = $this->get_dataFinalFormulario($idDependente);

                                p("Falta CADASTRAR o período <br/>de {$dataInicialTemp} até {$dataFinalTemp}", "pAvisoRegularizarVermelho");

                                # Verifica se tem mais meses
                                while (strtotime(date_to_bd($dataFinalTemp)) < strtotime(date_to_bd($datafinalProximoSemestre))) {

                                    # Define a data inicial
                                    $dataInicialTemp = addDias($dataFinalTemp, 1, false);

                                    # Define a data final
                                    $ano = year($dataInicialTemp);
                                    if (month($dataInicialTemp) <= 6) {
                                        $dataFinalTemp = "30/06/{$ano}";
                                    } else {
                                        $dataFinalTemp = "31/12/{$ano}";
                                    }

                                    # Verifica se passou a data final do semestre
                                    if (dataMenor($dataFinalTemp, $datafinalProximoSemestre) == $datafinalProximoSemestre) {
                                        $dataFinalTemp = $datafinalProximoSemestre;
                                    }

                                    hr("alerta");
                                    p("Falta CADASTRAR o período <br/>de {$dataInicialTemp} até {$dataFinalTemp}", "pAvisoRegularizarVermelho");
                                }
                            }
                        }
                    }

                    ###########################################################
                } else {
                    if ($dados["auxEducacao"] == "Não") {
                        if ($pendencia) {
                            return "Não";
                        } else {
                            p("Não Recebe o Auxílio", "pAvisoRegularizarVermelho");
                        }
                    } else {
                        if ($pendencia) {
                            return "Não";
                        } else {
                            p("N/D", "pAvisoRegularizarVermelho");
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

    ##############################################################
}
