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
        $atividade = "Visualizou a área de processos do Sei";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros    
    $parametroDescricao = post('parametroDescricao', get_session('parametroDescricao'));
    $parametroNomeMat = post('parametroNomeMat', get_session('parametroNomeMat'));

    # Joga os parâmetros par as sessions   
    set_session('parametroDescricao', $parametroDescricao);
    set_session('parametroNomeMat', $parametroNomeMat);

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
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("?fase=relatorio");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            #$menu1->add_link($botaoRel, "right");

            $menu1->show();

            ##############
            # Formulário de Pesquisa
            $form = new Form('?');

            $controle = new Input('parametroNomeMat', 'texto', 'Nome, Matrícula ou id:', 1);
            $controle->set_size(100);
            $controle->set_title('Nome do servidor');
            $controle->set_valor($parametroNomeMat);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            $controle = new Input('parametroDescricao', 'texto', 'Descrição:', 1);
            $controle->set_size(100);
            $controle->set_title('Descrição');
            $controle->set_valor($parametroDescricao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            $form->show();

            ##############
            # Pega os dados
            $select = "SELECT tbservidor.idfuncional,
                              tbservidor.idServidor,
                              IF(tipo = 1, CONCAT('SEI-',numero), CONCAT('E-26/',numeroAntigo)),
                              descricao,
                              idSei
                         FROM tbsei LEFT JOIN tbservidor USING (idServidor)   
                                         JOIN tbpessoa USING (idPessoa)                                                                         
                        WHERE TRUE";

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

            # Descricao
            if (!is_null($parametroDescricao)) {
                $select .= ' AND tbsei.descricao LIKE "%' . $parametroDescricao . '%"';
            }

            $select .= " ORDER BY tbpessoa.nome";
            #echo $select;

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Cadastro de Processos Cadastrados no SEI');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["IdFuncional", "Servidor", "Processo", "Descrição"]);
            $tabela->set_width([10, 20, 20, 40]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["center", "left", "left", "left"]);
            $tabela->set_classe([null, "pessoal"]);
            $tabela->set_metodo([null, "get_nomeECargoELotacao"]);
            $tabela->set_rowspan([0, 1]);
            $tabela->set_grupoCorColuna(1);

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
            set_session('origem', 'areaProcessosSei.php');

            # Carrega a página específica
            loadPage('servidorSei.php');
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


