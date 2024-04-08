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
    $fase = get('fase');

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

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);

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

    $grid2 = new Grid();
    $grid2->abreColuna(12, 3);

     $array = [
        ['Abono Permanencia', 'abono'],
        ['Aniversariantes', 'aniversariantes'],
        ['Afastamentos', 'afastamentos'],
        ['Aposentadoria e Tempo Averbado', 'aposentados'],
        ['Atestado', 'atestado'],
        ['Cargo Efetivo', 'cargoEfetivo'],
        ['Cargo em Comissão', 'cargoEmComissao'],
        ['Cedidos', 'cedidos'],
        ['Contatos & Endereços', 'contatos'],
        ['Dependentes & Auxílio Creche', 'dependentes'],
        ['Estatutários', 'estatutarios'],
        ['Etiquetas', 'etiquetas'],
        ['Férias', 'ferias'],
        ['Financeiro', 'financeiro'],
        ['Folha de Frequência', 'frequencia'],
        ['Geral - Servidores Ativos', 'geralAtivos'],
        ['Geral - Servidores Inativos', 'geralInativos'],
        ['Geral - Servidores Ativos e Inativos', 'geralGeral'],
        ['Licença Prêmio', 'licencaPremio'],
        ['Lotação', 'lotacao'],
        ['Movimentação de Pessoal', 'movimentacao'],
        ['Parentes', 'parentes'],
        ['Professores (Docentes)', 'professores'],
        ['Processo Eleitoral', 'eleitoral'],
        ['Seguro Anual', 'seguro'],
        ['Triênio', 'trienio'],
        ['Histórico', 'historico']
    ];

    # Menu de tipos de relatórios
    $menu = new Menu();
    $menu->add_item('titulo', 'Relatórios');

    foreach ($array as $item) {
        if ($fase == $item[1]) {
            $menu->add_item('link', "<b>| {$item[0]} |</b>", "?fase={$item[1]}");
        } else {
            $menu->add_item('link', $item[0], "?fase={$item[1]}", "?fase={$item[1]}");
        }
    }

    $menu->show();
    $grid->fechaColuna();

    $grid2->fechaColuna();
    $grid2->abreColuna(12, 9);

    #######################################

    switch ($fase) {
        case "":
            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=lista');
            break;

        #######################################

        case "lista" :

            # Formulário de Pesquisa
            $form = new Form('?');

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
            $aposentadoria->exibeAposentadosPorAno($parametroAno);
            break;

        #######################################    

        case "editar" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria_aposentadosPorAno.php');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;
    }

    $grid2->fechaColuna();
    $grid2->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}