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
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros    
    $parametroAno = post('parametroAno', get_session('parametroAno', 'Vigente'));

    # Joga os parâmetros par as sessions   
    set_session('parametroAno', $parametroAno);

    # Grava no log a atividade
    $atividade = "Visualizou os responsáveis pela Prestação de Contas em {$parametroAno}";
    $data = date("Y-m-d H:i:s");
    $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);

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
            $grid = new Grid();
            $grid->abreColuna(9);
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
            $botaoRel->set_url('?fase=relatorio');
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "left");

            $menu1->show();
            $grid->fechaColuna();
            ##############
            $grid->abreColuna(3);
            # Formulário de Pesquisa
            $form = new Form('?');

            # Cria um array com os anos possíveis
            $anoInicial = 1993;
            $anoAtual = date('Y');
            $anoExercicio = arrayPreenche($anoInicial, $anoAtual, "d");

            array_unshift($anoExercicio, 'Vigente');

            $controle = new Input('parametroAno', 'combo', 'Ano:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anoExercicio);
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            ##############
            $grid->abreColuna(12);
            # Pega os dados

            if ($parametroAno == 'Vigente') {
                $select = '(SELECT "Ordenador de Despesa Nato",
                               tbservidor.idFuncional,
                               tbpessoa.nome,
                               tbcomissao.dtNom,
                               tbcomissao.dtPublicNom,                               
                               "Reitor",
                               tbservidor.idServidor,
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
                               tbservidor.idServidor,
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
                               tbservidor.idServidor,
                               tbservidor.idServidor
                          FROM tbordenador LEFT JOIN tbservidor USING (idservidor)
                                           LEFT JOIN tbpessoa USING (idPessoa)                                          
                         WHERE CURRENT_DATE BETWEEN dtDesignacao AND dtTermino OR dtTermino is null)';

                #echo $select;

                $result = $pessoal->select($select);

                $tabela = new Tabela();
                $tabela->set_titulo('Responsáveis pela Prestação de Contas');
                $tabela->set_label(["Tipo", "IdFuncional", "Servidor", "Nomeação", "Publicação", "Detalhe", "Relatório"]);
                $tabela->set_conteudo($result);
                $tabela->set_align(["left", "center", "left", "center", "center", "left"]);
                $tabela->set_width([15, 10, 20, 10, 10, 25, 5]);

                $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php"]);
                $tabela->set_classe([null, null, null, null, null, null, "CadastroResponsavel"]);
                $tabela->set_metodo([null, null, null, null, null, null, "exibeAnexo"]);

                $tabela->set_idCampo('idServidor');
                $tabela->set_idCampo('idServidor');
                $tabela->set_editar('?fase=editaServidor');

                $tabela->set_rowspan(0);
                $tabela->set_grupoCorColuna(0);

                $tabela->show();
            } else {
                $select = "(SELECT 'Ordenador de Despesa Nato',
                               tbservidor.idFuncional,
                               tbpessoa.nome,
                               tbcomissao.dtNom,
                               tbcomissao.dtPublicNom,
                               tbcomissao.dtExo,
                               tbcomissao.dtPublicExo,    
                               'Reitor',
                               tbservidor.idServidor,
                               tbservidor.idServidor
                          FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                          LEFT JOIN tbcomissao USING (idservidor)
                                          LEFT JOIN tbtipocomissao USING (idTipoComissao)
                         WHERE year(tbcomissao.dtNom) <= '{$parametroAno}'
                           AND (tbcomissao.dtExo IS null OR year(tbcomissao.dtExo) >= '{$parametroAno}')
                           AND tbtipocomissao.idTipoComissao = 13) UNION                          
                       (SELECT 'Prestador de Contas Nato',
                               tbservidor.idFuncional,
                               tbpessoa.nome,
                               tbcomissao.dtNom,
                               tbcomissao.dtPublicNom,
                               tbcomissao.dtExo,
                               tbcomissao.dtPublicExo,    
                               tbdescricaocomissao.descricao,
                               tbservidor.idServidor,
                               tbservidor.idServidor
                          FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                          LEFT JOIN tbcomissao USING (idservidor)
                                          LEFT JOIN tbtipocomissao USING (idTipoComissao)
                                          LEFT JOIN tbdescricaocomissao USING (idDescricaoComissao)
                        WHERE year(tbcomissao.dtNom) <= '{$parametroAno}'
                           AND (tbcomissao.dtExo IS null OR year(tbcomissao.dtExo) >= '{$parametroAno}')
                           AND tbdescricaocomissao.prestadorNato IS TRUE) UNION
                       (SELECT 'Ordenador de Despesa Designado',
                               tbservidor.idFuncional,
                               tbpessoa.nome,
                               tbordenador.dtDesignacao,
                               tbordenador.dtPublicDesignacao,
                               tbordenador.dtTermino,
                               tbordenador.dtPublicTermino,    
                               descricao,
                               tbservidor.idServidor,
                               tbservidor.idServidor
                          FROM tbordenador LEFT JOIN tbservidor USING (idservidor)
                                           LEFT JOIN tbpessoa USING (idPessoa)    
                         WHERE year(dtDesignacao) <= '{$parametroAno}'
                           AND (dtTermino IS null OR year(dtTermino) >= '{$parametroAno}'))";

                #echo $select;

                $result = $pessoal->select($select);

                $tabela = new Tabela();
                $tabela->set_titulo("Responsáveis pela Prestação de Contas");
                $tabela->set_subtitulo("Em {$parametroAno}");
                $tabela->set_label(["Tipo", "IdFuncional", "Servidor", "Nomeação", "Publicação", "Exoneração", "Publicação", "Detalhe", "Relatório"]);
                $tabela->set_conteudo($result);
                $tabela->set_align(["left", "center", "left", "center", "center", "center", "center", "left"]);

                $tabela->set_funcao([null, null, null, "date_to_php", "date_to_php", "date_to_php", "date_to_php"]);
                $tabela->set_classe([null, null, null, null, null, null, null, null, "CadastroResponsavel"]);
                $tabela->set_metodo([null, null, null, null, null, null, null, null, "exibeAnexo"]);
                $tabela->set_width([15, 7, 20, 7, 7, 7, 7, 25, 5]);

                $tabela->set_idCampo('idServidor');
                $tabela->set_editar('?fase=editaServidor');

                $tabela->set_rowspan(0);
                $tabela->set_grupoCorColuna(0);

                $tabela->show();
            }

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

            if ($parametroAno == 'Vigente') {
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

                $relatorio = new Relatorio();
                $relatorio->set_titulo('Responsáveis pela Prestação de Contas');
                $relatorio->set_label(["Tipo", "IdFuncional", "Servidor", "Nomeação", "Publicação", "Detalhe"]);
                $relatorio->set_width([0, 15, 30, 10, 10, 30]);
                $relatorio->set_conteudo($result);
                $relatorio->set_align(["left", "center", "left", "center", "center", "left"]);
                $relatorio->set_funcao([null, null, null, "date_to_php", "date_to_php"]);
                $relatorio->set_numGrupo(0);
                $relatorio->show();
            } else {
                $select = "(SELECT 'Ordenador de Despesa Nato',
                               tbservidor.idFuncional,
                               tbpessoa.nome,
                               tbcomissao.dtNom,
                               tbcomissao.dtPublicNom,
                               tbcomissao.dtExo,
                               tbcomissao.dtPublicExo,    
                               'Reitor',
                               tbservidor.idServidor
                          FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                          LEFT JOIN tbcomissao USING (idservidor)
                                          LEFT JOIN tbtipocomissao USING (idTipoComissao)
                         WHERE year(tbcomissao.dtNom) <= '{$parametroAno}'
                           AND (tbcomissao.dtExo IS null OR year(tbcomissao.dtExo) >= '{$parametroAno}')
                           AND tbtipocomissao.idTipoComissao = 13) UNION                          
                       (SELECT 'Prestador de Contas Nato',
                               tbservidor.idFuncional,
                               tbpessoa.nome,
                               tbcomissao.dtNom,
                               tbcomissao.dtPublicNom,
                               tbcomissao.dtExo,
                               tbcomissao.dtPublicExo,    
                               tbdescricaocomissao.descricao,
                               tbservidor.idServidor
                          FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                          LEFT JOIN tbcomissao USING (idservidor)
                                          LEFT JOIN tbtipocomissao USING (idTipoComissao)
                                          LEFT JOIN tbdescricaocomissao USING (idDescricaoComissao)
                        WHERE year(tbcomissao.dtNom) <= '{$parametroAno}'
                           AND (tbcomissao.dtExo IS null OR year(tbcomissao.dtExo) >= '{$parametroAno}')
                           AND tbdescricaocomissao.prestadorNato IS TRUE) UNION
                       (SELECT 'Ordenador de Despesa Designado',
                               tbservidor.idFuncional,
                               tbpessoa.nome,
                               tbordenador.dtDesignacao,
                               tbordenador.dtPublicDesignacao,
                               tbordenador.dtTermino,
                               tbordenador.dtPublicTermino,    
                               descricao,
                               tbservidor.idServidor
                          FROM tbordenador LEFT JOIN tbservidor USING (idservidor)
                                           LEFT JOIN tbpessoa USING (idPessoa)    
                         WHERE year(dtDesignacao) <= '{$parametroAno}'
                           AND (dtTermino IS null OR year(dtTermino) >= '{$parametroAno}'))";

                #echo $select;

                $result = $pessoal->select($select);

                $relatorio = new Relatorio();
                $relatorio->set_titulo("Responsáveis pela Prestação de Contas em {$parametroAno}");
                $relatorio->set_label(["Tipo", "IdFuncional", "Servidor", "Nomeação", "Publicação", "Exoneração", "Publicação", "Detalhe"]);
                $relatorio->set_conteudo($result);
                $relatorio->set_align(["left", "center", "left", "center", "center", "center", "center", "left"]);
                $relatorio->set_funcao([null, null, null, "date_to_php", "date_to_php", "date_to_php", "date_to_php"]);
                $relatorio->set_numGrupo(0);
                $relatorio->show();
            }
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


