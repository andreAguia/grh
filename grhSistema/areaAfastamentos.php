<?php

/**
 * Área de Frequência
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

    # Verifica a fase do programa
    $fase = get('fase');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de afastamento";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', date('Y')));
    $parametroMes = post('parametroMes', get_session('parametroMes', date('m')));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao'));
    $parametroTipo = post('parametroTipo', get_session('parametroTipo'));
    $parametroCargo = post('parametroCargo', get_session('parametroCargo', '*'));

    # atribui lotação padrão quanfo vem de grh.php
    if ($grh) {
        $parametroLotacao = 66;
    }

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);
    set_session('parametroMes', $parametroMes);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroTipo', $parametroTipo);
    set_session('parametroCargo', $parametroCargo);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio" AND $fase <> "relatorio2") {
        AreaServidor::cabecalho();
    }

################################################################

    switch ($fase) {
        case "" :
            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=exibeLista');
            break;

################################################################

        case "exibeLista" :
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Calendário
            $botaoCalendario = new Link("Calendário", "calendario.php");
            $botaoCalendario->set_class('button');
            $botaoCalendario->set_title('Exibe o calendário');
            $botaoCalendario->set_target("_calenmdario");
            $menu1->add_link($botaoCalendario, "right");

            # Relatório Detalhado           
            $botaoRel = new Link("Relatório Detalhado", "?fase=relatorio");
            $botaoRel->set_class('button');
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_target("_blank");
            $menu1->add_link($botaoRel, "right");

            # Relatório Simples           
            $botaoRel2 = new Link("Relatório Simples", "?fase=relatorio2");
            $botaoRel2->set_class('button');
            $botaoRel2->set_title("Relatório simples dessa pesquisa");
            $botaoRel2->set_target("_blank");
            $menu1->add_link($botaoRel2, "right");

            $menu1->show();

            ################################################################
            # Formulário de Pesquisa
            $form = new Form('?');

            # Cria um array com os anos possíveis
            $anoInicial = 1999;
            $anoAtual = date('Y');
            $anoExercicio = arrayPreenche($anoInicial, $anoAtual, "d");

            $controle = new Input('parametroAno', 'combo', 'Ano:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anoExercicio);
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            # Mês
            array_unshift($mes, array('*', '-- Todos --'));
            $controle = new Input('parametroMes', 'combo', 'Mês:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra pelo Mês');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
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

            array_unshift($result, array(null, "Todos"));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            # Cargos
            $result1 = $pessoal->select('SELECT tbcargo.idCargo, 
                                                    concat(tbtipocargo.cargo," - ",tbarea.area," - ",tbcargo.nome) as cargo
                                              FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                                                           LEFT JOIN tbarea USING (idArea)    
                                      ORDER BY 2');

            # cargos por nivel
            $result2 = $pessoal->select('SELECT cargo,cargo FROM tbtipocargo WHERE cargo <> "Professor Associado" AND cargo <> "Professor Titular" ORDER BY 2');

            # junta os dois
            $result = array_merge($result2, $result1);

            # acrescenta Professor
            array_unshift($result, array('Professor', 'Professores'));

            # acrescenta todos
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroCargo', 'combo', 'Cargo - Área - Função:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(7);
            $form->add_item($controle);

            # Tipo do afastamento
            $result = $pessoal->select('SELECT idTpLicenca,nome FROM tbtipolicenca ORDER BY nome'); // Licenças gerais
            $result[] = array("ferias", "Ferias");
            $result[] = array("faltas", "Faltas Abonadas");
            $result[] = array("TTRE", "Trabalhando TRE");
            $result[] = array("FTRE", "Folga TRE");

            array_unshift($result, array(null, "Todos"));

            $controle = new Input('parametroTipo', 'combo', 'Tipo:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por tipo de afastamento.');
            $controle->set_array($result);
            $controle->set_valor($parametroTipo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(5);
            $form->add_item($controle);

            $form->show();

            ################################################################
            # Exibe a tabela de Servidores afastados
            $afast = new ListaAfastamentos();
            $afast->set_ano($parametroAno);
            if ($parametroMes <> "*") {
                $afast->set_mes($parametroMes);
            }
            $afast->set_tipo($parametroTipo);
            $afast->set_lotacao($parametroLotacao);
            if ($parametroCargo <> "*") {
                $afast->set_cargo($parametroCargo);
            }
            $afast->set_linkEditar('?fase=editaServidor');
            $afast->exibeTabela();
            #$afast->exibeGrafico();  // ainda não está pronto

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAfastamentos.php');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################
        # Relatório
        case "relatorio" :

            $afast = new ListaAfastamentos();
            $afast->set_ano($parametroAno);
            if ($parametroMes <> "*") {
                $afast->set_mes($parametroMes);
            }
            $afast->set_lotacao($parametroLotacao);
            if ($parametroCargo <> "*") {
                $afast->set_cargo($parametroCargo);
            }
            $afast->set_tipo($parametroTipo);
            $afast->set_linkEditar('?fase=editaServidor');
            $afast->exibeRelatorio();
            break;

        ################################################################
        # Relatório
        case "relatorio2" :

            $afast = new ListaAfastamentos();
            $afast->set_ano($parametroAno);
            if ($parametroMes <> "*") {
                $afast->set_mes($parametroMes);
            }
            $afast->set_lotacao($parametroLotacao);
            if ($parametroCargo <> "*") {
                $afast->set_cargo($parametroCargo);
            }
            $afast->set_tipo($parametroTipo);
            $afast->exibeDetalhes(false);
            $afast->exibeRelatorio();
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
