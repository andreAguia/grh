<?php

/**
 * Área de Penalidades
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
        $atividade = "Visualizou a área de penalidades";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

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
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            # Pega os dados
            $select = "(SELECT tbservidor.idfuncional,
                               tbservidor.idServidor,
                               data,
                              penalidade,
                              falta,
                              concat('p',idPenalidade),
                              descricao,
                               concat('p',tbservidor.idServidor),
                              idPenalidade,
                              tbpessoa.nome
                         FROM tbpenalidade JOIN tbtipopenalidade USING (idTipoPenalidade)
                                           JOIN tbservidor USING (idServidor)
                                           JOIN tbpessoa USING (idPessoa)
                       WHERE situacao = 1)
                       UNION
                       (SELECT tbservidor.idfuncional,
                               tbservidor.idServidor,
                               dtInicial,
                               'suspensão',
                               '-',
                               concat('s',idLicenca),
                               tblicenca.obs,
                               concat('s',tbservidor.idServidor),
                               idLicenca,
                               tbpessoa.nome
                          FROM tblicenca JOIN tbservidor USING (idServidor)
                                         JOIN tbpessoa USING (idPessoa)
                         WHERE idTpLicenca = 26 AND situacao = 1)                
                      ORDER BY 10, 3";
            #echo $select;

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo("Area de Penalidades");
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["IdFuncional", "Servidor", "Data", "Tipo", "Referente a Faltas?", "Processo / Publicação", "Descrição", "Editar"]);
            $tabela->set_width([10, 15, 10, 10, 10, 15, 25, 5]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["center", "left", "center", "center", "center", "center", "left"]);
            $tabela->set_classe([null, "pessoal", null, null, null, "Penalidade", null, "Penalidade"]);
            $tabela->set_metodo([null, "get_nomeECargoELotacao", null, null, null, "exibeProcessoPublicacaoGeral", null, "editarGeral"]);
            $tabela->set_funcao([null, null, "date_to_php"]);
            $tabela->set_rowspan([0, 1]);
            $tabela->set_grupoCorColuna(1);

//            $tabela->set_idCampo('idServidor');
//            $tabela->set_editar('?fase=editaServidor');
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
            set_session('origem', 'areaPenalidades.php');

            # Carrega a página específica
            loadPage('servidorPenalidades.php');
            break;

        ################################################################

        case "editaServidorSuspensao" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaPenalidades.php');

            # Carrega a página específica
            loadPage('servidorSuspensao.php');
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


    