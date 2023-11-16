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
        $atividade = "Visualizou a área de formação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros    
    $parametroNivel = post('parametroNivel', get_session('parametroNivel', 'Todos'));
    $parametroPerfil = post('parametroPerfil', get_session('parametroPerfil', 1));
    #$parametroSituacao = post('parametroSituacao', get_session('parametroSituacao', 1));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', 66));
    $parametroEscolaridade = post('parametroEscolaridade', get_session('parametroEscolaridade', 'Todos'));
    $parametroCurso = post('parametroCurso', get_session('parametroCurso', 'Todos'));
    $parametroInstituicao = post('parametroInstituicao', get_session('parametroInstituicao', 'Todos'));
    $parametroAno = post('parametroAno', get_session('parametroAno'));

    if ($grh) {
        $parametroAno = 'Todos';
    }
    
    # Joga os parâmetros par as sessions   
    set_session('parametroNivel', $parametroNivel);
    set_session('parametroEscolaridade', $parametroEscolaridade);
    set_session('parametroCurso', $parametroCurso);
    set_session('parametroInstituicao', $parametroInstituicao);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroPerfil', $parametroPerfil);
    set_session('parametroSituacao', $parametroSituacao);
    set_session('parametroAno', $parametroAno);

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
            $botaoRel->set_url("?fase=relatorio");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");

            $menu1->show();

            ##############
            # Formulário de Pesquisa
            $form = new Form('?');

            # Nivel do Cargo    
            $controle = new Input('parametroNivel', 'combo', 'Nível do Cargo Efetivo:', 1);
            $controle->set_size(20);
            $controle->set_title('Nível do Cargo');
            $controle->set_valor($parametroNivel);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $controle->set_array(["Todos", "Doutorado", "Superior", "Médio", "Fundamental", "Elementar"]);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            # Perfil
            $result = $pessoal->select('SELECT idperfil,
                                       nome,
                                       tipo
                                  FROM tbperfil
                                 WHERE tipo <> "Outros"  
                              ORDER BY tipo, nome');

            array_unshift($result, array('Todos', 'Todos'));

            $controle = new Input('parametroPerfil', 'combo', 'Perfil:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Perfil');
            $controle->set_array($result);
            $controle->set_optgroup(true);
            $controle->set_valor($parametroPerfil);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);
//
//            # Situação
//            $result = $pessoal->select('SELECT idsituacao, situacao
//                                              FROM tbsituacao                                
//                                          ORDER BY 1');
//            array_unshift($result, array('Todos', 'Todos'));
//
//            $controle = new Input('parametroSituacao', 'combo', 'Situação:', 1);
//            $controle->set_size(30);
//            $controle->set_title('Filtra por Situação');
//            $controle->set_array($result);
//            $controle->set_valor($parametroSituacao);
//            $controle->set_onChange('formPadrao.submit();');
//            $controle->set_linha(1);
//            $controle->set_col(2);
//            $form->add_item($controle);
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
            $controle->set_col(7);
            $form->add_item($controle);

            # Pega os dados da combo ano
            $selectano = "SELECT DISTINCT anoTerm, 
                                          anoTerm
                                     FROM tbformacao LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbservidor USING (idPessoa)
                                         LEFT JOIN tbescolaridade USING (idEscolaridade)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)                                 
                                         LEFT JOIN tbcargo USING (idCargo)
                                         LEFT JOIN tbtipocargo USING (idTipoCargo)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

