<?php

/**
 * Área de Progressão
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
        $atividade = "Visualizou a área de progressão";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));

    # Joga os parâmetros par as sessions   
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

            # Planos
            $botao = new Button("Planos", "exibeTabela.php");
            $botao->set_title("Exibe os planos de cargo e as tabalas\ salariais");
            $botao->set_target("_blank");
            $menu1->add_link($botao, "right");

            $menu1->show();

            ###
            # Formulário de Pesquisa
            $form = new Form('?');

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

            ###
            # Pega os dados
            $select = "SELECT tbservidor.idFuncional,
                              tbservidor.idServidor,
                              tbprogressao.dtInicial,
                              concat('R$ ',Valor,' - ',faixa) as classe,
                              tbplano.numdecreto,
                              idProgressao,
                              idProgressao,
                              tbservidor.idServidor
                         FROM tbprogressao LEFT JOIN tbservidor USING (idServidor)
                                                JOIN tbpessoa USING (idPessoa) 
                                                JOIN tbclasse USING (idClasse)
                                                JOIN tbplano USING (idPlano)
                                                JOIN tbhistlot USING (idServidor)
                                                JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                         WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND situacao = 1";

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = '{$parametroLotacao}')";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY tbpessoa.nome,  tbprogressao.dtInicial DESC";

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Verifica Problemas no Lançamento do Plano de Cargos na Progressão / Enquadramentos');
            $tabela->set_label(['IdFuncional', 'Servidor', 'Data Inicial', 'Salário Cadastrado', 'Plano Cadastrado', 'Plano Sugerido','Problemas ?']);
            $tabela->set_align(["center", "left"]);
            $tabela->set_funcao([null, null, "date_to_php"]);
            $tabela->set_classe([null, "pessoal", null, null, null,"Progressao","Progressao"]);
            $tabela->set_metodo([null, "get_nomeECargoELotacao", null, null, null,"get_planoSugerido","verificaProblemaPlano"]);
            $tabela->set_rowspan(1);
            $tabela->set_grupoCorColuna(1);
            $tabela->set_conteudo($result);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');
            $tabela->show();

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
            set_session('origem', 'areaProblemasProgressao.php');

            # Carrega a página específica
            loadPage('servidorProgressao.php');
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


