<?php

/**
 * Área de Prestação de Contas
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

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
        $atividade = "Visualizou a área de prestação de contas";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros    
    $parametroNome = post('parametroNome', get_session('parametroNome'));

    # Joga os parâmetros par as sessions   
    set_session('parametroNome', $parametroNome);

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
            $botaoRel->set_url('../grhRelatorios/parentes.geral.php');
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            #$menu1->add_link($botaoRel, "right");

            $menu1->show();

            ##############
            # Pega os dados
            $select = '(SELECT "Ordenador de Despesa Nato",
                               tbservidor.idFuncional,
                               tbpessoa.nome,
                               tbcomissao.dtNom,
                               tbcomissao.dtPublicNom,                               
                               "Reitor",
                               tbservidor.idServidor
                          FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                          LEFT JOIN tbcomissao USING (idservidor)
                                          LEFT JOIN tbtipocomissao USING (idTipoComissao)
                         WHERE (CURRENT_DATE BETWEEN dtNom AND dtExo OR dtExo is null)
                           AND tbtipocomissao.idTipoComissao = 13) UNION                          
                       (SELECT "Prestador de Contas Nato",
                               tbservidor.idFuncional,
                               tbpessoa.nome,
                               tbcomissao.dtNom,
                               tbcomissao.dtPublicNom,                               
                               tbdescricaocomissao.descricao,
                               tbservidor.idServidor
                          FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                          LEFT JOIN tbcomissao USING (idservidor)
                                          LEFT JOIN tbtipocomissao USING (idTipoComissao)
                                          LEFT JOIN tbdescricaocomissao USING (idDescricaoComissao)
                         WHERE (CURRENT_DATE BETWEEN dtNom AND dtExo OR dtExo is null)
                           AND tbdescricaocomissao.prestadorNato IS TRUE) UNION
                       (SELECT "Ordenador de Despesa Designado",
                               tbservidor.idFuncional,
                               tbpessoa.nome,
                               tbordenador.dtDesignacao,
                               tbordenador.dtPublicDesignacao,
                               descricao,
                               tbservidor.idServidor
                          FROM tbordenador LEFT JOIN tbservidor USING (idservidor)
                                           LEFT JOIN tbpessoa USING (idPessoa)                                          
                         WHERE CURRENT_DATE BETWEEN dtDesignacao AND dtTermino OR dtTermino is null)';

            #echo $select;

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Responsáveis pela Prestação de Contas');
            $tabela->set_label(array("Tipo","IdFuncional", "Servidor", "Nomeação", "Publicação",  "Detalhe"));
            $tabela->set_conteudo($result);
            $tabela->set_align(array("left","center", "left", "center", "center", "left"));
            $tabela->set_funcao(array(null,null, null, "date_to_php", "date_to_php"));
//            $tabela->set_classe(array(null,"Pessoal", null, "CargoComissao"));
//            $tabela->set_metodo(array(null,"get_nomeECargo", null, "exibeDadosNomeacao"));
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');

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
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaPrestacaoContas.php');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################
        # Relatório
        case "relatorio" :
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


