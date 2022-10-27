<?php

/**
 * Área de Parente
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
        $atividade = "Visualizou a área de parentes";
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
            $menu1->add_link($botaoRel, "right");

            $menu1->show();

            ##############
            # Formulário de Pesquisa
            $form = new Form('?');

            # Nome do Parente
            $controle = new Input('parametroNome', 'texto', 'Nome do Parente', 1);
            $controle->set_size(55);
            $controle->set_title('Nome, matrícula ou ID:');
            $controle->set_valor($parametroNome);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            $form->show();

            ##############
            # Pega os dados
            $select = 'SELECT tbservidor.idServidor,
                              tbdependente.nome,
                              tbparentesco.Parentesco,
                              TIMESTAMPDIFF (YEAR,tbdependente.dtNasc,CURDATE()),
                              tbdependente.cpf,                             
                              dependente
                         FROM tbdependente JOIN tbpessoa USING (idPessoa)
                                           JOIN tbservidor USING (idPessoa)
                                           JOIN tbparentesco ON (tbdependente.parentesco = tbparentesco.idParentesco)
                       WHERE situacao = 1';

            if (!empty($parametroNome)) {
                $select .= ' AND tbdependente.nome LIKE "%' . $parametroNome . '%"';
            }

            $select .= ' ORDER BY tbpessoa.nome, tbdependente.nome';

            #echo $select;

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Cadastro de Parentes de Servidores');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["Servidor", "Parente", "Parentesco", "Idade", "CPF", "Dependente IR"]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["left", "left"]);
            $tabela->set_classe(["pessoal"]);
            $tabela->set_metodo(["get_nomeECargoELotacao"]);

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            if (!vazio($parametroNome)) {
                $tabela->set_textoRessaltado($parametroNome);
            }

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
            set_session('origem', 'areaParente.php');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################
        # Relatório
        case "relatorio" :

            $subTitulo = null;

            # Pega os dados
            $select = 'SELECT tbservidor.idfuncional,
                          tbpessoa.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbescolaridade.escolaridade,
                          idFormacao
                     FROM tbformacao JOIN tbpessoa USING (idPessoa)
                                     JOIN tbservidor USING (idPessoa)
                                     JOIN tbescolaridade USING (idEscolaridade)
                                     LEFT JOIN tbcargo USING (idCargo)
                                     LEFT JOIN tbtipocargo USING (idTipoCargo)
                     WHERE situacao = 1
                       AND idPerfil = 1';

            if ($parametroNivel <> "Todos") {
                $select .= ' AND tbtipocargo.nivel = "' . $parametroNivel . '"';
                $subTitulo .= 'Cargo Efetivo de Nível ' . $parametroNivel . '<br/>';
            }

            if ($parametroEscolaridade <> "*") {
                $select .= ' AND tbformacao.idEscolaridade = ' . $parametroEscolaridade;
                $subTitulo .= 'Curso de Nível ' . $pessoal->get_escolaridade($parametroEscolaridade) . '<br/>';
            }

            if (!vazio($parametroCurso)) {
                $select .= ' AND tbformacao.habilitacao like "%' . $parametroCurso . '%"';
                $subTitulo .= 'Filtro : ' . $parametroCurso . '<br/>';
            }

            $select .= ' ORDER BY tbpessoa.nome, tbformacao.anoTerm';

            # Monta o Relatório
            $relatorio = new Relatorio();
            $relatorio->set_titulo('Relatório Geral de Formação Servidores');

            if (!is_null($subTitulo)) {
                $relatorio->set_subtitulo($subTitulo);
            }

            $result = $pessoal->select($select);

            $relatorio->set_label(array("IdFuncional", "Nome", "Cargo", "Lotação", "Escolaridade", "Curso"));
            $relatorio->set_conteudo($result);
            $relatorio->set_align(array("center", "left", "left", "left", "left", "left"));
            $relatorio->set_classe(array(null, null, "pessoal", "pessoal", null, "Formacao"));
            $relatorio->set_metodo(array(null, null, "get_Cargo", "get_Lotacao", null, "get_curso"));
            $relatorio->show();
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


