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
        $atividade = "Visualizou a área de licença prêmio";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    set_session('areaPremio', false);

    # Pega os parâmetros
    $parametroNomeMat = post('parametroNomeMat', get_session('parametroNomeMat'));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));
    $parametroProcesso = post('parametroProcesso', get_session('parametroProcesso'));
    $parametroSituacao = post('parametroSituacao', get_session('parametroSituacao', 1));
    $selectRelatorio = get_session('selectRelatorio');

    # Joga os parâmetros par as sessions    
    set_session('parametroNomeMat', $parametroNomeMat);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroProcesso', $parametroProcesso);
    set_session('parametroSituacao', $parametroSituacao);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "relatorio" AND $fase <> "relatorioPublicacao" AND $fase <> "relatorioDias") {
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
            $grid->abreColuna(6);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            $grid->fechaColuna();
            $grid->abreColuna(6);

            # Cria um menu
            $menu2 = new MenuBar();

            # Relatório de Dias
            $botaoDias = new Link("<p id='pBotaoRelatorio'>Relatório de</p>Dias", "?fase=relatorioDias");
            $botaoDias->set_class('button');
            $botaoDias->set_target("_blank");
            $botaoDias->set_title('Relatório informando somente os dias publicados fruidos e pendentes');
            $menu2->add_link($botaoDias, "right");

            # Relatório de Publicações
            $botaoPub = new Link("<p id='pBotaoRelatorio'>Relatório de</p>Publicações", "?fase=relatorioPublicacao");
            $botaoPub->set_class('button');
            $botaoPub->set_target("_blank");
            $botaoPub->set_title('Relatório informando somente as publicações');
            $menu2->add_link($botaoPub, "right");

            # Relatórios           
            $botaoRel = new Link("<p id='pBotaoRelatorio'>Relatório</p>Geral", "?fase=relatorio");
            $botaoRel->set_class('button');
            $botaoRel->set_target("_blank");
            $botaoRel->set_title("Relatório dessa pesquisa");
            $menu2->add_link($botaoRel, "right");

            $menu2->show();

            $grid->fechaColuna();
            $grid->abreColuna(12);

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
            $controle->set_col(4);
            $form->add_item($controle);

            # Situação
            $result = $pessoal->select('SELECT idsituacao, situacao
                                             FROM tbsituacao                                
                                         ORDER BY 1');
            array_unshift($result, array('*', '-- Todos --'));

            $controle = new Input('parametroSituacao', 'combo', 'Situação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Situação');
            $controle->set_array($result);
            $controle->set_valor($parametroSituacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            # Processo
            $controle = new Input('parametroProcesso', 'combo', 'Processo:', 1);
            $controle->set_size(30);
            $controle->set_title('Escolhe se tem ou não processo cadastrado');
            $controle->set_array(array("-- Todos --", "Cadastrado", "Em Branco"));
            $controle->set_valor($parametroProcesso);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            # Pega o time inicial
            $time_start = microtime(true);

            # Conecta com o banco de dados
            $servidor = new Pessoal();

            # Pega os dados
            $select = "SELECT tbservidor.idServidor, 
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbsituacao.situacao,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idsituacao)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";

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

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                if (is_numeric($parametroLotacao)) {
                    $select .= ' AND (tblotacao.idlotacao = "' . $parametroLotacao . '")';
                } else { # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
                }
            }

            # Processo
            switch ($parametroProcesso) {
                case "Cadastrado":
                    $select .= ' AND tbservidor.processoPremio IS NOT null';
                    break;

                case "Em Branco":
                    $select .= ' AND tbservidor.processoPremio IS null';
                    break;
            }

            # situação
            if (($parametroSituacao <> "*") AND ($parametroSituacao <> "")) {
                $select .= ' AND (tbservidor.situacao = "' . $parametroSituacao . '")';
            }

            $select .= "  ORDER BY tbpessoa.nome";

            # Guarde o select para o relatório
            set_session('selectRelatorio', $select);

            $resumo = $servidor->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(["Id / Matrícula", "Servidor", "Lotação", "Processo de<br/>Contagem", "Admissão", "Número de Dias<br/>Publ./ Fruídos / Disp.", "Número de Publicações<br/>Reais / Possíveis / Faltantes", "Situação"]);
            $tabela->set_align(["center", "left"]);
            #$tabela->set_width([5, 25, 8, 13, 18, 18, 8]);
            $tabela->set_funcao([null, null, null, null, null, "exibeDiasLicencaPremio", "exibeNumPublicacoesLicencaPremio"]);
            $tabela->set_classe(["pessoal", "pessoal", "pessoal", "LicencaPremio", "pessoal"]);
            $tabela->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargo", "get_lotacao", "get_numProcessoContagem", "get_dtAdmissao"]);
            $tabela->set_titulo("Licença Prêmio");

            if (!is_null($parametroNomeMat)) {
                $tabela->set_textoRessaltado($parametroNomeMat);
            }

            $tabela->set_editar('?fase=editaServidorPremio&id=');
            $tabela->set_nomeColunaEditar("Acessar");
            $tabela->set_editarBotao("bullet_edit.png");
            $tabela->set_idCampo('idServidor');
            $tabela->show();

            # Pega o time final
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            p(number_format($time, 4, '.', ',') . " segundos", "right", "f10");

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

################################################################
        # Chama o menu do Servidor que se quer editar
        case "editaServidorPremio" :
            set_session('idServidorPesquisado', $id);
            set_session('areaPremio', true);
            loadPage('servidorLicencaPremio.php');
            break;

################################################################
        # Relatório
        case "relatorio" :
            $result = $pessoal->select($selectRelatorio);

            # Inicia a variável do subtítulo
            $subtitulo = null;

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                $subtitulo = $pessoal->get_nomeLotacao($parametroLotacao) . "<br/>";
            }

            # Processo
            switch ($parametroProcesso) {
                case "Cadastrado":
                    $subtitulo .= "Processos Cadastrados<br/>";
                    break;

                case "Em Branco":
                    $subtitulo .= "Processos Em Branco<br/>";
                    break;
            }

            # Situação
            if (($parametroSituacao <> "*") AND ($parametroSituacao <> "")) {
                $subtitulo .= "Servidores " . $pessoal->get_nomeSituacao($parametroSituacao) . "s<br/>";
            }

            # Nome, MAtricula e id
            if (!is_null($parametroNomeMat)) {
                $subtitulo .= "Pesquisa: " . $parametroNomeMat;
            }

            $relatorio = new Relatorio();
            $relatorio->set_titulo('Relatório de Licença Prêmio');

            # Acrescenta o subtítulo de tiver filtro
            if ($subtitulo <> null) {
                $relatorio->set_subtitulo($subtitulo);
            }

            $relatorio->set_label(["Id / Matrícula", "Servidor", "Lotação", "Admissão", "Número de Dias<br/>Publ./ Fruídos / Disp.", "Número de Publicações<br/>Reais / Possíveis / Faltantes", "Situação"]);
            $relatorio->set_align(["center", "left"]);
            $relatorio->set_funcao([null, null, null, null, "exibeDiasLicencaPremio", "exibeNumPublicacoesLicencaPremio"]);
            $relatorio->set_classe(["pessoal", "pessoal", "pessoal", "pessoal"]);
            $relatorio->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargo", "get_lotacao", "get_dtAdmissao"]);
            $relatorio->set_bordaInterna(true);

            $relatorio->set_conteudo($result);
            $relatorio->show();
            break;

