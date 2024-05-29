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
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));
    $parametroTipo = post('parametroTipo', get_session('parametroTipo', "Todos"));

    # Joga os parâmetros par as sessions
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

    $grid2 = new Grid();
    $grid2->abreColuna(12, 3);

    $aposentadoria->exibeMenu(8);

    # Exibe regras
    $permanente = new AposentadoriaCompulsoria();
    $permanente->exibe_tabelaRegras();

    $grid2->fechaColuna();
    $grid2->abreColuna(12, 9);

    #######################################3
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

            # Idade obrigatória
            $idade = $intra->get_variavel("aposentadoria.compulsoria.idade");

            # Formulário de Pesquisa
            $form = new Form('?');

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
            $select = "SELECT idFuncional,  
                              tbservidor.idServidor,
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
            $titulo = "Previsão de Aposentadoria Compulsória";

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['IdFuncional', 'Servidor', 'Lotação', 'Idade', "Compulsória em:", "Faltam<br/>(dias)"]);
            $tabela->set_align(['center', 'left']);
            $tabela->set_width([15, 40, 15, 10, 15]);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, "get_nomeECargo", "get_lotacao"]);
            $tabela->set_funcao([null, null, null, null, "date_to_php"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editar');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 3,
                    'valor' => '74',
                    'operador' => '>',
                    'id' => 'indeferido')
            ));
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        #######################################    

        case "editar" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria_previsaoCompulsoria.php');

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