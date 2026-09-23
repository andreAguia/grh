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
    $parametroAlta = get('parametroAlta', get_session('parametroAlta', 3));

    # Joga os parâmetros par as sessions
    set_session('parametroAlta', $parametroAlta);

    # Licenças consideradas
    $arrayLicencas = [1, 30, 2];

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
    if ($fase <> "relatorio" AND $fase <> "procedimentos") {
        $menu1 = new MenuBar();

        # Voltar
        $botaoVoltar = new Link("Voltar", "grh.php");
        $botaoVoltar->set_class('button');
        $botaoVoltar->set_title('Voltar a página anterior');
        $botaoVoltar->set_accessKey('V');
        $menu1->add_link($botaoVoltar, "left");

        # Calendário
        $botaoCalendario = new Link("Calendário", "calendario.php");
        $botaoCalendario->set_class('button');
        $botaoCalendario->set_title('Exibe o calendário');
        $botaoCalendario->set_target("_calendario");
        $menu1->add_link($botaoCalendario, "right");

        # Procedimentos
        if (Verifica::acesso($idUsuario, 1)) {
            $botaoProcedimentos = new Link("Procedimentos", "?fase=procedimentos");
            $botaoProcedimentos->set_class('button');
            $botaoProcedimentos->set_title('Exibe os procedimentos');
            $botaoProcedimentos->set_target("_blank");
            $menu1->add_link($botaoProcedimentos, "right");
        }

        # Relatórios
        $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
        $botaoRel = new Button();
        $botaoRel->set_title("Relatório dessa pesquisa");
        $botaoRel->set_url("?fase=relatorio");
        $botaoRel->set_target("_blank");
        $botaoRel->set_imagem($imagem);
        #$menu1->add_link($botaoRel, "right");

        $menu1->show();
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

            # Área Lateral
            $grid->fechaColuna();
            $grid->abreColuna(3);

            ########################################
            # Menu

            $menu = new Menu("menuProcedimentos");
            $menu->add_item('titulo', 'Menu');
            if ($parametroAlta == 1) {
                $menu->add_item('link', '<b>Licença Com Alta - A Vencer</b>', '?parametroAlta=1');
            } else {
                $menu->add_item('link', 'Licença Com Alta - A Vencer', '?parametroAlta=1');
            }

            if ($parametroAlta == 2) {
                $menu->add_item('link', '<b>Licença Sem Alta - A Vencer</b>', '?parametroAlta=2');
            } else {
                $menu->add_item('link', 'Licença Sem Alta - A Vencer', '?parametroAlta=2');
            }

            if ($parametroAlta == 3) {
                $menu->add_item('link', '<b>Licença Sem Alta - Em Aberto</b>', '?parametroAlta=3');
            } else {
                $menu->add_item('link', 'Licença Sem Alta - Em Aberto', '?parametroAlta=3');
            }

            if ($parametroAlta == 4) {
                $menu->add_item('link', '<b>Somatório - Artigo 117</b>', '?parametroAlta=4');
            } else {
                $menu->add_item('link', 'Somatório - Artigo 117', '?parametroAlta=4');
            }

            $menu->show();

            $grid->fechaColuna();

            #################################

            $grid->abreColuna(9);

            if ($parametroAlta == 4) {
                # PAra o somatório de dias na licença 117
                $select = "SELECT DISTINCT tbservidor.idServidor,
                                           CASE
                                                WHEN SUM(numDias) >= 730 THEN 'Não Pode Mais Pedir Licença'
                                                WHEN SUM(numDias) >= 365 AND  SUM(numDias) < 730 THEN 'Redução de 1/3 do Salário'
                                                ELSE '---'
                                            END,
                                           SUM(numDias) as somatorio
                             FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                             JOIN tblicenca USING (idServidor)                                         
                        WHERE situacao = 1 
                          AND tblicenca.idTpLicenca = 2
                          GROUP BY tbservidor.idServidor
                          ORDER BY somatorio DESC";

                $resumo = $pessoal->select($select);

                # Monta a tabela
                $tabela = new Tabela();
                $tabela->set_titulo("Total de Dias Fruídos na Licença Médica Por Motivo de Doença em Pessoa da Família - Artigo 117");
                $tabela->set_subtitulo("Conforme o Artigo 119 do Estatuto do Servidro Público, Decreto 2479 de 08/03/1979,<br/>não é permitido tirar mais de 730 dias (2 anos) da Licença por Motivo de Doença em Pessoa da Família");
                $tabela->set_conteudo($resumo);
                $tabela->set_label(["Servidor", "Situação", "Total de Dias"]);
                $tabela->set_align(["left", "left", "center"]);
                $tabela->set_width([40, 40, 10]);
                $tabela->set_classe(["pessoal"]);
                $tabela->set_metodo(["get_nomeECargoELotacao"]);

                $tabela->set_editar('?fase=editaServidor&id=');
                $tabela->set_nomeColunaEditar("Acessar");
                $tabela->set_editarBotao("bullet_edit.png");
                $tabela->set_idCampo('idServidor');
                
                $tabela->set_formatacaoCondicional(array(
                array('coluna' => 1,
                    'valor' => 'Não Pode Mais Pedir Licença',
                    'operador' => '=',
                    'id' => 'licencaEmAberto'),
                array('coluna' => 1,
                    'valor' => 'Redução de 1/3 do Salário',
                    'operador' => '=',
                    'id' => 'reducaoSalario'),
                array('coluna' => 1,
                    'valor' => '---',
                    'operador' => '=',
                    'id' => 'licencaNormal'),
            ));
                
                $tabela->show();
            } else {
                # Para a relação de servidores com as licenças
                # Pega os dados
                $select = "SELECT tbservidor.idServidor,
                              tblicenca.idLicenca,
                              tblicenca.idLicenca,
                              tblicenca.idTpLicenca,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tblicenca USING (idServidor)                                         
                        WHERE situacao = 1";

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
                $select .= " AND (";

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

                $subtitulo = null;
                $titulo = null;

                # Alta
                if ($parametroAlta == 2) {
                    # Última licença sem alta a vencer
                    $select .= " AND alta <> 1 
                             AND TIMESTAMPDIFF(DAY,CURDATE(),ADDDATE(dtInicial,numDias-1)) >= 0";
                    $titulo = "Servidores Com a Última Licença Médica";
                    $subtitulo = "SEM ALTA - A VENCER";
                    $mensagem1 = "Servidores devem se apresentar para um novo exame pericial até 5 (cinco) dias antes do término da licença anterior.";
                } elseif ($parametroAlta == 3) {
                    # Última licença Sem Alta - Em Aberto
                    $select .= " AND alta <> 1 
                             AND TIMESTAMPDIFF(DAY,CURDATE(),ADDDATE(dtInicial,numDias-1)) < 0";
                    $titulo = "Servidores Com a Última Licença Médica";
                    $subtitulo = "<b>SEM ALTA - EM ABERTO</b>";
                    $mensagem1 = "Servidores com a licença em aberto deverão se apresentar com <b>URGÊNCIA</b> para um novo exame pericial.";
                } elseif ($parametroAlta == 1) {
                    # Última licença com Alta a vencer
                    $select .= " AND alta = 1
                             AND TIMESTAMPDIFF(DAY,CURDATE(),ADDDATE(dtInicial,numDias-1)) >= 0";
                    $titulo = "Servidores Com a Última Licença Médica";
                    $subtitulo = "COM ALTA - A VENCER";
                    $mensagem1 = "Servidores já devem estar em seus setores no dia imediatamente após ao término da licença. ";
                }

                $select .= "  ORDER BY ADDDATE(dtInicial,numDias-1)";

                if ($parametroAlta == "Com Alta") {
                    $select .= " DESC";
                }

                $resumo = $pessoal->select($select);

                # Guarde o select para o relatório
                set_session('selectRelatorio', $select);

                # Monta a tabela
                $tabela = new Tabela();
                $tabela->set_titulo($titulo);
                if (!empty($subtitulo)) {
                    $tabela->set_subtitulo("{$subtitulo}<br/>{$mensagem1}");
                }
                $tabela->set_conteudo($resumo);
                $tabela->set_label(["Servidor", "Período", "Situação", "Tipo"]);
                $tabela->set_align(["left", "left", "center", "left"]);
                $tabela->set_width([20, 20, 20, 30]);
                $tabela->set_classe(["pessoal", "Licenca", "Licenca", "Licenca"]);
                $tabela->set_metodo(["get_nomeECargoELotacao", "exibePeriodo", "analisaTermino", "exibeNomeSimples"]);

                $tabela->set_editar('?fase=editaServidor&id=');
                $tabela->set_nomeColunaEditar("Acessar");
                $tabela->set_editarBotao("bullet_edit.png");
                $tabela->set_idCampo('idServidor');
                $tabela->show();

                # Exibe mensagem sobre as licenças
                $mensagem2 = null;
                foreach ($arrayLicencas as $item) {
                    $mensagem2 .= "{$licenca->exibeNomeSimples($item)}<br/>";
                }
                calloutWarning($mensagem2, "As licenças consideradas são:");
            }
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

        ############################################################################

        case "procedimentos" :

            br();
            $procedimento = new Procedimento();
            $procedimento->exibeProcedimentoSubCategoria("Licença Médica");
            break;

        ############################################################################    
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


