<?php

/**
 * Cadastro Tre
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados   
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Controle de folgas do TRE";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase');

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio da área de TRE
    $origem = get_session("origem");

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    ################################################################

    switch ($fase) {
        case "" :
            $grid = new Grid();
            $grid->abreColuna(12);

            # Pegas os valores
            $diasTrabalhados = $pessoal->get_treDiasTrabalhados($idServidorPesquisado);
            $folgasConcedidas = $pessoal->get_treFolgasConcedidas($idServidorPesquisado);
            $folgasFruidas = $pessoal->get_treFolgasFruidas($idServidorPesquisado);
            $folgasPendentes = $folgasConcedidas - $folgasFruidas;

            # botão de voltar da lista
            if ($origem == 'areaTre') {
                $voltar = 'areaTre.php';
            } else {
                $voltar = 'servidorMenu.php';
            }

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", $voltar);
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Dias Trabalhados
            $botao1 = new Link("Dias Trabalhados", "servidorTreAfastamento.php");
            $botao1->set_class('button');
            $botao1->set_title("Cadastro de Dias Trabalhados e Folgas Concedidas");
            $menu1->add_link($botao1, "right");

            # Folgas Fruídas
            $botao2 = new Link("Folgas Fruídas", "servidorTreFolga.php");
            $botao2->set_class('button');
            $botao2->set_title("Cadastro de Folgas Fruídas");
            $menu1->add_link($botao2, "right");

            $menu1->show();

            # Exibe os dados do Servidor
            get_DadosServidor($idServidorPesquisado);

            # Verifica se Folgas fruídas não são maiores que as concedidas
            if ($folgasFruidas > $folgasConcedidas) {
                callout('Servidor com mais folgas fruídas do Tre do que concedidas', 'warning');
            }

            $grid->fechaColuna();
            $grid->fechaGrid();

            # Área Latereal
            $grid = new Grid();

            # Resumo
            $grid->abreColuna(4);

            # Tabela
            $folgas = Array(
                Array('Dias Trabalhados', $diasTrabalhados),
                Array('Folgas Concedidas', $folgasConcedidas),
                Array('Folgas Fruídas', $folgasFruidas),
                Array('Folgas Pendentes', $folgasPendentes));

            $label = array("Descrição", "Dias");
            $width = array(70, 30);
            $align = array("left");


            $tabela = new Tabela("tabelaTre");
            $tabela->set_titulo('Resumo');
            $tabela->set_conteudo($folgas);
            $tabela->set_cabecalho($label, $width, $align);
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 0,
                    'valor' => 'Folgas Pendentes',
                    'operador' => '=',
                    'id' => 'trePendente')));

            $tabela->show();

            # Relatórios
            $menu = new Menu();
            $menu->add_item('titulo', 'Relatórios');
            $menu->add_item('linkWindow', 'Dias Trabalhados e Folgas Concedidas Geral', '../grhRelatorios/servidorTreAfastamento.php');
            $menu->add_item('linkWindow', 'Dias Trabalhados e Folgas Concedidas Por Ano', '../grhRelatorios/servidorTreAfastamentoPorAno.php');
            $menu->add_item('linkWindow', 'Folgas Fruídas Geral', '../grhRelatorios/servidorTreFolga.php');
            $menu->add_item('linkWindow', 'Folgas Fruídas Por Ano', '../grhRelatorios/servidorTreFolgaPorAno.php');
            $menu->show();

            $grid->fechaColuna();

            # Dias Trabalhados e Folgas Concedidas
            $grid->abreColuna(4);

            $select = 'SELECT data,
                                  ADDDATE(data,dias-1),
                                  dias,
                                  folgas
                             FROM tbtrabalhotre
                            WHERE idServidor=' . $idServidorPesquisado . '
                            ORDER BY data desc';

            $row = $pessoal->select($select);

            $tabela = new Tabela("tabelaTre");
            $tabela->set_titulo('Dias Trabalhados e Folgas Concedidas');
            $tabela->set_conteudo($row);
            $tabela->set_label(array("Início", "Término", "Dias", "Folgas Concedidas"));
            #$tabela->set_width(array(10,10,10,10,30,20));	
            $tabela->set_align(array('center'));
            $tabela->set_funcao(array("date_to_php", "date_to_php"));
            $tabela->show();
            $grid->fechaColuna();

            #  Folgas Fruídas
            $grid->abreColuna(4);

            $select = 'SELECT data,
                                  ADDDATE(data,dias-1),                                 
                                  dias,
                                  idFolga
                             FROM tbfolga
                            WHERE idServidor=' . $idServidorPesquisado . '
                         ORDER BY data desc';

            $row = $pessoal->select($select);

            $tabela = new Tabela("tabelaTre");
            $tabela->set_titulo('Folgas Fruídas');
            $tabela->set_conteudo($row);
            $tabela->set_label(array("Início", "Término", "Folgas Fruídas"));
            $tabela->set_width(array(30, 30, 30));
            $tabela->set_align(array("center"));
            $tabela->set_funcao(array("date_to_php", "date_to_php", null));
            $tabela->show();
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}