<?php

class LicencaSemVencimentos {

    /**
     * Abriga as várias rotina referentes ao afastamento do servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    private $linkEditar = null;
    private $atual = true;
    private $nome = null;

    ###########################################################

    function get_dados($idLicencaSemVencimentos) {

        /**
         * Informe o número do processo de solicitação de redução de carga horária de um servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (vazio($idLicencaSemVencimentos)) {
            alert("É necessário informar o id da Licença Sem Vencimentos.");
        } else {
            # Pega os dados
            $select = 'SELECT *
                         FROM tblicencasemvencimentos
                        WHERE idLicencaSemVencimentos = ' . $idLicencaSemVencimentos;

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);

            # Retorno
            return $row;
        }
    }

    ###########################################################

    function exibeStatus($idLicencaSemVencimentos) {

        /**
         * Informe o status de uma solicitação de redução de carga horária específica
         *
         * @obs Usada na tabela inicial do cadastro de redução
         */
        # Pega os dados
        $dados = $this->get_dados($idLicencaSemVencimentos);

        # Pega os campos necessários
        $dtPublicacao = $dados["dtPublicacao"];
        $crp = $dados["crp"];
        $dtRetorno = $dados["dtRetorno"];
        $dtTermino = $dados["dtTermino"];
        $dtSolicitacao = $dados["dtSolicitacao"];

        $retorno = null;

        # Se estiver vazio a data de publicação -> Em aberto
        if (vazio($dtPublicacao)) {
            $retorno = "Em Aberto";
        } else {
            $retorno = "Vigente";

            # Se não tiver retorno 
            if (vazio($dtRetorno)) {

                # Verifica se tem data de termino
                if (!vazio($dtTermino)) {

                    $dtTermino = date_to_php($dtTermino);

                    # Verifica se já passou
                    if (jaPassou($dtTermino)) {

                        # Se já passou, verifica se já enteogou o crp
                        if (vazio($crp)) {
                            $retorno = "Aguardando CRP";
                        } else {
                            # Se entregou então fica arquivado
                            $retorno = "Arquivado";
                        }
                    } else {
                        # Se a data de término não acabou, é vigente
                        $retorno = "Vigente";
                    }
                }
            } else {
                # Se já passou a data de retorno
                $dtRetorno = date_to_php($dtRetorno);
                if (jaPassou($dtRetorno)) {

                    # E não entregou o crp o status é aguardando o crp
                    if (vazio($crp)) {
                        $retorno = "Aguardando CRP";
                    } else {
                        # Se entregou é arquivado
                        $retorno = "Arquivado";
                    }
                } else {
                    # Se não passou é vigente
                    $retorno = "Vigente";
                }
            }
        }

        if (vazio($dtSolicitacao)) {
            $retorno = "INCOMPLETO";
        }


