<?php

/**
 * Área de Licença Prêmio
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
        $atividade = "Visualizou a área de TRE";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    set_session('areaPremio', false);

    # Pega os parâmetros
    $parametroNomeMat = post('parametroNomeMat', get_session('parametroNomeMat'));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao'));

    # Joga os parâmetros par as sessions    
    set_session('parametroNomeMat', $parametroNomeMat);
    set_session('parametroLotacao', $parametroLotacao);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
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
            
            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/treGeral.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");
            
            $menu1->show();
            
            ###
            # Formulário de Pesquisa
            $form = new Form('?');

            $controle = new Input('parametroNomeMat', 'texto', 'Nome, Matrícula ou id:', 1);
            $controle->set_size(100);
            $controle->set_title('Nome do servidor');
            $controle->set_valor($parametroNomeMat);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(6);
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
            $controle->set_col(6);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            $grid->abreColuna(3);

            ########################################
            # Exibe o Processo de férias            
            $classeFerias = new Ferias();
            $classeFerias->exibeProcesso($parametroLotacao);
            
            ########################################
            # Menu
            tituloTable("Menu");

            $menu = new Menu("menuProcedimentos");

            $menu->add_item('titulo', 'Relatórios da Pesquisa');                                  
            $menu->add_item('linkWindow', "Resumido","../grhRelatorios/treGeral.php");
            $menu->add_item('linkWindow', "Detalhado","../grhRelatorios/treDetalhado.php");
            
            $menu->add_item('titulo', 'Relatório Geral Anual');            
            $menu->add_item('linkWindow', "Anual de Dias Trabalhados","../grhRelatorios/treAfastamentoAnual.php");
            $menu->add_item('linkWindow', "Anual de Folgas Fruídas","../grhRelatorios/treFolgaAnual.php");

            $menu->show();

            #######################################
            # Área Principal
            $grid->fechaColuna();
            $grid->abreColuna(9);
            
            # Pega o time inicial
            $time_start = microtime(true);

            # Conecta com o banco de dados
            $servidor = new Pessoal();

            # Pega os dados
            $select = "SELECT idFuncional,
                              matricula,
                              tbpessoa.nome,
                              idServidor,
                              (SELECT IFnull(sum(dias),0) FROM tbtrabalhotre  WHERE tbtrabalhotre.idServidor = tbservidor.idServidor) as trabalhados,
                              (SELECT IFnull(sum(folgas),0) FROM tbtrabalhotre WHERE tbtrabalhotre.idServidor = tbservidor.idServidor) as concedidas,
                              (SELECT IFnull(sum(dias),0) FROM tbfolga WHERE tbfolga.idServidor = tbservidor.idServidor) as fruidas,
                              (SELECT IFnull(sum(folgas),0) FROM tbtrabalhotre WHERE tbtrabalhotre.idServidor = tbservidor.idServidor) - (SELECT IFnull(sum(dias),0) FROM tbfolga WHERE tbfolga.idServidor = tbservidor.idServidor)
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND situacao = 1";

            # Matrícula, nome ou id
            if (!is_null($parametroNomeMat)) {
                if (is_numeric($parametroNomeMat)) {
                    $select .= ' AND ((';
                } else {
                    $select .= ' AND (';
                }

                $select .= 'tbpessoa.nome LIKE "%' . $parametroNomeMat . '%")';

                if (is_numeric($parametroNomeMat)) {
                    $select .= ' OR (tbservidor.matricula LIKE "%' . $parametroNomeMat . '%")
                                 OR (tbservidor.idfuncional LIKE "%' . $parametroNomeMat . '%"))';
                }
            }

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                if (is_numeric($parametroLotacao)) {
                    $select .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
                } else { # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
                }
            }

            $select .= " AND (SELECT sum(dias) FROM tbtrabalhotre  WHERE tbtrabalhotre.idServidor = tbservidor.idServidor) > 0
                     ORDER BY tbpessoa.nome";

            # Guarde o select para o relatório
            set_session('selectRelatorio', $select);

            $resumo = $servidor->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(["Id", "Matricula", "Nome", "Lotação", "Dias Trabalhados", "Folgas Concedidas", "Folgas Fruidas", "Folgas Pendentes"]);
            $tabela->set_align(["center", "center", "left", "left"]);
            #$tabela->set_width(array(5,15,15,15,8,15,15,15));
            $tabela->set_funcao([null, "dv"]);
            $tabela->set_classe([null, null, null, "pessoal"]);
            $tabela->set_metodo([null, null, null, "get_lotacao"]);
            $tabela->set_titulo("TRE");

            if (!is_null($parametroNomeMat)) {
                $tabela->set_textoRessaltado($parametroNomeMat);
            }

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor&id=');
            $tabela->show();

            # Pega o time final
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            p(number_format($time, 4, '.', ',') . " segundos", "right", "f10");

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

################################################################
        # Chama o menu do Servidor que se quer editar
        case "editaServidor" :

            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaTre');

            # Carrega a página específica
            loadPage('servidorTre.php');
            break;

################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


