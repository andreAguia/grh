<?php

class Dependente {

    /**
     * Abriga as várias rotina do COntrole de parentes e dependentes
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
        if (!empty($id)) {
            $pessoal = new Pessoal();
            return $pessoal->select("SELECT * FROM tbdependente WHERE idDependente = {$id}", false);
        } else {
            return null;
        }
    }

    ###########################################################

    public function exibeNomeParentescoNascimento($id) {

        # Pega os dados
        $dados = $this->get_dados($id);
        $pessoal = new Pessoal();
        plista(
                $dados["nome"],
                "Nascimento: " . date_to_php($dados["dtNasc"]),
                $pessoal->get_parentesco($dados["idParentesco"])
        );
    }

    ###########################################################

    public function exibeNomeParentesco($id) {

        # Pega os dados
        $dados = $this->get_dados($id);
        $pessoal = new Pessoal();
        plista(
                $dados["nome"],
                $pessoal->get_parentesco($dados["idParentesco"])
        );
    }

    ###########################################################

    public function exibeNomeCpf($id) {

        # Pega os dados
        $dados = $this->get_dados($id);

        # verifica se tem cpf cadastrado
        if (empty($dados["cpf"])) {
            $cpf = null;
        } else {
            $cpf = "CPF: " . $dados["cpf"];
        }

        # Exibe o nome e o cpf (quando houver)
        plista(
                $dados["nome"],
                $cpf
        );
    }

    ###########################################################

    public function exibeNascimentoIdade($id) {

        # Pega os dados
        $dados = $this->get_dados($id);

        # Exibe os dados
        plista(
                date_to_php($dados["dtNasc"]),
                idade(date_to_php($dados["dtNasc"])) . " anos"
        );
    }

    ###########################################################
    /*
     * Verifica se o dependente tinha mais de 24 ou não na data da publicação da lei 9450/2021
     */

    public function tinhaDireitoDataHistorica($id) {

        # Pega os dados
        $dados = $this->get_dados($id);

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

    public function exibeauxEducacao($id) {

        # Pega os dados
        $dados = $this->get_dados($id);

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
                p("Estava com mais de 24 anos<br/>na data de Publicação<br/>da Portaria nº95 - {$dataHistoricaInicial}", "pDependenteSDireito");
            } else {

                if ($dados["auxEducacao"] == "Sim") {

                    # Informa O tempo que tem direito sem comprovar escolaridade
                    # Verifica se fez 21 antes da data historica inicial
                    if (dataMenor($dataHistoricaInicial, $anos21) == $dataHistoricaInicial) {
                        echo "- Período sem precisar da escolaridade:";
                        br();
                        p("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;de " . date_to_php($dados["auxEducacaoDtInicial"]) . " a " . $this->get_dtTerminoAuxEducacao($id), "plistaDeclaracaoRecebida");
                        hr("grosso1");
                    }

                    if (idade(date_to_php($dados["dtNasc"])) >= 21) {
                        echo "- Fez 21 anos em: {$anos21}";
                        hr("grosso1");
                    } else {
                        echo "- Fará 21 anos em: {$anos21}";
                        hr("grosso1");
                    }

                    if (idade(date_to_php($dados["dtNasc"])) > 21) {
                        $auxEdu = new AuxilioEducacao();
                        $dadosComprovantes = $auxEdu->get_dadosIdDependente($id);
                        $ultimaDatacomprovada = $auxEdu->get_ultimaDataComprovada($id);

                        if (count($dadosComprovantes) > 0) {
                            echo "- Comprovantes Recebidos";
                            br();
                            foreach ($dadosComprovantes as $item) {
                                p("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . date_to_php($item['dtInicio']) . " a " . date_to_php($item['dtTermino']), "plistaDeclaracaoRecebida");
                                hr("geral");
                            }
                            hr("grosso1");
                        }

                        # Verifica se tem mais que 21 e não comprovou nada
                        if (empty($ultimaDatacomprovada)) {
                            $ultimaDatacomprovada = $anos21;
                        }

                        # Verifica se existe ainda algum período possível
                        if ($anos24 <> $ultimaDatacomprovada) {
                            
                            # Pega a data do téwrmino desse semestre
                            $dtTermino = $this->get_dtFinalAuxEducacaoControle($id);
                            
                            
                            if (jaPassou($dtTermino)) {
                                p("Regularizar o período:<br/>de {$ultimaDatacomprovada} até {$dtTermino}", "pAvisoRegularizarVermelho");
                            }
                            hr("grosso1");
                        }
                    }

                    if (idade(date_to_php($dados["dtNasc"])) >= 24) {
                        echo "- Fez 24 anos em: {$anos24}";
                        br();
                    } else {
                        echo "- Fará 24 anos em: {$anos24}";
                        br();
                    }
                } else {
                    if ($dados["auxEducacao"] == "Não") {
                        p("Não", "vermelho", "center");
                    } else {
                        p("N/D", "vermelho", "center");
                    }
                }
            }
        } else {
            echo "---";
        }
    }

    ###########################################################

    public function exibeauxEducacaoControle($id) {

        # Pega os dados
        $dados = $this->get_dados($id);

        # Verifica se é filho (2), tutela(8) ou guarda provisória(9) 
        if (($dados["idParentesco"] == 2 OR $dados["idParentesco"] == 8 OR $dados["idParentesco"] == 9) AND ($dados["auxEducacao"] == "Sim")) {
            # botão
            $link = new Link(null, "?fase=comprovante&id={$id}");
            $link->set_id("btnAuxEduc");
            $link->set_imagem(PASTA_FIGURAS . 'declaracao.png', 20, 20);
            $link->set_title("Controle do envio de comprovante de escolaridade");
            $link->show();
        } else {
            echo "---";
        }
    }

    ###########################################################

    public function exibeBotaoControleEscolaridade($id) {

        # Pega os dados
        $dados = $this->get_dados($id);

        if ($dados["auxEducacao"] == "Sim") {
            if (idade(date_to_php($dados["dtNasc"])) > 21) {
                
            } else {
                echo "---";
            }
        } else {
            echo "---";
        }
    }

    ###########################################################

    public function get_dtTerminoAuxEducacao($id) {

        # Pega os dados
        $dados = $this->get_dados($id);
        return get_dataIdade(date_to_php($dados["dtNasc"]), 21);
    }

    ###########################################################

    public function get_dtInicialAuxEducacao($id) {

        # Dados do Dependente
        $dados = $this->get_dados($id);
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

    public function get_nome($id) {

        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);
            return $dados["nome"];
        }
    }

    ###########################################################

    public function get_idParentesco($id) {

        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);
            return $dados["idParentesco"];
        }
    }

     ###########################################################

    public function get_idServidor($id) {

        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);
            
            $pessoal = new Pessoal();            
            return $pessoal->get_idServidoridPessoa($dados["idPessoa"]);
        }
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
            # Pega os dados
            $dados = $this->get_dados($id);

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

    public function get_dtFinalAuxEducacaoControle($id) {

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
            # Pega os dados
            $dados = $this->get_dados($id);

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
}
