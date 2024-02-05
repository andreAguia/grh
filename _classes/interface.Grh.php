<?php

class Grh {
    /**
     * Encapsula as rotivas de interface do sistema de pessoal
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     */
######################################################################################################################    

    /**
     * Método cabecalho
     * 
     * Exibe o cabecalho
     */
    public static function cabecalho($titulo = null) {
        # tag do cabeçalho
        echo '<header>';

        $cabec = new Div('center');
        $cabec->abre();
        $imagem = new Imagem(PASTA_FIGURAS . 'uenf.jpg', 'Área do Servidor da Uenf', 190, 60);
        $imagem->show();
        $cabec->fecha();

        if (!(is_null($titulo))) {
            br();
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Topbar        
            $top = new TopBar($titulo);
            $top->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
        }
        echo '</header>';
    }

######################################################################################################################

    /**
     * método quadroLicencaPremio
     * Exibe um quadro informativo da licença Prêmio de um servidor
     */
    public static function quadroLicencaPremio($idServidor) {

        # Pega os dados para o alerta
        $licenca = new LicencaPremio();
        $diasPublicados = $licenca->get_numDiasPublicados($idServidor);
        $diasFruidos = $licenca->get_numDiasFruidos($idServidor);
        $diasDisponiveis = $licenca->get_numDiasDisponiveis($idServidor);

        # Div do numero de serviços
        $div = new Div('divQuadroLicenca');
        $div->set_title('Quadro de Licenças Prêmio e Publicações');
        $div->abre();

        # Tabela de Serviços
        $mesServico = date('m');
        $tabela = array(array('Dias Publicados', $diasPublicados),
            array('Dias Fruídos', $diasFruidos),
            array('Disponíveis', $diasDisponiveis));
        $estatistica = new Tabela();
        $estatistica->set_conteudo($tabela);
        $estatistica->set_label(array("", ""));
        $estatistica->set_align(array("center"));
        $estatistica->set_width(array(60, 40));
        $estatistica->set_totalRegistro(false);
        $estatistica->show();

        $div->fecha();
    }

######################################################################################################################

    /**
     * método quadroVagasCargoComissao
     * Exibe um quadro informativo das vagas dos Cargos em Comissão
     */
    public static function quadroVagasCargoComissao() {
        $select = 'SELECT descricao,
                          simbolo,
                          valsal,
                          vagas,                               
                          idTipoComissao,
                          idTipoComissao,
                          idTipoComissao,
                          idTipoComissao
                     FROM tbtipocomissao
                    WHERE ativo
                    ORDER BY 2 asc';

        # Conecta com o banco de dados
        $servidor = new Pessoal();
        $result = $servidor->select($select);

        # Verifica se tem registros a serem exibidos
        if (count($result) == 0) {
            $p = new P('Nenhum item encontrado !!', 'center');
            $p->show();
        } else {
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(array("Cargo", "Simbolo", "Valor (R$)", "Vagas", "Nomeados", "ProTempore", "Designados", "Vagas Disponíveis"));
            #$tabela->set_width(array(25,15,15,15,15,15));
            $tabela->set_align(array("left"));
            $tabela->set_funcao(array(null, null, "formataMoeda"));
            $tabela->set_classe(array(null, null, null, null, 'CargoComissao', 'CargoComissao', 'CargoComissao', 'CargoComissao'));
            $tabela->set_metodo(array(null, null, null, null, 'get_numServidoresNomeados', 'get_numServidoresProTempore', 'get_numServidoresDesignados', 'get_vagasDisponiveis'));
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 7,
                    'valor' => 0,
                    'operador' => '<',
                    'id' => "comissaoVagasNegativas"),
                array('coluna' => 7,
                    'valor' => 0,
                    'operador' => '=',
                    'id' => "comissaoSemVagas"),
                array('coluna' => 7,
                    'valor' => 0,
                    'operador' => '>',
                    'id' => "comissaoComVagas")));

            $tabela->show();
        }
    }

