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
    $aba = get('aba', 1);

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
    $parametroPerfil = post('parametroPerfil', get_session('parametroPerfil', 'Todos'));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', 66));
    $parametroEscolaridade = post('parametroEscolaridade', get_session('parametroEscolaridade', 'Todos'));
    $parametroCurso = post('parametroCurso', get_session('parametroCurso', 'Todos'));
    $parametroInstituicao = post('parametroInstituicao', get_session('parametroInstituicao', 'Todos'));
    $parametroAno = post('parametroAno', get_session('parametroAno', date("Y")));
    $parametroMarcador = post('parametroMarcador', get_session('parametroMarcador', 'Todos'));
    $parametroSituacao = post('parametroSituacao', get_session('parametroSituacao', 1));
    $parametroEscopo = post('parametroEscopo', get_session('parametroEscopo', 1));

    # Joga os parâmetros par as sessions   
    set_session('parametroNivel', $parametroNivel);
    set_session('parametroEscolaridade', $parametroEscolaridade);
    set_session('parametroCurso', $parametroCurso);
    set_session('parametroInstituicao', $parametroInstituicao);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroPerfil', $parametroPerfil);
    set_session('parametroAno', $parametroAno);
    set_session('parametroMarcador', $parametroMarcador);
    set_session('parametroSituacao', $parametroSituacao);
    set_session('parametroEscopo', $parametroEscopo);

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

            loadPage("?fase=exibeLista&aba={$aba}");
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

            $grid->fechaColuna();

            ##############

            $grid->abreColuna(9);

            # Formulário de Pesquisa
            $form = new Form('?');

            /*
             *  Marcador
             */
            # Pega os dados da datalist marcador
            $arrayMarcador = $formacao->get_arrayMarcadores();

            $controle = new Input('parametroMarcador', 'combo', 'Marcador:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Situação');
            $controle->set_array($arrayMarcador);
            $controle->set_valor($parametroMarcador);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            /*
             *  Lotação
             */
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
            array_unshift($result, array("Todos", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação do Servidor:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);

            /*
             * Curso
             */

            # Pega os dados da combo curso
            $curso = $pessoal->select('SELECT DISTINCT habilitacao, 
                                              habilitacao
                                         FROM tbformacao JOIN tbescolaridade USING (idEscolaridade)
                                     ORDER BY habilitacao');
            array_unshift($curso, array("Todos", "Todos"));

            $controle = new Input('parametroCurso', 'combo', 'Curso:', 1);
            $controle->set_size(200);
            $controle->set_title('Curso');
            $controle->set_valor($parametroCurso);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(6);
            $controle->set_array($curso);
            $form->add_item($controle);

            /*
             * Instituição
             */
            $instEnsino = $pessoal->select('SELECT DISTINCT instEnsino, 
                                                   instEnsino
                                              FROM tbformacao
                                             WHERE instEnsino <> ""
                                          ORDER BY instEnsino');
            array_unshift($instEnsino, array("Todos", "Todos"));

            $controle = new Input('parametroInstituicao', 'combo', 'Instituição:', 1);
            $controle->set_size(200);
            $controle->set_title('Instituiçlão de Ensino');
            $controle->set_valor($parametroInstituicao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(2);
            $controle->set_col(6);
            $controle->set_array($instEnsino);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();

            ##############
            # Faz os Selects com os cálculos
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbescolaridade.escolaridade,
                              idFormacao,
                              idFormacao,
                              idFormacao
                         FROM tbformacao LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbservidor USING (idPessoa)
                                              JOIN tbescolaridade USING (idEscolaridade)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND situacao = 1";

            if ($parametroCurso <> "Todos") {
                $select .= " AND tbformacao.habilitacao LIKE '%{$parametroCurso}%'";
            }

            if ($parametroInstituicao <> "Todos") {
                $select .= " AND tbformacao.instEnsino LIKE '%{$parametroInstituicao}%'";
            }

            if ($parametroMarcador <> "Todos") {

                $select .= " AND ("
                        . "tbformacao.marcador1 = {$parametroMarcador} OR "
                        . "tbformacao.marcador2 = {$parametroMarcador} OR "
                        . "tbformacao.marcador3 = {$parametroMarcador} OR "
                        . "tbformacao.marcador4 = {$parametroMarcador})";
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

            $result = $pessoal->select($select);

            # Altera o select para mostrar o número de servidores e não o de certificados
            $numero = $pessoal->count($select);

            # Exibe a tabela com o resumo
            $grid->abreColuna(3);

            # Pega o total de servidores desta lotação
            $total = $pessoal->get_numServidoresAtivos($parametroLotacao);

            $array = [
                ["COM PETEC", $numero],
                ["SEM PETEC", $total - $numero],
            ];

            $tabela = new Tabela();
            $tabela->set_conteudo($array);
            $tabela->set_titulo(null);
            $tabela->set_label(["Situação", " Quantidade"]);
            $tabela->set_width([80, 20]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_colunaSomatorio(1);
            $tabela->set_totalRegistro(false);
            $tabela->show();

            $grid->fechaColuna();

            ##############

            $grid->abreColuna(12);

            # Menu de Abas
            $tab = new Tab([
                "COM Petec",
                "SEM Petec"], $aba);

            ##############
            /*
             * COM PECTEC
             */

            $tab->abreConteudo();

            $tabela = new Tabela();
            $tabela->set_titulo('Servidores COM PETEC');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Servidor", "Nível do Curso", "Marcadores", "Curso", "Certificado"]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["center", "left", "center", "center", "left"]);
            $tabela->set_classe(['pessoal', "pessoal", null, "Formacao", "Formacao", "Formacao"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargoELotacaoEPerfilESituacao", null, "exibeMarcador", "exibeCurso", "exibeCertificado"]);

            if ($parametroSituacao == 1) {
                $tabela->set_rowspan(0);
                $tabela->set_grupoCorColuna(0);
            }

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor&aba=1');
            $tabela->show();

            $tab->fechaConteudo();

            /*
             * SEM PECTEC
             */

            $tab->abreConteudo();

            $novoArray = array();

            $select2 = "SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND situacao = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select2 .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select2 .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select2 .= " ORDER BY tbpessoa.nome";
            $result2 = $pessoal->select($select2);

            # Percorre o array
            foreach ($result2 as $item) {
                if (!$formacao->temPetec($item["idServidor"], $parametroMarcador)) {
                    $novoArray[] = [$item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"], $item["idServidor"]];
                }
            }

            $tabela = new Tabela();
            $tabela->set_titulo('Servidores SEM PETEC');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(["IdFuncional<br/>Matrícula", "Servidor", "Cargo", "Lotação", "Perfil","Editar"]);
            $tabela->set_conteudo($novoArray);
            $tabela->set_align(["center", "left", "left", "center", "left"]);
            $tabela->set_classe(['pessoal', "pessoal", "pessoal", "pessoal", "pessoal"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nome", "get_cargo", "get_lotacao", "get_perfil"]);

            if ($parametroSituacao == 1) {
                $tabela->set_rowspan(0);
                $tabela->set_grupoCorColuna(0);
            }

            # Botão Editar
            $botao = new Link(null, '?fase=editaServidor&aba=2&id=', 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, null, $botao]);
            $tabela->show();

            $tab->fechaConteudo();

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
            set_session('origem', "areaPetec.php?aba={$aba}");

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

            if ($parametroMarcador <> "Todos") {
                $select .= " AND (tbformacao.marcador1 = {$parametroMarcador} OR tbformacao.marcador2 = {$parametroMarcador} OR tbformacao.marcador3 = {$parametroMarcador} OR tbformacao.marcador4 = {$parametroMarcador})";
                $subTitulo .= "Filtro Marcador: {$formacao->get_marcador($parametroMarcador)}<br/>";
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
            #echo $select;
            # Monta o Relatório
            $relatorio = new Relatorio();
            $relatorio->set_titulo('Relatório Geral de Formação Servidores');

            if (!is_null($subTitulo)) {
                $relatorio->set_subtitulo($subTitulo);
            }

            $result = $pessoal->select($select);

            $relatorio->set_label(["Servidor", "Nível do Curso", "Curso"]);
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