################################################################
        # Relatório
        case "relatorioPublicacao" :
            $result = $pessoal->select($selectRelatorio);

            # Inicia a variável do subtítulo
            $subtitulo = 'Número de Publicações<br/>';

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                $subtitulo .= $pessoal->get_nomeLotacao($parametroLotacao) . "<br/>";
            }

            # Processo
            switch ($parametroProcesso) {
                case "Cadastrado":
                    $subtitulo .= "Processos Cadastrados<br/>";
                    break;

                case "Em Branco":
                    $subtitulo .= "Processos Em Branco<br/>";
                    break;
            }

            # Situação
            if (($parametroSituacao <> "*") AND ($parametroSituacao <> "")) {
                $subtitulo .= "Servidores " . $pessoal->get_nomeSituacao($parametroSituacao) . "s<br/>";
            }

            # Nome, MAtricula e id
            if (!is_null($parametroNomeMat)) {
                $subtitulo .= "Pesquisa: " . $parametroNomeMat;
            }

            $relatorio = new Relatorio();
            $relatorio->set_titulo('Relatório de Licença Prêmio');

            # Acrescenta o subtítulo de tiver filtro
            if ($subtitulo <> null) {
                $relatorio->set_subtitulo($subtitulo);
            }

            $relatorio->set_label(["Id / Matrícula", "Servidor", "Lotação", "Admissão", "Número de Publicações<br/>Reais / Possíveis / Faltantes", "Situação"]);
            $relatorio->set_align(["center", "left"]);
            $relatorio->set_funcao([null, null, null, null, "exibeNumPublicacoesLicencaPremio"]);
            $relatorio->set_classe(["pessoal", "pessoal", "pessoal", "pessoal", null, "pessoal"]);
            $relatorio->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargo", "get_lotacao", "get_dtAdmissao", null, "get_situacao"]);
            $relatorio->set_bordaInterna(true);

            $relatorio->set_conteudo($result);
            $relatorio->show();
            break;

################################################################
        # Relatório
        case "relatorioDias" :
            $result = $pessoal->select($selectRelatorio);

            # Inicia a variável do subtítulo
            $subtitulo = 'Número de Dias Publicados, Fruídos e Disponíveis<br/>';

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                $subtitulo .= $pessoal->get_nomeLotacao($parametroLotacao) . "<br/>";
            }

            # Processo
            switch ($parametroProcesso) {
                case "Cadastrado":
                    $subtitulo .= "Processos Cadastrados<br/>";
                    break;

                case "Em Branco":
                    $subtitulo .= "Processos Em Branco<br/>";
                    break;
            }

            # Situação
            if (($parametroSituacao <> "*") AND ($parametroSituacao <> "")) {
                $subtitulo .= "Servidores " . $pessoal->get_nomeSituacao($parametroSituacao) . "s<br/>";
            }

            # Nome, Matricula e id
            if (!is_null($parametroNomeMat)) {
                $subtitulo .= "Pesquisa: " . $parametroNomeMat;
            }

            $relatorio = new Relatorio();
            $relatorio->set_titulo('Relatório de Licença Prêmio');

            # Acrescenta o subtítulo de tiver filtro
            if ($subtitulo <> null) {
                $relatorio->set_subtitulo($subtitulo);
            }

            $relatorio->set_label(["Id / Matrícula", "Servidor", "Lotação", "Admissão", "Número de Dias<br/>Publ./ Fruídos / Disp.", "Situação"]);
            $relatorio->set_align(["center", "left"]);
            $relatorio->set_funcao([null, null, null, null, "exibeDiasLicencaPremio"]);
            $relatorio->set_classe(["pessoal", "pessoal", "pessoal", "pessoal", null, "pessoal"]);
            $relatorio->set_metodo(["get_idFuncionalEMatricula", "get_nomeECargo", "get_lotacao", "get_dtAdmissao", null, "get_situacao"]);
            $relatorio->set_bordaInterna(true);

            $relatorio->set_conteudo($result);
            $relatorio->show();
            break;

################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