######################################################################################################################

    /**
     * método listaDadosServidor
     * Exibe os dados principais do servidor logado
     * 
     * @param    string $idServidor -> idServidor do servidor
     */
    public static function listaDadosServidor($idServidor = null, $detalhado = true) {

        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        # Telas maiores
        $div = new Div(null, "hide-for-small-only");
        $div->abre();

        $select = 'SELECT tbservidor.idServidor,
                          tbpessoa.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.dtAdmissao,
                          tbservidor.idServidor,
                          tbservidor.dtDemissao
                     FROM tbservidor LEFT JOIN tbpessoa ON tbservidor.idPessoa = tbpessoa.idPessoa
                                     LEFT JOIN tbsituacao ON tbservidor.situacao = tbsituacao.idsituacao
                    WHERE idServidor = ' . $idServidor;

        $conteudo = $servidor->select($select, true);

        # Pega a situação
        $situacao = $servidor->get_situacao($idServidor);

        if ($situacao == "Ativo") {
            $label = ["Id Funcional / Matrícula", "Servidor", "Cargo - Área - Função (Comissão)", "Lotação", "Perfil", "Admissão", "Situação"];
            $function = [null, null, null, null, null, "date_to_php"];
        } else {
            $label = ["Id Funcional / Matrícula", "Servidor", "Cargo - Área - Função (Comissão)", "Lotação", "Perfil", "Admissão", "Situação", "Saída"];
            $function = [null, null, null, null, null, "date_to_php", null, "date_to_php"];
        }
        #$align = array("center");

        $classe = ["pessoal", null, "pessoal", "pessoal", "pessoal", null, "pessoal"];

        if ($detalhado) {
            $metodo = ["exibe_idFuncionalEMatricula", null, "get_cargoCompleto2", "get_Lotacao", "get_Perfil", null, "get_situacaoDetalhada"];
        } else {
            $metodo = ["exibe_idFuncionalEMatricula", null, "get_cargoCompleto2", "get_Lotacao", "get_Perfil", null, "get_Situacao"];
        }

        $formatacaoCondicional = array(
            array('coluna' => 1,
                'valor' => $servidor->get_nome($idServidor),
                'operador' => '=',
                'id' => 'listaDados'));

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conteudo);
        $tabela->set_label($label);
        $tabela->set_funcao($function);
        $tabela->set_classe($classe);
        $tabela->set_metodo($metodo);
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional($formatacaoCondicional);

        $tabela->show();

        $div->fecha();

        ######################################3
        # Telas menores
        $div = new Div(null, "show-for-small-only");
        $div->abre();

        $select = 'SELECT tbservidor.idServidor,
                             tbpessoa.nome,
                             tbservidor.idServidor
                        FROM tbservidor LEFT JOIN tbpessoa ON tbservidor.idPessoa = tbpessoa.idPessoa
                                        LEFT JOIN tbsituacao ON tbservidor.situacao = tbsituacao.idsituacao
                       WHERE idServidor = ' . $idServidor;

        $conteudo = $servidor->select($select, true);

        # Pega a situação
        $situacao = $servidor->get_situacao($idServidor);
        $label = array("Id Funcional / Matrícula", "Servidor", "Perfil");
        $function = array(null, null, null);
        $classe = array("pessoal", null, "pessoal");
        $metodo = array("get_idFuncionalEMatricula", null, "get_Perfil");

        $formatacaoCondicional = array(
            array('coluna' => 1,
                'valor' => $servidor->get_nome($idServidor),
                'operador' => '=',
                'id' => 'listaDados'));

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conteudo);
        $tabela->set_label($label);
        $tabela->set_funcao($function);
        $tabela->set_classe($classe);
        $tabela->set_metodo($metodo);
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional($formatacaoCondicional);

        $tabela->show();

        $select = 'SELECT tbservidor.idServidor,
                             tbservidor.dtAdmissao,
                             tbservidor.idServidor,
                             tbservidor.idServidor,
                             tbservidor.dtDemissao
                        FROM tbservidor LEFT JOIN tbpessoa ON tbservidor.idPessoa = tbpessoa.idPessoa
                                           LEFT JOIN tbsituacao ON tbservidor.situacao = tbsituacao.idsituacao
                       WHERE idServidor = ' . $idServidor;

        $conteudo = $servidor->select($select, true);

        # Pega a situação
        $situacao = $servidor->get_situacao($idServidor);

        if ($situacao == "Ativo") {
            $label = array("Cargo", "Admissão", "Lotação", "Situação");
            $function = array(null, "date_to_php");
        } else {
            $label = array("Cargo", "Admissão", "Lotação", "Situação", "Saída");
            $function = array(null, "date_to_php", null, null, "date_to_php");
        }

        $classe = array("pessoal", null, "pessoal", "pessoal");
        $metodo = array("get_CargoCompleto2", null, "get_Lotacao", "get_Situacao");

        $formatacaoCondicional = array(
            array('coluna' => 3,
                'valor' => $situacao,
                'operador' => '=',
                'id' => 'listaDados'));

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conteudo);
        $tabela->set_label($label);
        $tabela->set_funcao($function);
        $tabela->set_classe($classe);
        $tabela->set_metodo($metodo);
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional($formatacaoCondicional);

        $tabela->show();

        $div->fecha();

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

