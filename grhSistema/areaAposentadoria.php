<?php

/**
 * Área de Aposentadoria
 *
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();
    $aposentadoria = new Aposentadoria();

    # Verifica a fase do programa
    $fase = get('fase', 'aguardePorAno');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de aposentadoria";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', $aposentadoria->get_ultimoAnoAposentadoria()));
    $parametroMotivo = post('parametroMotivo', get_session('parametroMotivo', 3));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));
    $parametroTipo = post('parametroTipo', get_session('parametroTipo', "Todos"));

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);
    set_session('parametroMotivo', $parametroMotivo);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroTipo', $parametroTipo);

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    $menu = new MenuBar();

    # Voltar
    $botaoVoltar = new Link("Voltar", "grh.php");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Voltar a página anterior');
    $botaoVoltar->set_accessKey('V');
    $menu->add_link($botaoVoltar, "left");
    $menu->show();

    tituloTable("Área de Aposentadoria");
    br();

    $grid->fechaColuna();
    $grid->abreColuna(12, 3);

    $array = [
        ["titulo", "Servidores Aposentados", null],
        ["link", "Por Ano", "porAno"],
        ["link", "Por Tipo", "porTipo"],
        ["link", "Estatistica", "estatistica"],
        ["titulo", "Previsão", null],
        ["titulo1", "Regras Permanentes", null],
        ["link", "Voluntária", "voluntaria"],
        ["link", "Compulsória", "compulsoria"],
        ["link", "Compulsória Por Ano", "compulsoriaPorAno"],
        ["titulo1", "Regras de Transição", null],
        ["link", "Regra dos Pontos - Integral", "transicao1"],
        ["link", "Regra dos Pontos - Média", "transicao2"],
        ["link", "Regra do Pedágio - Integral", "transicao3"],
        ["link", "Regra do Pedágio - Média", "transicao4"],
        ["titulo1", "Direito Adquirido", null],
        ["link", "C.F. Art. 40, §1º, III, alínea a", "direitoAdquirido1"],
        ["link", "C.F. Art. 40, §1º, III, alínea b", "direitoAdquirido2"],
        ["link", "Art. 2º da EC Nº 41/2003", "direitoAdquirido3"],
    ];

    # Menu de tipos de relatórios
    $menu = new Menu("menuAposentadoria");

    $lista = null;

    foreach ($array as $item) {
        if ($fase == $item[2] OR $fase == "aguarde" . ucfirst($item[2])) {
            $menu->add_item($item[0], "<b>{$item[1]}</b>", (is_null($item[2]) ? null : "?fase=aguarde" . ucfirst($item[2])));
        } else {
            $menu->add_item($item[0], $item[1], (is_null($item[2]) ? null : "?fase=aguarde" . ucfirst($item[2])));
        }
    }
    
    $menu->add_item("titulo", "Documentação");

            # Pega os projetos cadastrados
            $select = 'SELECT idMenuDocumentos,
                          categoria,
                          texto,
                          title
                     FROM tbmenudocumentos
                     WHERE categoria = "Regras de Aposentadoria"
                  ORDER BY categoria, texto';

            $dados = $pessoal->select($select);
            $num = $pessoal->count($select);

            # Verifica se tem itens no menu
            if ($num > 0) {
                # Percorre o array 
                foreach ($dados as $valor) {

                    if (empty($valor["title"])) {
                        $title = $valor["texto"];
                    } else {
                        $title = $valor["title"];
                    }

                    # Verifica qual documento
                    $arquivoDocumento = PASTA_DOCUMENTOS . $valor["idMenuDocumentos"] . ".pdf";
                    if (file_exists($arquivoDocumento)) {
                        # Caso seja PDF abre uma janela com o pdf
                        $menu->add_item('linkWindow', $valor["texto"], PASTA_DOCUMENTOS . $valor["idMenuDocumentos"] . '.pdf', $title);
                    } else {
                        # Caso seja um .doc, somente faz o download
                        $menu->add_item('link', $valor["texto"], PASTA_DOCUMENTOS . $valor["idMenuDocumentos"] . '.doc', $title);
                    }
                }
            }

            $menu->add_item("linkWindow", "Regras Vigentes a partir de 01/01/2022", "https://www.rioprevidencia.rj.gov.br/PortalRP/Servicos/RegrasdeAposentadoria/apos2022/index.htm");
            $menu->add_item("linkWindow", "Regras Vigentes até 31/12/2021", "https://www.rioprevidencia.rj.gov.br/PortalRP/Servicos/RegrasdeAposentadoria/ate2021/index.htm");

    $menu->show();

    $grid->fechaColuna();
    $grid->abreColuna(12, 9);

    #######################################

    switch ($fase) {
        case "":
        case "aguardePorAno":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=porAno');
            break;

        #######################################

        case "porAno" :

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardePorAno');

            # Cria um array com os anos possíveis
            $select = 'SELECT DISTINCT YEAR(tbservidor.dtDemissao), YEAR(tbservidor.dtDemissao)
                         FROM tbservidor 
                        WHERE situacao = 2
                          AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                     ORDER BY 1 desc';

            $anos = $pessoal->select($select);

            $controle = new Input('parametroAno', 'combo');
            $controle->set_size(6);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anos);
            $controle->set_valor($parametroAno);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            # Exibe a lista
            $aposentadoria->exibeAposentadosPorAno($parametroAno, "editarPorAno");
            break;

        #######################################    

        case "editarPorAno" :

            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardePorAno');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################

        case "aguardePorTipo":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=porTipo');
            break;

        #######################################

        case "porTipo" :

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardePorTipo');

            # Cria um array com os tipo possíveis
            $selectMotivo = "SELECT DISTINCT idMotivo,
                                    tbmotivo.motivo
                               FROM tbmotivo JOIN tbservidor ON (tbservidor.motivo = tbmotivo.idMotivo)
                              WHERE situacao = 2
                                AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                           ORDER BY 2";

            $motivosPossiveis = $pessoal->select($selectMotivo);

            $controle = new Input('parametroMotivo', 'combo', null, 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Motivo');
            $controle->set_array($motivosPossiveis);
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroMotivo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            $form->show();

            # Exibe a lista
            $aposentadoria->exibeAposentadosPorTipo($parametroMotivo, "editarPorTipo");
            break;

        #######################################    

        case "editarPorTipo" :

            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardePorTipo');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################

        case "aguardeEstatistica":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=estatistica');
            break;

        #######################################

        case "estatistica" :

            #tituloTable("por Tipo de Aposentadoria");
            #br();

            $grid1 = new Grid();
            $grid1->abreColuna(6);

            # Monta o select
            $selectGrafico = 'SELECT tbmotivo.motivo, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                               WHERE tbservidor.situacao = 2
                               AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                            GROUP BY tbmotivo.motivo
                            ORDER BY 2 DESC ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            $chart = new Chart("Pie", $servidores);
            $chart->set_idDiv("cargo");
            $chart->set_legend(false);
            $chart->set_tamanho($largura = 400, $altura = 300);
            $chart->show();

            $grid1->fechaColuna();

            #####

            $grid1->abreColuna(6);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("por Tipo de Aposentadoria");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(["Aposentadoria", "Servidores"]);
            $tabela->set_width([80, 20]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_colunaSomatorio(1);
            $tabela->show();

            $grid1->fechaColuna();
            $grid1->fechaGrid();

            # Select
            $selectGrafico = 'SELECT YEAR(tbservidor.dtDemissao), count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                               WHERE tbservidor.situacao = 2
                            GROUP BY YEAR(tbservidor.dtDemissao)
                            ORDER BY 1 asc ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            tituloTable("por Ano da Aposentadoria");

            # Gráfico
            $chart = new Chart("ColumnChart", $servidores);
            $chart->set_idDiv("perfil");
            $chart->set_legend(false);
            $chart->set_label(array("Ano", "Nº de Servidores"));
            $chart->set_tamanho($largura = 900, $altura = 500);
            $chart->show();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_titulo("por Ano da Aposentadoria");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(["Ano", "Servidores"]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_rodape("Total de Servidores: " . $total);
            #$tabela->show();
            break;

        #######################################

        case "aguardeVoluntaria":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=voluntaria');
            break;

        #######################################

        case "voluntaria" :

            # Define a classe
            $classe = "AposentadoriaLC195Voluntaria";

            # Acessa a classe
            $aposentadoria1 = new $classe();

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardeVoluntaria');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            $controle = new Input('parametroTipo', 'combo', 'Tipo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Tipo');
            $controle->set_array(["Todos", "Já Podem requerer", "Ainda Não Podem Requerer", "Não Tem Direito"]);
            $controle->set_valor($parametroTipo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),                 
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY dtNasc";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Os que já podem requerer
            if ($parametroTipo == "Já Podem requerer") {

                # Inicia o array
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "0") {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Ainda Não Podem Requerer
            if ($parametroTipo == "Ainda Não Podem Requerer") {

                # Inicia a classe
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if (intval($aposentadoria1->getDiasFaltantes($item[0])) > 0) {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Não Tem Direito
            if ($parametroTipo == "Não Tem Direito") {

                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "Não Tem Direito") {
                        $lista[] = $item;                
                    }
                }
            }

            # Exibe a tabela
            $tabela = new Tabela();
            if ($parametroTipo == "Todos") {
                $tabela->set_conteudo($result);
            } else {
                $tabela->set_conteudo($lista);
            }
            $tabela->set_label(['IdFuncional<br/>Matrícula', 'Servidor', "Idade", "Aposenta em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left']);
            $tabela->set_width([15, 40, 15, 15, 15]);
            $tabela->set_titulo($aposentadoria1->get_descricao());
            $tabela->set_classe(["Pessoal", "Pessoal", null, $classe, $classe]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoELotacao", null, "getDataAposentadoria", "getDiasFaltantes"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarVoluntaria');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '=',
                    'id' => 'emAberto')
            ));
            $tabela->show();
            break;

        #######################################    

        case "editarVoluntaria" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardeVoluntaria');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################

        case "aguardeCompulsoria":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=compulsoria');
            break;

        #######################################

        case "compulsoria" :

            # Define a classe
            $classe = "AposentadoriaLC195Compulsoria";

            # Acessa a classe
            $aposentadoria1 = new $classe();

            # Idade obrigatória
            $idade = $intra->get_variavel("aposentadoria.compulsoria.idade");

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardeCompulsoria');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            $controle = new Input('parametroTipo', 'combo', 'Tipo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Tipo');
            $controle->set_array(["Todos", "Já Podem requerer", "Ainda Não Podem Requerer"]);
            $controle->set_valor($parametroTipo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT tbservidor.idServidor,  
                              tbservidor.idServidor,
                              TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),
                              ADDDATE(dtNasc, INTERVAL {$idade} YEAR),
                              TIMESTAMPDIFF(DAY,CURDATE(),ADDDATE(dtNasc, INTERVAL {$idade} YEAR))
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                     JOIN tbhistlot USING (idServidor)
                                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            # Os que já podem requerer
            if ($parametroTipo == "Já Podem requerer") {
                $select .= " AND TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()) >= {$idade}";
            }

            # Os que Ainda Não Podem Requerer
            if ($parametroTipo == "Ainda Não Podem Requerer") {
                $select .= " AND TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()) < {$idade}";
            }

            $select .= " ORDER BY dtNasc";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional<br/>Matrícula', 'Servidor', 'Idade', "Compulsória em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left']);
            $tabela->set_width([15, 40, 15, 15, 15]);
            $tabela->set_titulo($aposentadoria1->get_descricao());
            $tabela->set_classe(["Pessoal", "Pessoal"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoELotacao"]);
            $tabela->set_funcao([null, null, null, "date_to_php"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarCompulsoria');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 3,
                    'valor' => '74',
                    'operador' => '>',
                    'id' => 'pode')
            ));
            $tabela->show();
            break;

        #######################################    

        case "editarCompulsoria" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardeCompulsoria');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################

        case "aguardeCompulsoriaPorAno":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=compulsoriaPorAno');
            break;

        #######################################

        case "compulsoriaPorAno" :
            # Idade obrigatória
            $idade = $intra->get_variavel("aposentadoria.compulsoria.idade");

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardeCompulsoriaPorAno');

            # Cria um array com os anos possíveis
            $anos = arrayPreenche(date("Y") - 2, date("Y") + 20);

            $controle = new Input('parametroAno', 'combo');
            $controle->set_size(6);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anos);
            $controle->set_valor($parametroAno);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            # Exibe a lista
            $select = "SELECT month(dtNasc),  
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),
                              ADDDATE(dtNasc, INTERVAL {$idade} YEAR),
                              tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                        WHERE tbservidor.situacao = 1
                          AND idPerfil = 1
                          AND ({$parametroAno} - YEAR(tbpessoa.dtNasc) = {$idade})                    
                     ORDER BY dtNasc";

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($pessoal->select($select));
            $tabela->set_label(['Mês', 'Servidor', 'Lotação', "Idade", "Fará / Fez {$idade}"]);
            $tabela->set_align(['center', 'left', 'center', 'center', 'center']);
            $tabela->set_titulo("Previsão de Aposentadoria Compulsória Para o Ano de {$parametroAno}");
            $tabela->set_classe([null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, "get_nomeECargo", "get_lotacao"]);
            $tabela->set_funcao(["get_nomeMes", null, null, null, "date_to_php"]);
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarCompulsoriaPorAno');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 3,
                    'valor' => 74,
                    'operador' => '>',
                    'id' => 'pode')
            ));

            $tabela->show();
            break;

        #######################################

        case "editarCompulsoriaPorAno" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardeCompulsoriaPorAno');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################

        case "aguardeTransicao1":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=transicao1');
            break;

        #######################################

        case "transicao1" :

            # Define a classe
            $classe = "AposentadoriaTransicaoPontos1";

            # Acessa a classe
            $aposentadoria1 = new $classe();

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardeTransicao1');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            $controle = new Input('parametroTipo', 'combo', 'Tipo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Tipo');
            $controle->set_array(["Todos", "Já Podem requerer", "Ainda Não Podem Requerer", "Não Tem Direito"]);
            $controle->set_valor($parametroTipo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),                 
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY dtNasc";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Os que já podem requerer
            if ($parametroTipo == "Já Podem requerer") {

                # Inicia o array
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "0") {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Ainda Não Podem Requerer
            if ($parametroTipo == "Ainda Não Podem Requerer") {

                # Inicia a classe
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if (intval($aposentadoria1->getDiasFaltantes($item[0])) > 0) {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Não Tem Direito
            if ($parametroTipo == "Não Tem Direito") {

                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "Não Tem Direito") {
                        $lista[] = $item;                
                    }
                }
            }

            # Exibe a tabela
            $tabela = new Tabela();
            if ($parametroTipo == "Todos") {
                $tabela->set_conteudo($result);
            } else {
                $tabela->set_conteudo($lista);
            }
            $tabela->set_label(['IdFuncional<br/>Matrícula', 'Servidor', "Idade", "Aposenta em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left']);
            $tabela->set_width([15, 40, 15, 15, 15]);
            $tabela->set_titulo($aposentadoria1->get_descricao());
            $tabela->set_classe(["Pessoal", "Pessoal", null, $classe, $classe]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoELotacao", null, "getDataAposentadoria", "getDiasFaltantes"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarTransicao1');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '=',
                    'id' => 'pode'),
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '>',
                    'id' => 'normal'),
                array('coluna' => 4,
                    'valor' => 'Não Tem Direito',
                    'operador' => '=',
                    'id' => 'naoPode'),
            ));
            $tabela->show();
            break;

        #######################################    

        case "editarTransicao1" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardeTransicao1');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################

        case "aguardeTransicao2":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=transicao2');
            break;

        #######################################

        case "transicao2" :

            # Define a classe
            $classe = "AposentadoriaTransicaoPontos2";

            # Acessa a classe
            $aposentadoria1 = new $classe();

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardeTransicao2');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            $controle = new Input('parametroTipo', 'combo', 'Tipo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Tipo');
            $controle->set_array(["Todos", "Já Podem requerer", "Ainda Não Podem Requerer", "Não Tem Direito"]);
            $controle->set_valor($parametroTipo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),                 
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY dtNasc";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Os que já podem requerer
            if ($parametroTipo == "Já Podem requerer") {

                # Inicia o array
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "0") {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Ainda Não Podem Requerer
            if ($parametroTipo == "Ainda Não Podem Requerer") {

                # Inicia a classe
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if (intval($aposentadoria1->getDiasFaltantes($item[0])) > 0) {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Não Tem Direito
            if ($parametroTipo == "Não Tem Direito") {

                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "Não Tem Direito") {
                        $lista[] = $item;                
                    }
                }
            }
            
            # Exibe a tabela
            $tabela = new Tabela();
            if ($parametroTipo == "Todos") {
                $tabela->set_conteudo($result);
            } else {
                $tabela->set_conteudo($lista);
            }
            $tabela->set_label(['IdFuncional<br/>Matrícula', 'Servidor', "Idade", "Aposenta em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left']);
            $tabela->set_width([15, 40, 15, 15, 15]);
            $tabela->set_titulo($aposentadoria1->get_descricao());
            $tabela->set_classe(["Pessoal", "Pessoal", null, $classe, $classe]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoELotacao", null, "getDataAposentadoria", "getDiasFaltantes"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarTransicao2');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '=',
                    'id' => 'pode'),
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '>',
                    'id' => 'normal'),
                array('coluna' => 4,
                    'valor' => 'Não Tem Direito',
                    'operador' => '=',
                    'id' => 'naoPode'),
            ));
            $tabela->show();
            break;

        #######################################    

        case "editarTransicao2" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardeTransicao2');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################

        case "aguardeTransicao3":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=transicao3');
            break;

        #######################################

        case "transicao3" :

            # Define a classe
            $classe = "AposentadoriaTransicaoPedagio1";

            # Acessa a classe
            $aposentadoria1 = new $classe();

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardeTransicao3');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            $controle = new Input('parametroTipo', 'combo', 'Tipo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Tipo');
            $controle->set_array(["Todos", "Já Podem requerer", "Ainda Não Podem Requerer", "Não Tem Direito"]);
            $controle->set_valor($parametroTipo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),                 
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY dtNasc";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Os que já podem requerer
            if ($parametroTipo == "Já Podem requerer") {

                # Inicia o array
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "0") {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Ainda Não Podem Requerer
            if ($parametroTipo == "Ainda Não Podem Requerer") {

                # Inicia a classe
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if (intval($aposentadoria1->getDiasFaltantes($item[0])) > 0) {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Não Tem Direito
            if ($parametroTipo == "Não Tem Direito") {

                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "Não Tem Direito") {
                        $lista[] = $item;                
                    }
                }
            }
            
            # Exibe a tabela
            $tabela = new Tabela();
            if ($parametroTipo == "Todos") {
                $tabela->set_conteudo($result);
            } else {
                $tabela->set_conteudo($lista);
            }
            $tabela->set_label(['IdFuncional<br/>Matrícula', 'Servidor', "Idade", "Aposenta em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left']);
            $tabela->set_width([15, 40, 15, 15, 15]);
            $tabela->set_titulo($aposentadoria1->get_descricao());
            $tabela->set_classe(["Pessoal", "Pessoal", null, $classe, $classe]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoELotacao", null, "getDataAposentadoria", "getDiasFaltantes"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarTransicao3');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '=',
                    'id' => 'pode'),
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '>',
                    'id' => 'normal'),
                array('coluna' => 4,
                    'valor' => 'Não Tem Direito',
                    'operador' => '=',
                    'id' => 'naoPode'),
            ));
            $tabela->show();
            break;

        #######################################    

        case "editarTransicao3" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardeTransicao3');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################

        case "aguardeTransicao4":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=transicao4');
            break;

        #######################################

        case "transicao4" :

            # Define a classe
            $classe = "AposentadoriaTransicaoPedagio2";

            # Acessa a classe
            $aposentadoria1 = new $classe();

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardeTransicao4');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            $controle = new Input('parametroTipo', 'combo', 'Tipo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Tipo');
            $controle->set_array(["Todos", "Já Podem requerer", "Ainda Não Podem Requerer", "Não Tem Direito"]);
            $controle->set_valor($parametroTipo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),                 
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY dtNasc";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Os que já podem requerer
            if ($parametroTipo == "Já Podem requerer") {

                # Inicia o array
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "0") {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Ainda Não Podem Requerer
            if ($parametroTipo == "Ainda Não Podem Requerer") {

                # Inicia a classe
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if (intval($aposentadoria1->getDiasFaltantes($item[0])) > 0) {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Não Tem Direito
            if ($parametroTipo == "Não Tem Direito") {

                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "Não Tem Direito") {
                        $lista[] = $item;                
                    }
                }
            }
            
            # Exibe a tabela
            $tabela = new Tabela();
            if ($parametroTipo == "Todos") {
                $tabela->set_conteudo($result);
            } else {
                $tabela->set_conteudo($lista);
            }
            $tabela->set_label(['IdFuncional<br/>Matrícula', 'Servidor', "Idade", "Aposenta em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left']);
            $tabela->set_width([15, 40, 15, 15, 15]);
            $tabela->set_titulo($aposentadoria1->get_descricao());
            $tabela->set_classe(["Pessoal", "Pessoal", null, $classe, $classe]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoELotacao", null, "getDataAposentadoria", "getDiasFaltantes"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarTransicao4');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '=',
                    'id' => 'pode'),
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '>',
                    'id' => 'normal'),
                array('coluna' => 4,
                    'valor' => 'Não Tem Direito',
                    'operador' => '=',
                    'id' => 'naoPode'),
            ));
            $tabela->show();
            break;

        #######################################    

        case "editarTransicao4" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardeTransicao4');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################

        case "aguardeDireitoAdquirido1":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=direitoAdquirido1');
            break;

        #######################################

        case "direitoAdquirido1" :

            # Define a classe
            $classe = "AposentadoriaDireitoAdquirido1";

            # Acessa a classe
            $aposentadoria1 = new $classe();

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardeDireitoAdquirido1');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            $controle = new Input('parametroTipo', 'combo', 'Tipo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Tipo');
            $controle->set_array(["Todos", "Já Podem requerer", "Ainda Não Podem Requerer", "Não Tem Direito"]);
            $controle->set_valor($parametroTipo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),                 
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY dtNasc";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Os que já podem requerer
            if ($parametroTipo == "Já Podem requerer") {

                # Inicia o array
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "0") {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Ainda Não Podem Requerer
            if ($parametroTipo == "Ainda Não Podem Requerer") {

                # Inicia a classe
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if (intval($aposentadoria1->getDiasFaltantes($item[0])) > 0) {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Não Tem Direito
            if ($parametroTipo == "Não Tem Direito") {

                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "Não Tem Direito") {
                        $lista[] = $item;                
                    }
                }
            }

            # Exibe a tabela
            $tabela = new Tabela();
            if ($parametroTipo == "Todos") {
                $tabela->set_conteudo($result);
            } else {
                $tabela->set_conteudo($lista);
            }
            $tabela->set_label(['IdFuncional<br/>Matrícula', 'Servidor', "Idade", "Aposenta em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left']);
            $tabela->set_width([15, 40, 15, 15, 15]);
            $tabela->set_titulo($aposentadoria1->get_descricao());
            $tabela->set_classe(["Pessoal", "Pessoal", null, $classe, $classe]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoELotacao", null, "getDataAposentadoria", "getDiasFaltantes"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarDireitoAdquirido1');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '=',
                    'id' => 'pode'),
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '>',
                    'id' => 'normal'),
                array('coluna' => 4,
                    'valor' => 'Não Tem Direito',
                    'operador' => '=',
                    'id' => 'naoPode'),
            ));
            $tabela->show();
            break;

        #######################################    

        case "editarDireitoAdquirido1" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardeDireitoAdquirido1');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################        

        case "aguardeDireitoAdquirido2":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=direitoAdquirido2');
            break;

        #######################################

        case "direitoAdquirido2" :

            # Define a classe
            $classe = "AposentadoriaDireitoAdquirido2";

            # Acessa a classe
            $aposentadoria1 = new $classe();

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardeDireitoAdquirido2');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            $controle = new Input('parametroTipo', 'combo', 'Tipo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Tipo');
            $controle->set_array(["Todos", "Já Podem requerer", "Ainda Não Podem Requerer", "Não Tem Direito"]);
            $controle->set_valor($parametroTipo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),                 
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY dtNasc";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Os que já podem requerer
            if ($parametroTipo == "Já Podem requerer") {

                # Inicia o array
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "0") {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Ainda Não Podem Requerer
            if ($parametroTipo == "Ainda Não Podem Requerer") {

                # Inicia a classe
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if (intval($aposentadoria1->getDiasFaltantes($item[0])) > 0) {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Não Tem Direito
            if ($parametroTipo == "Não Tem Direito") {

                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "Não Tem Direito") {
                        $lista[] = $item;                
                    }
                }
            }
            
            # Exibe a tabela
            $tabela = new Tabela();
            if ($parametroTipo == "Todos") {
                $tabela->set_conteudo($result);
            } else {
                $tabela->set_conteudo($lista);
            }
            $tabela->set_label(['IdFuncional<br/>Matrícula', 'Servidor', "Idade", "Aposenta em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left']);
            $tabela->set_width([15, 40, 15, 15, 15]);
            $tabela->set_titulo($aposentadoria1->get_descricao());
            $tabela->set_classe(["Pessoal", "Pessoal", null, $classe, $classe]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoELotacao", null, "getDataAposentadoria", "getDiasFaltantes"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarDireitoAdquirido2');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '=',
                    'id' => 'pode'),
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '>',
                    'id' => 'normal'),
                array('coluna' => 4,
                    'valor' => 'Não Tem Direito',
                    'operador' => '=',
                    'id' => 'naoPode'),
            ));
            $tabela->show();
            break;

        #######################################    

        case "editarDireitoAdquirido2" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardeDireitoAdquirido2');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################        

        case "aguardeDireitoAdquirido3":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=direitoAdquirido3');
            break;

        #######################################

        case "direitoAdquirido3" :

            # Define a classe
            $classe = "AposentadoriaDireitoAdquirido3";

            # Acessa a classe
            $aposentadoria1 = new $classe();

            # Formulário de Pesquisa
            $form = new Form('?fase=aguardeDireitoAdquirido3');

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            $controle = new Input('parametroTipo', 'combo', 'Tipo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Tipo');
            $controle->set_array(["Todos", "Já Podem requerer", "Ainda Não Podem Requerer", "Não Tem Direito"]);
            $controle->set_valor($parametroTipo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);
            $form->show();

            # Exibe a lista
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),                 
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY dtNasc";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Os que já podem requerer
            if ($parametroTipo == "Já Podem requerer") {

                # Inicia o array
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "0") {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Ainda Não Podem Requerer
            if ($parametroTipo == "Ainda Não Podem Requerer") {

                # Inicia a classe
                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if (intval($aposentadoria1->getDiasFaltantes($item[0])) > 0) {
                        $lista[] = $item;
                    }
                }
            }

            # Os que Não Tem Direito
            if ($parametroTipo == "Não Tem Direito") {

                $lista = array();

                # percorre o array do banco de dados
                foreach ($result as $item) {
                    if ($aposentadoria1->getDiasFaltantes($item[0]) == "Não Tem Direito") {
                        $lista[] = $item;                
                    }
                }
            }

            # Exibe a tabela
            $tabela = new Tabela();
            if ($parametroTipo == "Todos") {
                $tabela->set_conteudo($result);
            } else {
                $tabela->set_conteudo($lista);
            }
            $tabela->set_label(['IdFuncional<br/>Matrícula', 'Servidor', "Idade", "Aposenta em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left']);
            $tabela->set_width([15, 40, 15, 15, 15]);
            $tabela->set_titulo($aposentadoria1->get_descricao());
            $tabela->set_classe(["Pessoal", "Pessoal", null, $classe, $classe]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoELotacao", null, "getDataAposentadoria", "getDiasFaltantes"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editarDireitoAdquirido3');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '=',
                    'id' => 'pode'),
                array('coluna' => 4,
                    'valor' => '0',
                    'operador' => '>',
                    'id' => 'normal'),
                array('coluna' => 4,
                    'valor' => 'Não Tem Direito',
                    'operador' => '=',
                    'id' => 'naoPode'),
            ));
            $tabela->show();
            break;

        #######################################    

        case "editarDireitoAdquirido3" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria.php?fase=aguardeDireitoAdquirido3');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        #######################################       
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}