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
    $licenca = new Licenca();

    # Verifica a fase do programa
    $fase = get('fase');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de licença médica";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroNomeMat = post('parametroNomeMat', get_session('parametroNomeMat'));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao'));
    $parametroAlta = post('parametroAlta', get_session('parametroAlta', 3));

    # Joga os parâmetros par as sessions    
    set_session('parametroNomeMat', $parametroNomeMat);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroAlta', $parametroAlta);

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

    # Cria um menu
    if ($fase <> "relatorio") {
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

        ################################################################
        # Formulário de Pesquisa
        $form = new Form('?');

        $controle = new Input('parametroNomeMat', 'texto', 'Nome, Matrícula ou id:', 1);
        $controle->set_size(100);
        $controle->set_title('Nome do servidor');
        $controle->set_valor($parametroNomeMat);
        $controle->set_autofocus(true);
        $controle->set_onChange('formPadrao.submit();');
        $controle->set_linha(1);
        $controle->set_col(3);
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

        $controle = new Input('parametroAlta', 'combo', 'Situação da Licença:', 1);
        $controle->set_size(30);
        $controle->set_title('Filtra por Alta');
        $controle->set_array([
            [1, "Última Licença Com Alta"],
            [2, "Última Licença Sem Alta - A Vencer"],
            [3, "Última Licença Sem Alta - Em Aberto"],
            [4, "Todas as Licenças"]]);
        $controle->set_valor($parametroAlta);
        $controle->set_onChange('formPadrao.submit();');
        $controle->set_linha(1);
        $controle->set_col(4);
        $form->add_item($controle);

        $form->show();
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

            loadPage('?fase=lista');
            break;

################################################################

        case "lista" :

            # Define as licenças consideradas
            $arrayLicencas = [1, 2, 30];

            # Pega os dados
            $select = "SELECT tbservidor.idServidor,
                              tblicenca.idLicenca,
                              tblicenca.idLicenca,
                              tblicenca.idTpLicenca,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tblicenca USING (idServidor)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

            if ($parametroAlta == 2 OR $parametroAlta == 3) {
                $select .= " AND tblicenca.dtInicial = (select max(dtInicial) from tblicenca where tblicenca.idServidor = tbservidor.idServidor AND (";

                $contador1 = count($arrayLicencas);
                foreach ($arrayLicencas as $item) {
                    $contador1--;
                    if ($contador1 > 0) {
                        $select .= "tblicenca.idTpLicenca = {$item} OR ";
                    } else {
                        $select .= "tblicenca.idTpLicenca = {$item}";
                    }
                }
                $select .= "))";
            }

            # Continua
            $select .= "AND situacao = 1
                        AND (";

            $contador2 = count($arrayLicencas);
            foreach ($arrayLicencas as $item) {
                $contador2--;
                if ($contador2 > 0) {
                    $select .= "tblicenca.idTpLicenca = {$item} OR ";
                } else {
                    $select .= "tblicenca.idTpLicenca = {$item}";
                }
            }

            # Continua
            $select .= ") AND idPerfil = 1";

            # Alta
            if ($parametroAlta == 2) {
                # Última licença sem alta a vencer
                $select .= " AND alta <> 1 
                             AND TIMESTAMPDIFF(DAY,CURDATE(),ADDDATE(dtInicial,numDias-1)) >= 0";
                $titulo = "Servidores Com a Última Licença Médica SEM ALTA - A VENCER";
                $mensagem1 = "Servidores devem se apresentar para um novo exame pericial até 5 (cinco) dias antes do término da licença anterior.";
            } elseif ($parametroAlta == 3) {
                # Última licença Sem Alta - Em Aberto
                $select .= " AND alta <> 1 
                             AND TIMESTAMPDIFF(DAY,CURDATE(),ADDDATE(dtInicial,numDias-1)) < 0";
                $titulo = "Servidores Com a Última Licença Médica <b>SEM ALTA - EM ABERTO</b>";
                $mensagem1 = "Servidores com a licença em aberto deverão se apresentar com <b>URGÊNCIA</b> para um novo exame pericial.";
            } elseif ($parametroAlta == 1) {
                # Última licença com Alta
                $select .= " AND alta = 1";
                $titulo = "Servidores Com a Última Licença Médica COM ALTA";
                $mensagem1 = "Servidores já devem estar em seus setores no dia imediatamente após ao término da licença. ";
            } elseif ($parametroAlta == 4) {
                # Todas as Licenças
                $titulo = "Todas as Licença Médicas";
                $mensagem1 = "Todas as licenças cadastradas.";
            }


            # Licenças consideradas
            $mensagem2 = null;
            foreach ($arrayLicencas as $item) {
                $mensagem2 .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- {$licenca->exibeNomeSimples($item)}<br/>";
            }

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

            $select .= "  ORDER BY ADDDATE(dtInicial,numDias-1)";

            if ($parametroAlta == "Com Alta") {
                $select .= " DESC";
            }

            #echo $select;
            # Guarde o select para o relatório
            set_session('selectRelatorio', $select);

            $resumo = $pessoal->select($select);

            $grid->fechaColuna();
            $grid->abreColuna(6);

            tituloTable("Procedimento:");
            callout($mensagem1);

            $grid->fechaColuna();
            $grid->abreColuna(6);

            tituloTable("As licenças consideradas são:");
            callout($mensagem2);

            $grid->fechaColuna();
            $grid->abreColuna(12);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(["Servidor", "Período", "Situação", "Tipo"]);
            $tabela->set_align(["left", "left", "center", "left"]);
            $tabela->set_classe(["pessoal", "Licenca", "Licenca", "Licenca"]);
            $tabela->set_metodo(["get_nomeECargoELotacao", "exibePeriodo", "analisaTermino", "exibeNomeSimples"]);
            $tabela->set_titulo($titulo);

            $tabela->set_editar('?fase=editaServidor&id=');
            $tabela->set_nomeColunaEditar("Acessar");
            $tabela->set_editarBotao("bullet_edit.png");
            $tabela->set_idCampo('idServidor');
            $tabela->show();
            break;

        ################################################################
        # Chama o menu do Servidor que se quer editar
        case "editaServidor" :

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaLicencaMedica.php');

            # Carrega a página específica
            loadPage('servidorMenu.php');
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

            # Alta
            if ($parametroAlta == "Sem Alta - A Vencer") {
                $titulo = "Servidores Com a Última Licença Médica<br/>Sem Alta - A Vencer";
            } elseif ($parametroAlta == "Sem Alta - Em Aberto") {
                $titulo = "Servidores Com a Última Licença Médica<br/>Sem Alta - Em Aberto";
            } else {
                $titulo = "Servidores Com a Última Licença Médica<br>Com Alta";
            }

            # Nome, MAtricula e id
            if (!is_null($parametroNomeMat)) {
                $subtitulo .= "Pesquisa: " . $parametroNomeMat;
            }

            $relatorio = new Relatorio();
            $relatorio->set_titulo($titulo);

            # Acrescenta o subtítulo de tiver filtro
            if ($subtitulo <> null) {
                $relatorio->set_subtitulo($subtitulo);
            }

            $relatorio->set_label(["Servidor", "Período", "Situação", "Tipo"]);
            $relatorio->set_align(["left", "left", "center", "left"]);
            $relatorio->set_width([30, 20, 30, 20]);
            $relatorio->set_classe(["pessoal", "Licenca", "Licenca", "Licenca"]);
            $relatorio->set_metodo(["get_nomeECargoELotacao", "exibePeriodo", "analisaTermino", "exibeNomeSimples"]);
            $relatorio->set_bordaInterna(true);

            $relatorio->set_conteudo($result);
            $relatorio->show();
            break;

        ################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