######################################################################################################################

    /**
     * método listaFolgasTre
     * Exibe os dados de Folgas do TRE
     * 
     * @param    string $idServidor -> idServidor do servidor
     */
    public static function listaFolgasTre($idServidor) {
        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $folgasConcedidas = $servidor->get_treFolgasConcedidas($idServidor);
        $folgasFruidas = $servidor->get_treFolgasFruidas($idServidor);
        $folgasPendentes = $folgasConcedidas - $folgasFruidas;

        # Div do numero de folgas
        $div = new Div('divAfastamentoTre');
        $div->abre();

        # Tabela
        $folgas = Array(Array('Folgas Concedidas', $folgasConcedidas),
            Array('Folgas Fruídas', $folgasFruidas),
            Array('Folgas Pendentes', $folgasPendentes));
        #$label = array("Folgas","Dias");
        $label = array("", "");
        $width = array(70, 30);
        $align = array("left");

        $tabela = new Tabela("tabelaTre");
        #$estatistica->set_titulo('Legenda'); 
        $tabela->set_conteudo($folgas);
        $tabela->set_cabecalho($label, $width, $align);
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 0,
                'valor' => 'Folgas Pendentes',
                'operador' => '=',
                'id' => 'trePendente')));

        $tabela->show();

        $div->fecha();
    }

######################################################################################################################

    /**
     * método listaDadosServidorRelatório
     * Exibe os dados principais do servidor para relatório
     * 
     * @param string $idServidor null idServidor do servidor
     * @param string $titulo     null O título do relatório 
     * @param string $cabecalho  true Se exibirá o início do relatório (menu, cabecalho, etc) 
     */
    public static function listaDadosServidorRelatorio($idServidor, $titulo = null, $subTitulo = null, $cabecalho = true) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        # Dados do Servidor
        $select = 'SELECT tbservidor.idFuncional,
                         tbpessoa.nome,
                         tbperfil.nome,
                         tbservidor.idServidor,
                         tbservidor.dtAdmissao,
                         tbservidor.idServidor,
                         tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa ON tbservidor.idPessoa = tbpessoa.idPessoa
                                       LEFT JOIN tbsituacao ON tbservidor.situacao = tbsituacao.idsituacao
                                       LEFT JOIN tbperfil ON tbservidor.idPerfil = tbperfil.idPerfil
                   WHERE idServidor = ' . $idServidor;

        $result = $pessoal->select($select);

        $relatorio = new Relatorio();
        $relatorio->set_titulo($titulo);
        $relatorio->set_subtitulo($subTitulo);
        $relatorio->set_label(["Id", "Servidor", "Perfil", "Cargo - Área - Função (Comissão)", "Admissão", "Lotação", "Situação"]);
        $relatorio->set_funcao([null, null, null, null, "date_to_php"]);
        $relatorio->set_classe([null, null, null, "pessoal", null, "pessoal", "pessoal"]);
        $relatorio->set_metodo([null, null, null, "get_cargo", null, "get_Lotacao", "get_Situacao"]);
        $relatorio->set_align(['center']);
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_linhaNomeColuna(false);
        $relatorio->set_brHr(0);
        $relatorio->set_linhaFinal(true);
        $relatorio->set_log(false);

        # Verifica se exibe ou não o início do cabeçalho
        # Utilizado para quando os dados doservidor é a primeira coisa a ser
        # exibida no relatório. Se não for esconde o cabeçalho, menu etc
        if (!$cabecalho) {
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
        }

        $relatorio->show();
    }

