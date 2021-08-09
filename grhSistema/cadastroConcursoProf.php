<?php

/**
 * Cadastro de Concursos professor
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Limpa as sessões
set_session('origem');

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {

    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de concurso administrativo";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Pega as variáveis
    $fase = get('fase', 'Publicacao');
    $idConcurso = get_session("idConcurso");
    $idServidorPesquisado = get('idServidorPesquisado');
    $parametroCargo = post('parametroCargo', get_session('parametroCargo', '*'));
    set_session('parametroCargo', $parametroCargo);
    $concurso = new Concurso($idConcurso);
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorioAtivos" AND $fase <> "relatorioInativos") {
        AreaServidor::cabecalho();
    }

    $grid = new Grid();
    $grid->abreColuna(12);

################################################################

    switch ($fase) {
        case "":
        case "aguardaClassificacao" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoProf.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            $grid->fechaColuna();

            #######################################################

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso,"Classificação");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            $grid->fechaColuna();

            #######################################################3

            $grid->abreColuna(9);

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=classificacao');
            break;

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "classificacao" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoAdm.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            $grid->fechaColuna();

            #######################################################
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso,"Classificação");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            $grid->fechaColuna();

            #######################################################3

            $grid->abreColuna(9);

            # Formulário
            $form = new Form('?fase=aguardaClassificacao');

            # cargos por nivel
            $result = $pessoal->select('SELECT cargo,
                                                cargo
                                           FROM tbtipocargo
                                          WHERE cargo <> "Professor Associado" 
                                            AND cargo <> "Professor Titular" 
                                       ORDER BY 2');

            # acrescenta todos
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroCargo', 'combo', 'Cargo - Área - Função:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            # Monta o select
            $select = "SELECT CONCAT(sigla,' - ',tbcargo.nome),
                              classificacaoConcurso,                                                           
                              idServidor,
                              idServidor,
                              idServidor,
                              idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)
                                         LEFT JOIN tbcargo USING (idCargo)
                                         LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                        WHERE idConcurso = {$idConcurso}";

            # cargo
            if ($parametroCargo <> "*") {
                if (is_numeric($parametroCargo)) {
                    $select .= ' AND (tbcargo.idcargo = "' . $parametroCargo . '")';
                    $titulo = $pessoal->get_nomeCompletoCargo($parametroCargo);
                } else { # senão é nivel do cargo
                    $select .= ' AND (tbtipocargo.cargo = "' . $parametroCargo . '")';
                    $titulo = $parametroCargo;
                }
            } else {
                $titulo = "Classificação Geral";
            }

            $select .= " ORDER BY tbtipocargo.idTipoCargo, tbcargo.nome, classificacaoConcurso";

            # Pega os dados
            $row = $pessoal->select($select);

            # tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Classificação - {$titulo}");
            $tabela->set_conteudo($row);
            $tabela->set_label(["Cargo", "Class.", "Servidor", "Publicações", "Vaga Ant. Ocupada por:", "Editar"]);
            $tabela->set_classe([null, null, "pessoal", "Concurso", "Concurso"]);
            $tabela->set_metodo([null, null, "get_nomeELotacaoEPerfilESituacao", "exibePublicacoesServidor", "exibeOcupanteAnterior"]);
            #$tabela->set_funcao([null, null, null, null, "date_to_php"]);
            $tabela->set_width(array(15, 6, 22, 25, 22, 5));
            $tabela->set_align(array("left", "center", "left", "left"));

            # Botão de exibição dos servidores com permissão a essa regra
            $botao = new Link(null, '?fase=editaServidor&idServidorPesquisado=', 'Edita o Servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);
            $tabela->set_link([null, null, null, null, null, $botao]);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $idServidorPesquisado);

            # Informa a origem
            set_session('origem', 'cadastroConcursoProf.php');

            # Carrega a página específica
            loadPage('servidorConcurso.php');
            break;

        ################################################################
        
        case "aguardaListaServidoresAtivos" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoProf.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            $grid->fechaColuna();

            #######################################################
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso,"Servidores Ativos");
            
            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            $grid->fechaColuna();

            #######################################################3

            $grid->abreColuna(9);

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=listaServidoresAtivos');
            break;

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "listaServidoresAtivos" :

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'cadastroConcursoProf.php?fase=listaServidoresAtivos');

            $vagaAdm = new VagaAdm();

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoProf.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorioAtivos");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            $grid->fechaColuna();

            #######################################################
            # Menu
            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);

            # menu
            $concurso->exibeMenu($idConcurso,"Servidores Ativos");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            $grid->fechaColuna();

            #######################################################

            $grid->abreColuna(9);

            # Formulário
            $form = new Form('?fase=aguardaListaServidoresAtivos');

            # cargos por nivel
            $result = $pessoal->select('SELECT cargo,
                                               cargo
                                          FROM tbtipocargo
                                         WHERE cargo = "Professor Associado" 
                                            OR cargo = "Professor Titular" 
                                      ORDER BY 2');

            # acrescenta todos
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroCargo', 'combo', 'Cargo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            # Lista de Servidores
            if ($parametroCargo <> "*") {
                $lista = new ListaServidores("{$parametroCargo} - Servidores Ativos");
                $lista->set_cargo($parametroCargo);
            }else{
                $lista = new ListaServidores("Servidores Ativos");
            }

            $lista->set_situacao(1);
            $lista->set_concurso($idConcurso);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################
        
        case "aguardaListaServidoresInativos" :

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoProf.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            $grid->fechaColuna();

            #######################################################
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);           

            # menu
            $concurso->exibeMenu($idConcurso,"Servidores Inativos");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            $grid->fechaColuna();

            #######################################################3

            $grid->abreColuna(9);

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=listaServidoresInativos');
            break;

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "listaServidoresInativos" :

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Informa a origem
            set_session('origem', 'cadastroConcursoProf.php?fase=listaServidoresInativos');

            $vagaAdm = new VagaAdm();

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "areaConcursoProf.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");

            # Relatório
            $imagem2 = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dos Servidores");
            $botaoRel->set_target("_blank");
            $botaoRel->set_url("?fase=relatorioInativos");
            $botaoRel->set_imagem($imagem2);
            $menu->add_link($botaoRel, "right");

            $menu->show();

            $grid->fechaColuna();

            #######################################################3
            # Menu

            $grid->abreColuna(3);

            # Exibe os dados do Concurso
            $concurso->exibeDadosConcurso($idConcurso, true);            

            # menu
            $concurso->exibeMenu($idConcurso,"Servidores Inativos");

            # Exibe os servidores deste concurso
            $concurso->exibeQuadroServidoresConcursoPorCargo($idConcurso);

            $grid->fechaColuna();

            #######################################################

            $grid->abreColuna(9);

            # Formulário
            $form = new Form('?fase=aguardaListaServidoresInativos');

            # cargos por nivel
            $result = $pessoal->select('SELECT cargo,
                                               cargo
                                          FROM tbtipocargo
                                         WHERE cargo = "Professor Associado" 
                                            OR cargo = "Professor Titular" 
                                      ORDER BY 2');

            # acrescenta todos
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroCargo', 'combo', 'Cargo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();
            
            # Lista de Servidores
            if ($parametroCargo <> "*") {
                $lista = new ListaServidores("{$parametroCargo} - Servidores Inativos");
                $lista->set_cargo($parametroCargo);
            }else{
                $lista = new ListaServidores("Servidores Inativos");
            }
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_concurso($idConcurso);
            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "relatorioAtivos" :
            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores Ativos');
            $lista->set_situacao(1);
            $lista->set_concurso($idConcurso);
            if ($parametroCargo <> "*") {
                $lista->set_cargo($parametroCargo);
            }
            $lista->showRelatorio();
            break;

        ################################################################

        case "relatorioInativos" :
            # Lista de Servidores Inativos
            $lista = new ListaServidores('Servidores Inativos');
            $lista->set_situacao(1);
            $lista->set_situacaoSinal("<>");
            $lista->set_concurso($idConcurso);
            if ($parametroCargo <> "*") {
                $lista->set_cargo($parametroCargo);
            }
            $lista->showRelatorio();
            break;

        ################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
