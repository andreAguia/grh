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
        #####################################m Parei aqui
        # Pega os dados
        $dependente = new Dependente();
        $dados = $dependente->get_dados($id);

        # Pega os parentescos com direito au auxEducação
        $tipos = $dependente->get_arrayTipoParentescoAuxEduca();

        # Verifica se tem direito
        if (in_array($dados["idParentesco"], $tipos)) {

            # Pega as datas limites
            $anos21 = get_dataIdade(date_to_php($dados["dtNasc"]), 21);
            $anos24 = get_dataIdade(date_to_php($dados["dtNasc"]), 24);

            # Data Histórica Inicial
            $intra = new Intra();
            $dataHistoricaInicial = $intra->get_variavel('dataHistoricaInicialAuxEducacao');

            # Verifica se perdeu o direito antes da data histórica
            if (dataMenor($dataHistoricaInicial, $anos24) <> $anos24) {

                if ($dados["auxEducacao"] == "Sim") {

                    if (idade(date_to_php($dados["dtNasc"])) > 21) {
                        $dadosComprovantes = $this->get_dadosIdDependente($id);
                        $ultimaDatacomprovada = $this->get_ultimaDataComprovada($id);

                        # Verifica se tem mais que 21 e não comprovou nada
                        if (empty($ultimaDatacomprovada)) {
                            $ultimaDatacomprovada = $anos21;
                        }

                        # Verifica se existe ainda algum período possível
                        if ($anos24 <> $ultimaDatacomprovada) {
                            if (jaPassou($ultimaDatacomprovada)) {
                                return "Sim";
                            } else {
                                return "Não";
                            }
                        }
                    } else {
                        return "Não";
                    }
                } else {
                    return "---";
                }
            }
        } else {
            return "---";
        }
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
            p("Sim", "pAvisoRegularizarVermelho");
        } elseif ($dado == "Não") {
            p("Não", "pAvisoRegularizarAzul");
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

        # Pega os parentescos com direito au auxEducação
        $tipos = $dependente->get_arrayTipoParentescoAuxEduca();

        # Verifica se tem direito
        if (in_array($dados["idParentesco"], $tipos)) {

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
                    'valor' => $dependente->get_dtInicialAuxEducacao($id),
                    'operador' => '=',
                    'id' => 'alerta')));

            $tabela->show();

            /*
             * Exibe o período sem obrigação de 
             * enviar a declaração de escolaridade
             */

            tituloTable("Sem Declaração de Escolaridade");
            $painel = new Callout("warning");
            $painel->abre();

            # Verifica se teve período sem precisar comprovar
            if (dataMenor($dataHistoricaInicial, $anos21) == $anos21) {
                p("Dependente já tinha mais de 21 anos quando adquiriu o direito!", "center", "f14");
            } else {
                p($dependente->get_dtInicialAuxEducacao($id) . " a " . $anos21, "center", "f14");
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
    
}
