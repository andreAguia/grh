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

    public function get_auxEducacaoCobrancaDataInicial($id) {

        /**
         * Informa a data em que o dependente faz a idade inicial do direito
         */
        # Pega os dados do dependente
        $dependente = new Dependente();
        $dados = $dependente->get_dados($id);

        return get_dataIdade(date_to_php($dados["dtNasc"]), $this->idadeInicial);
    }

    ##############################################################

    public function get_auxEducacaoCobrancaDataFinal($id) {

        /**
         * Informa a data em que o dependente faz a idade final do direito
         * menos um para atender a lei
         */
        # Pega os dados do dependente
        $dependente = new Dependente();
        $dados = $dependente->get_dados($id);

        # Retorna a data anterior - um dia antes
        return addDias(get_dataIdade(date_to_php($dados["dtNasc"]), $this->idadeFinal), -1, false);
    }

    ##############################################################

    public function get_dataInicialDeFato($id) {

        /**
         * Informa, comprarando todas as variáveis, a data que,
         * de fato, o servidor tem o direito ao benefício
         */
        # Pega as possíveis datas a serem analisadas
    }

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
            $dataInicioCobranca = $this->get_auxEducacaoCobrancaDataInicial($id);
            $dataFinalCobranca = $this->get_auxEducacaoCobrancaDataFinal($id);

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
                    'valor' => $this->get_auxEducacaoDataInicial($id),
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

    public function get_auxEducacaoControleDataInicial($id) {

        /**
         * fornece a data inicial de um lançamento do controle de declaração escolar para o Auxílio educação
         * 
         * @param $id integer null O id 
         * 
         * @syntax $dependente->get_auxEducacaoControleDataInicial([$id]);
         */
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados do dependente
            $dependente = new Dependente();
            $dados = $dependente->get_dados($id);

            # Pega as datas limites
            $anos21 = get_dataIdade(date_to_php($dados["dtNasc"]), $this->idadeInicial);
            $dtInicioGeral = $this->get_auxEducacaoDataInicial($id);

            # Pega o último comprovante deste dependente
            $pessoal = new Pessoal();
            $comprovantes = $pessoal->select("SELECT * FROM tbauxeducacao WHERE idDependente = {$id} ORDER BY dtInicio desc LIMIT 1", false);

            if (empty($comprovantes[0])) {
                # Verifica se fez 21 anos apos a data inicial limite
                if (dataMaior($dtInicioGeral, $anos21) == $dtInicioGeral) {
                    return $dtInicioGeral;
                } else {
                    return $anos21;
                }
            } else {
                return addDias(date_to_php($comprovantes["dtTermino"]), 1, false);
            }
        }
    }

    ###########################################################

    public function get_auxEducacaoControleDataFinal($id) {

        /**
         * fornece a data final de um lançamento do controle de declaração escolar para o Auxílio educação
         * 
         * @param $id integer null O id 
         * 
         * @syntax $dependente->get_auxEducacaoControleDataFinal([$id]);
         */
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados do dependente
            $dependente = new Dependente();
            $dados = $dependente->get_dados($id);

            # Pega as datas
            $dtInicial = $this->get_auxEducacaoControleDataInicial($id);
            $dataFinal = $this->get_auxEducacaoCobrancaDataFinal($id);

            if (month($dtInicial) < 6) {
                return dataMenor("30/06/" . year($dtInicial), $dataFinal);
            } else {
                return dataMenor("31/12/" . year($dtInicial), $dataFinal);
            }
        }
    }