//            if ($parametroSituacao <> "Todos") {
//                $selectano .= " AND situacao = {$parametroSituacao}";
//            }
            $selectano .= " AND situacao = 1";

            if ($parametroPerfil <> "Todos") {
                $selectano .= " AND idPerfil = {$parametroPerfil}";
            }

            if ($parametroNivel <> "Todos") {
                $selectano .= " AND tbtipocargo.nivel = '{$parametroNivel}'";
            }

            if ($parametroEscolaridade <> "Todos") {
                $selectano .= " AND tbformacao.idEscolaridade = {$parametroEscolaridade}";
            }

            if ($parametroCurso <> "Todos") {
                $selectano .= " AND tbformacao.habilitacao LIKE '%{$parametroCurso}%'";
            }

            if ($parametroInstituicao <> "Todos") {
                $selectano .= " AND tbformacao.instEnsino LIKE '%{$parametroInstituicao}%'";
            }

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $selectano .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $selectano .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $selectano .= " ORDER BY anoTerm";

            $anoExercicio = $pessoal->select($selectano);
            array_unshift($anoExercicio, array("Todos", "Todos"));

            $controle = new Input('parametroAno', 'combo', 'Ano Término:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anoExercicio);
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(2);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            # Pega os dados da combo escolaridade
            $escolaridade = $pessoal->select('SELECT idEscolaridade, 
                                               escolaridade
                                          FROM tbescolaridade
                                      ORDER BY idEscolaridade');
            array_unshift($escolaridade, array("Todos", "Todos"));

            # Escolaridade do Servidor    
            $controle = new Input('parametroEscolaridade', 'combo', 'Formação:', 1);
            $controle->set_size(20);
            $controle->set_title('Escolaridade do Servidor');
            $controle->set_valor($parametroEscolaridade);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(2);
            $controle->set_array($escolaridade);
            $form->add_item($controle);

            # Pega os dados da combo curso
            $curso = $pessoal->select('SELECT DISTINCT habilitacao, 
                                              habilitacao
                                         FROM tbformacao
                                     ORDER BY habilitacao');
            array_unshift($curso, array("Todos", "Todos"));

            # Curso
            $controle = new Input('parametroCurso', 'combo', 'Curso:', 1);
            $controle->set_size(100);
            $controle->set_title('Curso');
            $controle->set_valor($parametroCurso);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(4);
            $controle->set_array($curso);
            $form->add_item($controle);

            # Pega os dados da combo instituição
            $instEnsino = $pessoal->select('SELECT DISTINCT instEnsino, 
                                              instEnsino
                                         FROM tbformacao
                                        WHERE instEnsino <> ""
                                     ORDER BY instEnsino');
            array_unshift($instEnsino, array("Todos", "Todos"));

            # Instituição
            $controle = new Input('parametroInstituicao', 'combo', 'Instituição:', 1);
            $controle->set_size(100);
            $controle->set_title('Instituiçlão de Ensino');
            $controle->set_valor($parametroInstituicao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(4);
            $controle->set_array($instEnsino);
            $form->add_item($controle);

            $form->show();

            ##############
            # Pega os dados
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbescolaridade.escolaridade,
                              idFormacao,
                              idFormacao
                         FROM tbformacao LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbservidor USING (idPessoa)
                                         LEFT JOIN tbescolaridade USING (idEscolaridade)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)                                 
                                         LEFT JOIN tbcargo USING (idCargo)
                                         LEFT JOIN tbtipocargo USING (idTipoCargo)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";
            
            $select .= " AND situacao = 1";

            if ($parametroPerfil <> "Todos") {
                $select .= " AND idPerfil = {$parametroPerfil}";
            }

            if ($parametroNivel <> "Todos") {
                $select .= " AND tbtipocargo.nivel = '{$parametroNivel}'";
            }

            if ($parametroEscolaridade <> "Todos") {
                $select .= " AND tbformacao.idEscolaridade = {$parametroEscolaridade}";
            }

            if ($parametroCurso <> "Todos") {
                $select .= " AND tbformacao.habilitacao LIKE '%{$parametroCurso}%'";
            }

            if ($parametroInstituicao <> "Todos") {
                $select .= " AND tbformacao.instEnsino LIKE '%{$parametroInstituicao}%'";
            }

            if ($parametroAno <> "Todos") {
                if (empty($parametroAno)) {
                    $select .= " AND tbformacao.anoTerm IS NULL";
                } else {
                    $select .= " AND tbformacao.anoTerm = '{$parametroAno}'";
                }
            }

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY tbpessoa.nome, tbformacao.anoTerm";
            #echo $select;

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Cadastro de Formação Servidores');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["IdFuncional/Matrícula", "Servidor", "Escolaridade", "Curso", "Certificado"]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["center", "left", "center", "left"]);
            $tabela->set_classe(['pessoal', "pessoal", null, "Formacao", "Formacao"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoELotacao", null, "exibeCurso", "exibeCertificado"]);
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
            set_session('origem', 'areaFormacao.php');

            # Carrega a página específica
            loadPage('servidorFormacao.php');
            break;

        ################################################################
        # Relatório
        case "relatorio" :

            $subTitulo = null;

            # Pega os dados
            $select = "SELECT tbservidor.idServidor,
                              tbescolaridade.escolaridade,
                              idFormacao
                         FROM tbformacao LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbservidor USING (idPessoa)
                                         LEFT JOIN tbescolaridade USING (idEscolaridade)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)                                 
                                         LEFT JOIN tbcargo USING (idCargo)
                                         LEFT JOIN tbtipocargo USING (idTipoCargo)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

            $select .= " AND situacao = 1";

            if ($parametroPerfil <> "Todos") {
                $select .= " AND idPerfil = {$parametroPerfil}";
                $subTitulo .= "Filtro Perfil: {$pessoal->get_perfilNome($parametroPerfil)}<br/>";
            }

            if ($parametroNivel <> "Todos") {
                $select .= " AND tbtipocargo.nivel = '{$parametroNivel}'";
                $subTitulo .= "Filtro Cargo Efetivo de Nível: {$parametroNivel}<br/>";
            }

            if ($parametroEscolaridade <> "Todos") {
                $select .= " AND tbformacao.idEscolaridade = {$parametroEscolaridade}";
                $subTitulo .= "Filtro Curso de Nível: {$pessoal->get_escolaridade($parametroEscolaridade)}<br/>";
            }

            if ($parametroCurso <> "Todos") {
                $select .= " AND tbformacao.habilitacao LIKE '%{$parametroCurso}%'";
                $subTitulo .= "Filtro Curso: {$parametroCurso}<br/>";
            }

            if ($parametroInstituicao <> "Todos") {
                $select .= " AND tbformacao.instEnsino LIKE '%{$parametroInstituicao}%'";
                $subTitulo .= "Filtro Instituição: {$parametroInstituicao}<br/>";
            }

            if ($parametroAno <> "Todos") {
                $select .= " AND tbformacao.anoTerm = '{$parametroAno}'";
                $subTitulo .= "Filtro Ano: {$parametroAno}<br/>";
            }

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                    $subTitulo .= "Filtro Lotação: {$pessoal->get_nomeLotacao($parametroLotacao)}<br/>";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                    $subTitulo .= "Filtro Lotação: {$parametroLotacao}<br/>";
                }
            }

            $select .= ' ORDER BY tbpessoa.nome, tbformacao.anoTerm';
            echo $select;

            # Monta o Relatório
            $relatorio = new Relatorio();
            $relatorio->set_titulo('Relatório Geral de Formação Servidores');

            if (!is_null($subTitulo)) {
                $relatorio->set_subtitulo($subTitulo);
            }

            $result = $pessoal->select($select);

            $relatorio->set_label(["Servidor", "Escolaridade", "Curso"]);
            $relatorio->set_conteudo($result);
            $relatorio->set_align(["left", "center", "left"]);
            $relatorio->set_classe(["pessoal", null, "Formacao"]);
            $relatorio->set_metodo(["get_nomeECargoELotacaoEId", null, "exibeCurso"]);
            #$relatorio->set_rowspan(0);
            $relatorio->set_bordaInterna(true);
            $relatorio->show();
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