        return $retorno;
    }

    ###########################################################

    function exibePeriodo($idLicencaSemVencimentos) {

        /**
         * Informe os dados da período de uma solicitação de redução de carga horária específica
         *
         * @obs Usada na tabela inicial do cadastro de redução
         */
        # Pega os dados
        $dados = $this->get_dados($idLicencaSemVencimentos);

        # Pega os campos necessários
        $dtInicial = $dados["dtInicial"];
        $numDias = $dados["numDias"];
        $dtTermino = $dados["dtTermino"];
        $dtRetorno = $dados["dtRetorno"];
        $crp = $dados["crp"];

        # Retorno
        # Trata a data de Início
        if (!vazio($dtInicial)) {
            $dtInicial = date_to_php($dtInicial);
        }

        # Trata o período
        if (!vazio($numDias)) {
            $numDias = $numDias . " dias";
        }

        # Trata a data de término
        if (!vazio($dtTermino)) {
            $dtTermino = date_to_php($dtTermino);
        }

        # Trata a data de retorno
        if (!vazio($dtRetorno)) {
            $dtRetorno = date_to_php($dtRetorno);
        }

        $retorno = "Início : " . trataNulo($dtInicial) . "<br/>"
                . "Período: " . trataNulo($numDias) . "<br/>"
                . "Término: " . trataNulo($dtTermino) . "<br/>"
                . "Retornou: " . trataNulo($dtRetorno);

        # Verifica se estamos a 90 dias da data Termino
        if ((!vazio($dtTermino)) AND (vazio($dtRetorno))) {
            $hoje = date("d/m/Y");
            $dias = dataDif($hoje, $dtTermino);

            if (($dias > 0) AND ($dias < 90)) {
                if ($dias == 1) {
                    $retorno .= "<br/><span title='Falta Apenas $dias dia para o término do benefício. Entrar em contato com o servidor para avaliar renovação do benefício!' class='warning label'>Faltam $dias dias</span>";
                } else {
                    $retorno .= "<br/><span title='Faltam $dias dias para o término do benefício. Entrar em contato com o servidor para avaliar renovação do benefício!' class='warning label'>Faltam $dias dias</span>";
                }
            } elseif ($dias == 0) {
                $retorno .= "<br/><span title='Hoje Termina o benefício!' class='warning label'>Termina Hoje!</span>";
            }
        }

        return $retorno;
    }

    ###########################################################

    function exibeDados($idLicencaSemVencimentos) {

        /**
         * Informe o número do processo e a data da publicação de uma licença sem vencimentos
         *
         * @obs Usada na tabela inicial do cadastro de LSV
         */
        # Pega os dados
        $dados = $this->get_dados($idLicencaSemVencimentos);

        # Pega os campos necessários
        $processo = $dados["processo"];
        $dtPublicacao = $dados["dtPublicacao"];
        $pgPublicacao = $dados["pgPublicacao"];
        $dtSolicitacao = $dados["dtSolicitacao"];

        # Trata a data de retorno
        if (!empty($dtPublicacao)) {
            $dtPublicacao = date_to_php($dtPublicacao);
        }

        # Trata a data de $dtSolicitacao
        if (!empty($dtSolicitacao)) {
            $dtSolicitacao = date_to_php($dtSolicitacao);
        }

        $retorno = "Solicitado em : " . trataNulo($dtSolicitacao) . "<br/>"
                . "Processo : " . trataNulo($processo) . "<br/>"
                . "Publicação: " . trataNulo($dtPublicacao);

        if (!empty($pgPublicacao)) {
            $retorno .= " Pag. " . $pgPublicacao;
        }

        return $retorno;
    }

    ###########################################################

    function exibeRioprevidencia($idLicencaSemVencimentos) {

        /**
         * Informe se o servidor entregou o CRp e o prazo de entrega
         *
         * @obs Usada na tabela inicial do cadastro de LSV
         */
        # Pega os dados
        $dados = $this->get_dados($idLicencaSemVencimentos);

        # Pega os campos necessários
        $crp = $dados["crp"];
        $optouContribuir = $dados["optouContribuir"];
        $dtRetorno = $dados["dtRetorno"];
        $dttermino = $dados["dtTermino"];

        # Verifica Dados do Rioprevidência
        if ($optouContribuir == 1) {
            p("Optou pagar");
        } elseif ($optouContribuir == 2) {
            p("Optou NÃO pagar", "naoPagar");
        }

        # Verifica o CRP
        if ($crp) {
            echo "Entregou CRP";
        } else {
            echo "Não Entregou CRP";

            # Verifica se estamos a 90 dias da data Termino
            if (!empty($dtRetorno)) {
                # Passa para o formato brasileiro
                $dtRetorno = date_to_php($dtRetorno);

                # Calcula a data limite da entrega
                $dtLimite = addDias($dtRetorno, 90);

                if (jaPassou($dtLimite)) {
                    p("Data já Passou!", "jaPassou");
                } else {
                    p("Entregar até: $dtLimite", "plsvPassou");
                }

                # Calcula quantos dias faltam para essa data
                $hoje = date("d/m/Y");
                $dias = dataDif($hoje, $dtLimite);

                if (($dias > 0) AND ($dias < 90)) {
                    if ($dias == 1) {
                        echo "<span title='Falta Apenas $dias dia para o término do prazo para entregar o CRP.' class='warning label'>Falta $dias dia</span>";
                    } else {
                        echo "<span title='Faltam $dias dias para o término do prazo para entregar o CRP!' class='warning label'>Faltam $dias dias</span>";
                    }
                } elseif ($dias == 0) {
                    echo "<span title='Hoje Termina o benefício!' class='warning label'>Termina Hoje!</span>";
                }
            } else {
                if (!empty($dttermino)) {
                    # Passa para o formato brasileiro
                    $dttermino = date_to_php($dttermino);

                    # Verifica se já passou 
                    if (jaPassou($dttermino)) {

                        # Calcula a data limite da entrega
                        $dtLimite = addDias($dttermino, 90);

                        if (jaPassou($dtLimite)) {
                            p("Data já Passou!", "jaPassou");
                        } else {
                            p("Entregar até: $dtLimite", "plsvPassou");
                        }

                        # Calcula quantos dias faltam para essa data
                        $hoje = date("d/m/Y");
                        $dias = dataDif($hoje, $dtLimite);

                        if (($dias > 0) AND ($dias < 90)) {
                            if ($dias == 1) {
                                echo "<span title='Falta Apenas $dias dia para o término do prazo para entregar o CRP.' class='warning label'>Falta $dias dia</span>";
                            } else {
                                echo "<span title='Faltam $dias dias para o término do prazo para entregar o CRP!' class='warning label'>Faltam $dias dias</span>";
                            }
                        } elseif ($dias == 0) {
                            echo "<span title='Hoje Termina o benefício!' class='warning label'>Termina Hoje!</span>";
                        }
                    }
                }
            }
        }
    }

    ###########################################################

    public function set_linkEditar($linkEditar) {
        /**
         * Informa a rotina de edição (se houver)
         *
         * @param $linkEditar string null O link da rotina de edição
         *
         * @syntax $input->set_linkEditar($linkEditar);
         */
        $this->linkEditar = $linkEditar;
    }

    ###########################################################

    public function get_nomeLicenca($idTpLicenca) {
        /**
         * Informa o nome da licença de forma compacta para ser exibida na tabela
         *
         * @param $idTpLicenca integer null O id do tipo de licença
         *
         * @syntax $input->get_nomeLicenca($idTpLicenca);
         */
        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        # Pega o nome da Licença
        $tipo = $pessoal->get_nomeTipoLicenca($idTpLicenca);

        # Acha a posição do -
        $trac = stripos($tipo, "-");

        # Retira a primeira parte da string
        $pedaco = substr($tipo, $trac + 1);

        return plm($pedaco);
    }

    ###########################################################

    public function exibeLista($nome = null) {

        /**
         * Exibe uma tabela com a relação dos servidores comafastamento
         *
         * @syntax $input->exibeTabela();
         */
        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        $data = date("Y-m-d");

        # Licença
        $select = "SELECT idLicencaSemVencimentos,
                          idLicencaSemVencimentos,   
                         CASE tipo
                             WHEN 1 THEN 'Inicial'
                             WHEN 2 THEN 'Renovação'
                             ELSE '--'
                         END,
                         idServidor,
                         idTpLicenca,
                         idLicencaSemVencimentos,
                         idLicencaSemVencimentos,
                         idLicencaSemVencimentos,
                         idServidor
                    FROM tblicencasemvencimentos JOIN tbservidor USING (idServidor)
                                                 JOIN tbpessoa USING (idPessoa)
                    WHERE TRUE";

        # Nome
        if (!empty($nome)) {

            # Verifica se tem espaços
            if (strpos($nome, ' ') !== false) {
                # Separa as palavras
                $palavras = explode(' ', $nome);

                # Percorre as palavras
                foreach ($palavras as $item) {
                    $select .= " AND (tbpessoa.nome LIKE '%{$item}%')";
                }
            } else {
                $select .= " AND (tbpessoa.nome LIKE '%{$nome}%')";
            }
        }

        $select .= " ORDER BY dtSolicitacao desc, dtInicial desc";

        $result = $pessoal->select($select);
        $count = $pessoal->count($select);

        $titulo = 'Licença Sem Vencimentos';

        $tabela = new Tabela();
        $tabela->set_titulo($titulo);
        $tabela->set_conteudo($result);

        $tabela->set_label(["#", "Status", "Tipo", "Nome", "Licença / Afastemento", "Dados", "Período", "Rioprevidência"]);
        $tabela->set_align([null, "center", "center", "left", "left", "left", "left"]);
        $tabela->set_classe([null, "LicencaSemVencimentos", null, "Pessoal", "LicencaSemVencimentos", "LicencaSemVencimentos", "LicencaSemVencimentos", "LicencaSemVencimentos"]);
        $tabela->set_metodo([null, "exibeStatus", null, "get_nome", "get_nomeLicenca", "exibeDados", "exibePeriodo", "exibeRioprevidencia"]);

        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 1,
                'valor' => 'Em Aberto',
                'operador' => '=',
                'id' => 'emAberto'),
            array('coluna' => 1,
                'valor' => 'Arquivado',
                'operador' => '=',
                'id' => 'arquivado'),
            array('coluna' => 1,
                'valor' => 'Vigente',
                'operador' => '=',
                'id' => 'vigenteReducao')
        ));

        $tabela->set_idCampo('idServidor');
        $tabela->set_editar($this->linkEditar);

        if ($count > 0) {
            $tabela->show();
        } else {
            titulotable($titulo);
            callout("Nenhum valor a ser exibido !", "secondary");
        }
    }

    ###########################################################

    public function exibeRelatorio($nome = null) {

        /**
         * Exibe uma tabela com a relação dos servidores comafastamento
         *
         * @syntax $input->exibeTabela();
         */
        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        $relatorio = new Relatorio();

        # Licença
        $select = 'SELECT idLicencaSemVencimentos,
                         CASE tipo
                             WHEN 1 THEN "Inicial"
                             WHEN 2 THEN "Renovação"
                             ELSE "--"
                         END,
                         idServidor,
                         idTpLicenca,
                         idLicencaSemVencimentos,
                         idLicencaSemVencimentos, 
                         idLicencaSemVencimentos,
                         idServidor
                    FROM tblicencasemvencimentos JOIN tbservidor USING (idServidor)
                                                 JOIN tbpessoa USING (idPessoa)
                    WHERE TRUE';

        # Nome
        if (!empty($nome)) {

            # Verifica se tem espaços
            if (strpos($nome, ' ') !== false) {
                # Separa as palavras
                $palavras = explode(' ', $nome);

                # Percorre as palavras
                foreach ($palavras as $item) {
                    $select .= " AND (tbpessoa.nome LIKE '%{$item}%')";
                }
            } else {
                $select .= " AND (tbpessoa.nome LIKE '%{$nome}%')";
            }
            $relatorio->set_subtitulo("Pesquisa: {$nome}");
        }

        $select .= ' ORDER BY dtSolicitacao desc, dtInicial desc';

        $result = $pessoal->select($select);
        $count = $pessoal->count($select);

        $titulo = 'Servidores Em Licença Sem Vencimentos';

        # Monta o Relatório        
        $relatorio->set_titulo($titulo);
        $relatorio->set_label(["Status", "Tipo", "Nome", "Licença / Afastamento", "Dados", "Período", "Entregou CRP?"]);
        $relatorio->set_align(["center", "center", "left", "left", "left", "left"]);
        $relatorio->set_classe(["LicencaSemVencimentos", null, "Pessoal", "LicencaSemVencimentos", "LicencaSemVencimentos", "LicencaSemVencimentos", "LicencaSemVencimentos"]);
        $relatorio->set_metodo(["exibeStatus", null, "get_nome", "get_nomeLicenca", "exibeDados", "exibePeriodo", "exibeRioprevidencia"]);
        $relatorio->set_bordaInterna(true);
        $relatorio->set_conteudo($result);

        $relatorio->show();
    }

    ###########################################################

    function exibeBotaoDocumentos($idLicencaSemVencimentos) {

        /**
         * Exibe o botão de imprimir os documentos
         * 
         * @obs Usada na tabela inicial do cadastro de LSV
         */
        $menu = new Menu("menuBeneficios");

        # Despacho
        $menu->add_item('linkWindow', "\u{1F5A8} Reitoria - Nada Opor", '../grhRelatorios/lsv.despacho.reitoria.php?id=' . $idLicencaSemVencimentos, "Despacho à reitoria para emitir o nada opor a concessão da Licença");
        $menu->add_item('linkWindow', "\u{1F5A8} Sepof - Publicação", '../grhRelatorios/lsv.despacho.sepof.php?id=' . $idLicencaSemVencimentos, "Despacho ao Sepof solicitando publicação");
        $menu->add_item('linkWindow', "\u{1F5A8} Rioprev - Padrão", '../grhRelatorios/lsv.despacho.rioprev.padrao.php?id=' . $idLicencaSemVencimentos, "Despacho padrão ao Rioprev para emissão dos boletos de pgto");
        $menu->add_item('linkWindow', "\u{1F5A8} Carta de Reassunção", '?fase=cartaReassuncao&id=' . $idLicencaSemVencimentos);
        $menu->add_item('linkWindow', "\u{1F5A8} Rioprev - CSP/CRP", '../grhRelatorios/lsv.despacho.rioprev.crp.php?id=' . $idLicencaSemVencimentos, "Despacho ao Rioprev para emissão de CSP/CRP");
        $menu->add_item('linkWindow', "\u{1F5A8} Despacho - Servidor Com Débito", '../grhRelatorios/lsv.despacho.debito.php?id=' . $idLicencaSemVencimentos, "Despacho ao Servidor");

        $menu->show();
    }

    ##########################################################################################

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = 'SELECT obs
                     FROM tblicencasemvencimentos
                    WHERE idLicencasemvencimentos = ' . $id;

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno[0]);
        }
    }

###########################################################
}
