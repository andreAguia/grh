<?php

/**
 * Área de Fotografia
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

    # Pega o id
    $idPessoa = get('idPessoa');
    $idServidor = $pessoal->get_idServidoridPessoa($idPessoa);

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de contatos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroNome = post('parametroNome', retiraAspas(get_session('parametroNome')));
    $parametroTipo = post('parametroTipo', retiraAspas(get_session('parametroTipo', "Todos")));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));

    # Joga os parâmetros par as sessions    
    set_session('parametroNome', $parametroNome);
    set_session('parametroTipo', $parametroTipo);
    set_session('parametroLotacao', $parametroLotacao);

    # Relatório
    $selectRelatorio = get_session("selectRelatorio");

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();
    }

    $grid = new Grid();
    $grid->abreColuna(12);

################################################################

    switch ($fase) {

        case "" :
        case "lista" :

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
            $menu1->add_link($botaoRel, "right");

            $menu1->show();

            ###
            # Formulário de Pesquisa
            $form = new Form('?');

            # Nome    
            $controle = new Input('parametroNome', 'texto', 'Nome:', 1);
            $controle->set_size(30);
            $controle->set_title('Pesquisa');
            $controle->set_valor($parametroNome);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_autofocus(true);
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
            $controle->set_col(5);
            $form->add_item($controle);

            $controle = new Input('parametroTipo', 'combo', 'Tipo:', 1);
            $controle->set_size(30);
            $controle->set_title('E-mail Institucional');
            $controle->set_array(["Todos", "Com E-mail Institucional", "Sem E-mail Institucional"]);
            $controle->set_valor($parametroTipo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            $form->show();

            ###
            # Pega o time inicial
            $time_start = microtime(true);

            # Pega os dados
            $select = "SELECT idServidor,
                              idServidor,
                              idServidor,
                              dtAdmissao,
                              emailUenf                              
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbperfil USING (idPerfil)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND situacao = 1
                          AND tbperfil.tipo <> 'Outros'";

            # Nome
            if (!is_null($parametroNome)) {

                # Verifica se tem espaços
                if (strpos($parametroNome, ' ') !== false) {
                    # Separa as palavras
                    $palavras = explode(' ', $parametroNome);

                    # Percorre as palavras
                    foreach ($palavras as $item) {
                        $select .= " AND (tbpessoa.nome LIKE '%{$item}%')";
                    }
                } else {
                    $select .= " AND (tbpessoa.nome LIKE '%{$parametroNome}%')";
                }
            }

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = '{$parametroLotacao}')";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            # Tipo
            if ($parametroTipo == "Com E-mail Institucional") {
                $select .= " AND emailUenf IS NOT NULL AND emailUenf <> ''";
            }

            if ($parametroTipo == "Sem E-mail Institucional") {
                $select .= " AND (emailUenf IS NULL OR emailUenf = '')";
            }

            $select .= " ORDER BY tbpessoa.nome asc";

            # Guarde o select para o relatório
            set_session('selectRelatorio', $select);

            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Área do E-mail Institucional");
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Servidor", "Lotação", "Admissão", "E-mail Institucional"]);
            $tabela->set_align(["center", "left", "center", "center", "left"]);
            $tabela->set_width([10, 30, 30, 10, 30]);
            $tabela->set_funcao([null, null, null, "date_to_php"]);
            $tabela->set_classe(["Pessoal", "Pessoal", "Pessoal"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoSimples", "get_lotacao"]);
            $tabela->set_conteudo($resumo);

            $tabela->set_editar('?fase=editaServidor&id=');
            $tabela->set_nomeColunaEditar("Acessar");
            $tabela->set_editarBotao("bullet_edit.png");
            $tabela->set_idCampo('idServidor');
            $tabela->show();

            # Pega o time final
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            p(number_format($time, 4, '.', ',') . " segundos", "right", "f10");
            break;

        ################################################################
        # Chama o menu do Servidor que se quer editar
        case "editaServidor" :

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaEmailInstitucional.php');

            # Carrega a página específica
            loadPage('servidorEnderecoContatos.php');
            break;

        ################################################################
        # Relatório
        case "relatorio" :
            $result = $pessoal->select($selectRelatorio);

            # Inicia a variável do subtítulo
            $subtitulo = null;

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                $subtitulo = $pessoal->get_nomeLotacao2($parametroLotacao) . "<br/>";
            }

            # Tipo
            if ($parametroTipo == "Com E-mail Institucional") {
                $titulo = "Servidores COM E-mail Institucional Cadastrado";
            } elseif ($parametroTipo == "Sem E-mail Institucional") {
                $titulo = "Servidores SEM E-mail Institucional Cadastrado";
            } else {
                $titulo = "Servidores Ativos";
            }

            # Nome, Matricula e id
            if (!is_null($parametroNome)) {
                $subtitulo .= "Pesquisa: " . $parametroNome;
            }

            $relatorio = new Relatorio();
            $relatorio->set_titulo($titulo);

            # Acrescenta o subtítulo de tiver filtro
            if ($subtitulo <> null) {
                $relatorio->set_subtitulo($subtitulo);
            }

            $relatorio->set_label(["IdFuncional<br/>Matrícula", "Servidor", "Lotação", "Admissão", "E-mail Institucional"]);
            $relatorio->set_align(["center", "left", "center", "center", "left"]);
            $relatorio->set_width([10, 30, 30, 10, 30]);
            $relatorio->set_funcao([null, null, null, "date_to_php"]);
            $relatorio->set_classe(["Pessoal", "Pessoal", "Pessoal"]);
            $relatorio->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoSimples", "get_lotacao"]);
            $relatorio->set_bordaInterna(true);

            $relatorio->set_conteudo($result);
            $relatorio->show();
            break;

        ############################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


    