######################################################################################################################

    /**
     * método rodape
     * Exibe oo rodapé
     * 
     * @param    string $idUsuario -> Usuário logado
     */
    public static function rodape($idUsuario, $idServidor = null, $idPessoa = null) {

        $grid = new Grid();
        $grid->abreColuna(12);

        # Exibe faixa azul
        titulo();
        $grid->fechaColuna();
        $grid->fechaGrid();

        # Exibe a versão do sistema
        $intra = new Intra();
        $grid = new Grid();
        $grid->abreColuna(4);
        p('Usuário : ' . $intra->get_usuario($idUsuario), 'usuarioLogado');

        $grid->fechaColuna();
        $grid->abreColuna(4);

        # Exibe o idServidor somente para o administrador
        if (Verifica::acesso($idUsuario, 1)) {
            if (!empty($idServidor)) {
                # Conecta com o banco de dados
                $pessoal = new Pessoal();

                $idPessoa = $pessoal->get_idPessoa($idServidor);
                p("idServidor: {$idServidor} / idPessoa:{$idPessoa}", "pidServidor");
            }
        }
        $grid->fechaColuna();
        $grid->abreColuna(4);
        #p("Desenvolvido por André Águia", 'pauthor');
        p("UENF - Universidade Estadual do Norte Fluminense Darcy Ribeiro", 'pauthor');
        $grid->fechaColuna();
        $grid->fechaGrid();
    }

    ###########################################################

    /**
     * Método exibe get_numServidoresCargoComissao
     * 
     * Exibe o número de servidores ativos por de cargo em comissão e o link para exibí-los
     * Usado na tabela da rotina de cadastro de cargo em comissão
     */
    public function get_numServidoresCargoComissao($id) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        echo $pessoal->get_servidoresCargoComissao($id);

        # Botão de exibição dos servidores
        $botao = new Link('', '?fase=vigente&id=' . $id);
        $botao->set_id('aServidorTipoCargo');
        $botao->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
        $botao->show();
    }

    ###########################################################

    /**
     * Método exibe get_numServidoresAtivosLotacao
     * 
     * Exibe o número de servidores ativos por lotação e o link para exibí-los
     * Usado na tabela da rotina de cadastro de lotação
     */
    public function get_numServidoresAtivosLotacao($idLotacao) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        echo $pessoal->get_numServidoresAtivosLotacao($idLotacao);

        # Botão de exibição dos servidores
        $botao = new Link('', '?fase=aguardeAtivos&id=' . $idLotacao);
        $botao->set_id('aServidorTipoCargo');
        $botao->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
        $botao->show();
    }

    ###########################################################

    /**
     * Método exibe get_numServidoresInativosLotacao
     * 
     * Exibe o número de servidores inativos por lotação e o link para exibí-los
     * Usado na tabela da rotina de cadastro de lotação
     */
    public function get_numServidoresInativosLotacao($idLotacao) {

        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        echo $pessoal->get_servidoresInativosLotacao($idLotacao);

        # Botão de exibição dos servidores
        $botao = new Link('', '?fase=aguardeInativos&id=' . $idLotacao);
        $botao->set_id('aServidorTipoCargo');
        $botao->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
        $botao->show();
    }

    ###########################################################

    /**
     * Método exibe get_numServidoresInativosConcurso
     * 
     * Exibe o número de servidores inativos por concurso e o link para exibí-los
     * Usado na tabela da rotina de cadastro de concurso
     */
    public function exibeMapaFuncao($idCargo) {

        # Mapa da Função
        $botaoMapa = new Link(null, "../grhRelatorios/mapaCargo.php?cargo={$idCargo}");
        $botaoMapa->set_imagem(PASTA_FIGURAS . 'lista.png', 20, 20);
        $botaoMapa->set_title("Exibe o mapa do Cargo/Função");
        $botaoMapa->set_target("_blank");
        $botaoMapa->show();
    }

    ###########################################################
}
