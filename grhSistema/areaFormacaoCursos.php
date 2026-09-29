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
    $formacao = new Formacao();

    # Verifica a fase do programa
    $fase = get('fase');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de formação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros 
    $parametroCurso = post('parametroCurso', get_session('parametroCurso', 'Todos'));
    if ($parametroCurso == "Todos") {
        $parametroCurso = null;
    }

    # Joga os parâmetros par as sessions 
    set_session('parametroCurso', $parametroCurso);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

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

            ##############
            # Formulário de Pesquisa
            $form = new Form('?');

            /*
             * pesquisa por Curso
             */

            $controle = new Input('parametroCurso', 'texto', 'Curso:', 1);
            $controle->set_size(200);
            $controle->set_title('Curso');
            $controle->set_valor($parametroCurso);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $controle->set_autofocus(true);
            $form->add_item($controle);
            $form->show();

            ##############
            # Pega os dados
            $select = "SELECT DISTINCT habilitacao,
                                       habilitacao
                                  FROM tbformacao JOIN tbescolaridade USING (idEscolaridade)";

            if (!empty($parametroCurso)) {
                $select .= " AND habilitacao LIKE = '%{$parametroCurso}%'";
            }

            $select .= " ORDER BY habilitacao";

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Relação de Cursos');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["Curso", "Servidores"]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["left"]);
            $tabela->set_classe([null, "Formacao"]);
            $tabela->set_metodo([null, "get_numCertificados"]);
            $tabela->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


