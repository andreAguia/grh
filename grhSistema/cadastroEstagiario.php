<?php

/**
 * Cadastro de Servidores
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
    $intra = new Intra();
    $pessoal = new Pessoal();

    ########
    # Verifica a fase do programa
    $fase = get('fase');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de estagiários e bolsistas";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroNomeMat = retiraAspas(post('parametroNomeMat', get_session('parametroNomeMat')));
    $parametroCpf = retiraAspas(post('parametroCpf', get_session('parametroCpf')));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', '*'));
    $parametroPerfil = post('parametroPerfil', get_session('parametroPerfil', '*'));
    $parametroSituacao = post('parametroSituacao', get_session('parametroSituacao', 1));

    # Agrupamento do Relatório
    $agrupamentoEscolhido = post('agrupamento', 0);

    # Session do Relatório
    $select = get_session('sessionSelect');
    $titulo = get_session('sessionTitulo');
    $subTitulo = get_session('sessionSubTitulo');

    # Joga os parâmetros par as sessions
    set_session('parametroNomeMat', $parametroNomeMat);
    set_session('parametroCpf', $parametroCpf);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroPerfil', $parametroPerfil);
    set_session('parametroSituacao', $parametroSituacao);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

    ################################################################

    switch ($fase) {
        # Lista os Servidores
        case "" :
            br(10);
            aguarde();
            br();
            loadPage('?fase=pesquisar');
            break;

        case "pesquisar" :
            # Cadastro de Servidores 
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $linkBotao1 = new Link("Voltar", "grh.php");
            $linkBotao1->set_class('button');
            $linkBotao1->set_title('Voltar a página anterior');
            $linkBotao1->set_accessKey('V');
            $menu1->add_link($linkBotao1, "left");

            # Novo Estagiário / Bolsista            
            if (Verifica::acesso($idUsuario, [1, 2])) {
                $linkBotao2 = new Link("Novo Estagiário / Bolsista", "estagiarioInclusao.php");
                $linkBotao2->set_class('button');
                $linkBotao2->set_title('Incluir Novo Estagiário ou Bolsista');
                $menu1->add_link($linkBotao2, "right");
            }

            $menu1->show();

            # Lista de Servidores Ativos
            $lista = new ListaServidores('Estagiários & Bolsistas');

            # Parâmetros
            $form = new Form('?');

            # Nome ou Matrícula
            $controle = new Input('parametroNomeMat', 'texto', 'Nome, Mat ou Id:', 1);
            $controle->set_size(55);
            $controle->set_title('Nome:');
            $controle->set_valor($parametroNomeMat);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            # CPF
            $controle = new Input('parametroCpf', 'cpf', 'Cpf:', 1);
            $controle->set_size(55);
            $controle->set_title("CPF do servidor:");
            $controle->set_valor($parametroCpf);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            # Situação
            $result = $pessoal->select('SELECT idsituacao, situacao
                                          FROM tbsituacao                                
                                      ORDER BY 1');
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroSituacao', 'combo', 'Situação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Situação');
            $controle->set_array($result);
            $controle->set_valor($parametroSituacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                           FROM tblotacao
                                          WHERE ativo) UNION (SELECT distinct DIR, DIR
                                           FROM tblotacao
                                          WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            # Perfil
            $result = $pessoal->select('SELECT idperfil,
                                       nome
                                  FROM tbperfil
                                 WHERE tipo = "Outros"  
                              ORDER BY tipo, nome');

            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroPerfil', 'combo', 'Perfil:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Perfil');
            $controle->set_array($result);
            $controle->set_valor($parametroPerfil);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();

            if (!empty($parametroNomeMat)) {
                if (Verifica::acesso($idUsuario, 1)) {
                    $lista->set_idServidorIdPessoa($parametroNomeMat);
                }

                $lista->set_matNomeId($parametroNomeMat);
            }

            if (!empty($parametroCpf)) {
                $lista->set_cpf($parametroCpf);
            }

            if ($parametroLotacao <> "*") {
                $lista->set_lotacao($parametroLotacao);
            }

            if ($parametroPerfil <> "*") {
                $lista->set_perfil($parametroPerfil);
            } else {
                # quando todos só exibe esse tipo de perfil
                $lista->set_tipoPerfil("Outros");
            }

            if ($parametroSituacao <> "*") {
                $lista->set_situacao($parametroSituacao);
            }

            # Do botão Editar
            $lista->set_caminho("?fase=editaServidor");

            $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ###############################

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'cadastroEstagiario.php');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ###############################
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}