###########################################################

    public function get_auxEducacaoDataInicial($id) {

        /*
         * Informa a data em que o servidor passou a ter direito a receber o banefício
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

    /*
     * Verifica se o dependente tinha mais de 24 ou não na data da publicação da lei 9450/2021
     * 
     * 
     * VERRRRR
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

            if (dataMenor($dataHistoricaInicial, $this->get_auxEducacaoCobrancaDataFinal($id)) == $this->get_auxEducacaoCobrancaDataFinal($id)) {
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

    public function fazAnalise($id, $pendencia = false) {

        # Pega os dados do dependente
        $dependente = new Dependente();
        $dados = $dependente->get_dados($id);

        # Pega os parentescos com direito au auxEducação
        $tipos = $this->get_arrayTipoParentescoAuxEduca();

        # Verifica se tem direito
        if (in_array($dados["idParentesco"], $tipos)) {

            # Datas de acordo com a idade
            $dataInicioCobranca = $this->get_auxEducacaoCobrancaDataInicial($id);
            $dataFinalCobranca = $this->get_auxEducacaoCobrancaDataFinal($id);

            # Data Inicial (data de fato)
            $dataInicialDeFato = $this->get_auxEducacaoDataInicial($id);

            # Data em que iniciou a portaria
            $intra = new Intra();
            $dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');

            # Verifica se perdeu o direito antes da data histórica
            if (dataMenor($dataHistoricaInicial, $dataFinalCobranca) == $dataFinalCobranca) {
                if ($pendencia) {
                    return "Não";
                } else {
                    p("Estava com mais de {$this->idadeFinal} anos<br/>na data de Publicação<br/>da Portaria nº95 - {$dataHistoricaInicial}", "pDependenteSDireito");
                }
            } else {

                # Verifica se marcou Sim no aux Educação
                if ($dados["auxEducacao"] == "Sim") {

                    # Verifica se e menor de 21 anos e 
                    # informa a partir de quando fica sem precisar comprovar escolaridade
                    if (idade(date_to_php($dados["dtNasc"])) < $this->idadeInicial) {
                        if ($pendencia) {
                            return "Não";
                        } else {
                            p("Situação regular até:<br/>{$dataInicioCobranca} ({$this->idadeInicial} anos)", "pAvisoRegularizarAzul");
                        }
                    }

                    ###########################################################
                    # Verifica se tem mais que a idade inicial de cobrança (21 anos)
                    if (idade(date_to_php($dados["dtNasc"])) >= $this->idadeInicial) {

                        # Pega os dados de todos os comprovantes entregues
                        $pessoal = new Pessoal();
                        $row = $pessoal->select("SELECT * FROM tbauxeducacao WHERE idDependente = {$id} ORDER BY dtInicio");

                        # Variáveis uteis
                        $contador = 1;
                        $ultDataCadastrada = null;

                        # Percorre os dados
                        foreach ($row as $item) {
                            # Acerta os dados
                            $dataInicial = date_to_php($item["dtInicio"]);
                            $dataFinal = date_to_php($item["dtTermino"]);

                            #echo $dataInicial, " - ", $dataInicialDeFato;br();
                            # Verfica se existe algum período faltando
                            if ($contador == 1) {
                                # Atualiza a data anterior
                                $dataFinalAnterior = addDias($dataFinal, 1, false);
                            } else {  ##################################################################### Parei aqui
                                if ($dataInicial <> $dataFinalAnterior) {
                                    if ($pendencia) {
                                        return "Sim";
                                    } else {
                                        p("Falta cadastrar o período<br/>de {$dataFinalAnterior} até {$dataInicial}", "pAvisoRegularizarAzul");
                                        hr("alerta");
                                    }
                                    # Atualiza a data anterior
                                    $dataFinalAnterior = addDias($dataFinal, 1, false);
                                }
                            }
                            
                            echo $dataInicial,"->",$dataFinal," [",$dataFinalAnterior,"]<br/>";

                            # Verifica a primeira data cadastrada bate com a data inicial do controle
                            if (dataMenor($dataInicial, $dataInicialDeFato) == $dataInicialDeFato AND $contador == 1) {

                                if ($pendencia) {
                                    return "Sim";
                                } else {
                                    p("Falta cadastrar o período<br/>de {$dataInicialDeFato} até {$dataInicial}", "pAvisoRegularizarAzul");
                                    hr("alerta");
                                }
                            }

                            # Verifica se informou que estudou e tem comprovante
                            if (!$this->temComprovante($item["idAuxEducacao"]) AND $item["estudou"] <> "Não") {
                                if ($pendencia) {
                                    return "Sim";
                                } else {
                                    p("Falta regularizar período<br/>de {$dataInicial} até {$dataFinal}", "pAvisoRegularizarAzul");
                                    hr("alerta");
                                }
                            }

                            # acrescenta o contador
                            $contador++;
                        }




//                        $ultimaDatacomprovada = $this->get_ultimaDataComprovada($id);
//
//                        # Verifica se tem mais que 21 e não comprovou nada
//                        if (empty($ultimaDatacomprovada)) {
//                            $ultimaDatacomprovada = $dataInicioCobranca;
//                        }
//
//                        # Verifica se existe ainda algum período possível
//                        if (strtotime(date_to_bd($ultimaDatacomprovada)) < strtotime(date_to_bd($dataFinalCobranca))) {
//
//                            # Pega a data Inicial e de término desse semestre
//                            $dtTermino = $this->get_auxEducacaoControleDataFinal($id);
//                            $dtInicial = $this->get_auxEducacaoControleDataInicial($id);
//
//                            if (jaPassou($dtInicial)) {
//                                if ($pendencia) {
//                                    return "Sim";
//                                } else {
//                                    p("Regularizar o período:<br/>de " . addDias($ultimaDatacomprovada, 1, false) . " até {$dtTermino}", "pAvisoRegularizarVermelho");
//                                }
//                            } else {
//                                if ($pendencia) {
//                                    return "Não";
//                                } else {
//                                    p("Em {$dtInicial}<br/>comprovar o periodo de<br/>{$ultimaDatacomprovada} até {$dtTermino}", "pAvisoRegularizarAzul");
//                                    if ($dtTermino == $dataFinalCobranca) {
//                                        p("(Quando encerra o direito)", "pAvisoRegularizarVermelho");
//                                    }
//                                }
//                            }
//                        } else {
//                            if ($pendencia) {
//                                return "Não";
//                            } else {
//                                p("Dependente já encerrou o direito e comprovou todos os período possíveis", "pAvisoRegularizarAzul");
//                            }
//                        }
                    }

                    ###########################################################
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
