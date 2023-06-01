<?php

/**
 * Estatística
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
    $fase = get('fase', 'perfil');
    $diretoria = get('diretoria');
    $grafico = get('grafico');

    # Pega o ano
    $ano = post("ano", date("Y"));

    # Parametros
    $parametroPerfil = post('parametroPerfil', get_session('parametroPerfil', '*'));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', '*'));

    # Joga os parâmetros par as sessions    
    set_session('parametroPerfil', $parametroPerfil);
    set_session('parametroLotacao', $parametroLotacao);

    # Verifica se e relatorio
    $rel = get("rel");

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Limita o tamanho da tela
    $grid1 = new Grid();
    $grid1->abreColuna(12);

    # Define o select do perfil que será usado em toda a rotina
    $selectPerfil = "SELECT DISTINCT idPerfil, 
                                     tbperfil.nome,
                                     tbperfil.tipo
                                FROM tbservidor JOIN tbperfil USING (idPerfil)
                               WHERE situacao = 1
                                 AND tbperfil.tipo <> 'Outros' 
                            ORDER BY tbperfil.tipo, tbperfil.nome";

    if (!$rel) {
        # Cria um menu
        $menu1 = new MenuBar();

        # Voltar
        $linkVoltar = new Link("Voltar", "grh.php");
        $linkVoltar->set_class('button');
        $linkVoltar->set_title('Voltar para página anterior');
        $linkVoltar->set_accessKey('V');
        $menu1->add_link($linkVoltar, "left");

        if ($fase == "faixaEtaria") {
            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_imagem($imagem);
            $botaoRel->set_title("Relatório");
            $botaoRel->set_url("?fase=faixaEtaria&rel=1");
            $botaoRel->set_target("_blank");
            $menu1->add_link($botaoRel, "right");
        }

        $menu1->show();
    } else {
        br();
    }

    titulo("Estatística de Servidores");
    br();

    if (!$rel) {
        $grid = new Grid();

        ## Coluna do menu            
        $grid->abreColuna(12, 3);

        # Número de Servidores
        $painel = new Callout();
        $painel->abre();

        # Numero Geral
        $numServidores = $pessoal->get_numServidoresAtivos();
        p($numServidores, "estatisticaNumero");
        p("Servidores Ativos", "estatisticaTexto");

        # Perfil
        $numPerfil = $pessoal->select($selectPerfil);
        br();

        # Tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($numPerfil);
        $tabela->set_label(["", ""]);
        $tabela->set_align(["right", "left"]);
        $tabela->set_totalRegistro(false);

        $tabela->set_classe(["Pessoal"]);
        $tabela->set_metodo(["get_numServidoresAtivosPerfil"]);

        $tabela->show();

        $painel->fecha();

        ###############################
        # Menu de tipos
        $painel = new Callout();
        $painel->abre();

        titulo("Menu Principal");
        br();

        $itens = array(
            array('Por Perfil', 'perfil'),
            array('Por Cargo - Geral', 'cargo'),
            array('Por Cargo - Adm/Tec', 'cargoAdm'),
            array('Por Lotação x Cargo', 'cargoGerencia'),
            array('Por Professores x Diretoria', 'professorDiretoria'),
            array('Por Diretoria', 'diretoria'),
            array('Por Gerência', 'gerencia'),
            array('Por Filhos', 'filhos'),
            array('Por Idade', 'idade'),
            array('Por Faixa Etária', 'faixaEtaria'),
            array('Por Escolaridade', 'escolaridade'),
            array('Por Nacionalidade', 'nacionalidade'),
            array('Por Estado Civil', 'estadoCivil'),
            array('Por Cidade de Moradia', 'cidade')
        );

        $menu = new Menu();
        #$menu->add_item('titulo','Detalhada');

        foreach ($itens as $ii) {
            if ($fase == $ii[1]) {
                $menu->add_item('link', '<b>' . $ii[0] . '</b>', '?fase=' . $ii[1]);
            } else {
                $menu->add_item('link', $ii[0], '?fase=' . $ii[1]);
            }
        }

        #$menu->add_item('link','Temporal','?fase=temporalCargo');  # Retirado por imprecisão
        $menu->show();

        $painel->fecha();

        $grid->fechaColuna();

        ################################################################
        # Coluna de Conteúdo
        $grid->abreColuna(12, 9);
    }
    switch ($fase) {
        case "inicial":

            break;

        ################################################################

        case "idade":

            # Abre um callout
            $painal = new Callout();
            $painel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística por idade";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            # Título
            tituloTable("Por Idade");

            # Cria as colunas
            $grid2 = new Grid();
            $grid2->abreColuna(4);
            br();

            # Geral - Por Idade
            $selectGrafico = 'SELECT count(tbservidor.idServidor) as jj,
                                     TIMESTAMPDIFF(YEAR, tbpessoa.dtNasc, NOW()) AS idade
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                              JOIN tbperfil USING (idPerfil) 
                               WHERE situacao = 1 
                                 AND tbperfil.tipo <> "Outros" 
                            GROUP BY idade
                            ORDER BY 2';

            $servidores = $pessoal->select($selectGrafico);

            # Separa os arrays para analise estatística
            $idades = array();
            foreach ($servidores as $item) {
                $idades[] = $item[1];
            }

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Dados da tabela
            $dados[] = array("Maior Idade", maiorValor($idades));
            $dados[] = array("Menor Idade", menorValor($idades));
            $dados[] = array("Idade Média", media_aritmetica($idades));

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($dados);
            $tabela->set_label(["Descrição", "Idade"]);
            $tabela->set_width([50, 50]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_rodape("Total de Servidores: " . $total);
            $tabela->set_linkTituloTitle("Exibe detalhes");
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(8);

            # Chart
            $chart = new Chart("ColumnChart", $dados);
            $chart->set_idDiv("idade");
            $chart->set_legend(false);
            $chart->set_label(array("Descrição", "Idade"));
            $chart->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(12);

            $select = 'SELECT TIMESTAMPDIFF(YEAR, tbpessoa.dtNasc, NOW()) AS idade,
                              count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                              JOIN tbperfil USING (idPerfil) 
                               WHERE situacao = 1 
                                 AND tbperfil.tipo <> "Outros" 
                            GROUP BY idade
                            ORDER BY 1';

            $servidores = $pessoal->select($select);

            # Chart
            #tituloTable("por Cada Idade");
            $chart = new Chart("ColumnChart", $servidores);
            $chart->set_idDiv("faixa");
            $chart->set_label(array("Idade", "Servidores"));
            $chart->set_legend(false);
            $chart->set_idDiv("cadaIdade");
            $chart->show();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_titulo("por Cada Idade");
            $tabela->set_label(["Idade", "Servidores"]);
            $tabela->set_align(["center"]);
            $tabela->set_width([50, 50]);
            $tabela->set_rodape("Total de Servidores: " . $total);
            #$tabela->show();

            $grid2->fechaGrid();

            $painel->fecha();
            break;

        ################################################################   

        case "faixaEtaria":

            # Abre o painel
            $painel = new Callout();
            $painel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística por faixa etária";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            titulotable("por Faixa Etária");
            br();

            ########
            # Formulário de Pesquisa
            $form = new Form('?fase=faixaEtaria');

            # Lotação
            $result = $pessoal->select('SELECT DISTINCT DIR, DIR
                                          FROM tblotacao
                                         WHERE ativo
                                      ORDER BY DIR');
            array_unshift($result, array("*", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Diretoria/Centro:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            # Perfil
            $result = $pessoal->select($selectPerfil);
            array_unshift($result, array("*", 'Todos'));

            $controle = new Input('parametroPerfil', 'combo', 'Perfil:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Perfil');
            $controle->set_array($result);
            $controle->set_optgroup(true);
            $controle->set_valor($parametroPerfil);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            $form->show();

            ########

            $grid3 = new Grid();

            #######################################################
            # Faixa Etária Geral
            #######################################################

            $grid3->abreColuna(12);

            $select = '  
            SELECT CASE 
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 10 AND 19 THEN "até 19"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 20 AND 29 THEN "20 a 29"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 30 AND 39 THEN "30 a 39"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 40 AND 49 THEN "40 a 49"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 50 AND 59 THEN "50 a 59"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 60 AND 69 THEN "60 a 69"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 70 AND 79 THEN "70 a 79"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 80 AND 89 THEN "80 a 89"
                   END,
                   tbpessoa.sexo, count(tbservidor.idServidor) as jj
              FROM tbpessoa JOIN tbservidor USING (idPessoa)
                            LEFT JOIN tbhistlot USING (idServidor)
                            JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                            JOIN tbperfil USING (idPerfil)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbperfil.tipo <> "Outros" 
                     AND situacao = 1';

            if ($parametroLotacao <> '*') {
                $select .= ' AND tblotacao.dir="' . $parametroLotacao . '"';
            }

            if ($parametroPerfil <> '*') {
                $select .= ' AND tbservidor.idPerfil="' . $parametroPerfil . '"';
            }

            $select .= ' GROUP BY 1, tbpessoa.sexo ORDER BY 1';

            $servidores = $pessoal->select($select);

            # Novo array 
            $arrayResultado = array();
            $arrayGrafico = array();

            # Valores anteriores
            $escolaridadeAnterior = null;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if ($escolaridade <> $escolaridadeAnterior) {
                    if (is_null($escolaridadeAnterior)) {
                        $escolaridadeAnterior = $escolaridade;
                    } else {
                        $arrayResultado[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);
                        $arrayGrafico[] = array($escolaridadeAnterior, $femi, $masc);
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi + $masc);
                    }
                }

                if ($sexo == 'Masculino') {
                    $masc = $contagem;
                    $totalMasc += $masc;
                } else {
                    $femi = $contagem;
                    $totalFemi += $femi;
                }
            }

            $arrayResultado[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);
            $arrayGrafico[] = array($escolaridadeAnterior, $femi, $masc);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            $arrayResultado[] = ["Total", $totalFemi, $totalMasc, $total];

            # Chart
            $chart = new Chart("ColumnChart", $arrayGrafico, 2);
            $chart->set_cores(["Violet", "CornflowerBlue"]);
            $chart->set_idDiv("faixa");
            $chart->set_label(["Faixa", "Feminino", "Masculino"]);
            $chart->set_tituloEixoY("Servidores");
            $chart->set_tituloEixoX("Faixa Etária");
            $chart->set_legend(false);
            $chart->show();
            br();

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayResultado);
            $tabela->set_titulo("Todos os Cargos");
            $tabela->set_label(["Faixa", "Feminino", "Masculino", "Total"]);
            $tabela->set_width([55, 15, 15, 15]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                    'valor' => "Total",
                    'operador' => '=',
                    'id' => 'estatisticaTotal')));

            $tabela->show();

            $grid3->fechaColuna();
            #############################
            # Adm/Tec
            #############################
            $grid3->abreColuna(6);

            $select = '  
            SELECT CASE 
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 10 AND 19 THEN "até 19"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 20 AND 29 THEN "20 a 29"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 30 AND 39 THEN "30 a 39"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 40 AND 49 THEN "40 a 49"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 50 AND 59 THEN "50 a 59"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 60 AND 69 THEN "60 a 69"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 70 AND 79 THEN "70 a 79"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 80 AND 89 THEN "80 a 89"
                   END,
                   tbpessoa.sexo, count(tbservidor.idServidor) as jj
              FROM tbpessoa JOIN tbservidor USING (idPessoa)
                            JOIN tbcargo USING (idCargo)
                            JOIN tbtipocargo USING (idTipoCargo)
                            LEFT JOIN tbhistlot USING (idServidor)
                            JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)                            
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbtipocargo.tipo = "Adm/Tec"
                     AND situacao = 1';

            if ($parametroLotacao <> '*') {
                $select .= ' AND tblotacao.dir="' . $parametroLotacao . '"';
            }

            if ($parametroPerfil <> '*') {
                $select .= ' AND tbservidor.idPerfil="' . $parametroPerfil . '"';
            }

            $select .= ' GROUP BY 1, tbpessoa.sexo ORDER BY 1';

            $servidores = $pessoal->select($select);

            # Novo array 
            $arrayResultado = array();
            $arrayGrafico = array();

            # Valores anteriores
            $escolaridadeAnterior = null;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if ($escolaridade <> $escolaridadeAnterior) {
                    if (is_null($escolaridadeAnterior)) {
                        $escolaridadeAnterior = $escolaridade;
                    } else {
                        $arrayResultado[] = [$escolaridadeAnterior, $femi, $masc, $femi + $masc];
                        $arrayGrafico[] = [$escolaridadeAnterior, $femi, $masc];
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi + $masc);
                    }
                }

                if ($sexo == 'Masculino') {
                    $masc = $contagem;
                    $totalMasc += $masc;
                } else {
                    $femi = $contagem;
                    $totalFemi += $femi;
                }
            }

            $arrayResultado[] = [$escolaridadeAnterior, $femi, $masc, $femi + $masc];
            $arrayGrafico[] = [$escolaridadeAnterior, $femi, $masc];

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            $arrayResultado[] = ["Total", $totalFemi, $totalMasc, $total];

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayResultado);
            $tabela->set_titulo("Administrativos e Tecnicos");
            $tabela->set_label(["Faixa", "Feminino", "Masculino", "Total"]);
            $tabela->set_width([55, 15, 15, 15]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 0,
                    'valor' => "Total",
                    'operador' => '=',
                    'id' => 'estatisticaTotal')));

            $tabela->show();

            $grid3->fechaColuna();
            #############################
            # Professor
            #############################
            $grid3->abreColuna(6);

            $select = '  
            SELECT CASE 
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 10 AND 19 THEN "até 19"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 20 AND 29 THEN "20 a 29"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 30 AND 39 THEN "30 a 39"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 40 AND 49 THEN "40 a 49"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 50 AND 59 THEN "50 a 59"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 60 AND 69 THEN "60 a 69"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 70 AND 79 THEN "70 a 79"
                   WHEN (TIMESTAMPDIFF(YEAR, dtNasc, NOW())) BETWEEN 80 AND 89 THEN "80 a 89"
                   END,
                   tbpessoa.sexo, count(tbservidor.idServidor) as jj
              FROM tbpessoa JOIN tbservidor USING (idPessoa)
                            JOIN tbcargo USING (idCargo)
                            JOIN tbtipocargo USING (idTipoCargo)
                            LEFT JOIN tbhistlot USING (idServidor)
                            JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)                            
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbtipocargo.tipo = "Professor"
                     AND situacao = 1';

            if ($parametroLotacao <> '*') {
                $select .= ' AND tblotacao.dir="' . $parametroLotacao . '"';
            }

            if ($parametroPerfil <> '*') {
                $select .= ' AND tbservidor.idPerfil="' . $parametroPerfil . '"';
            }

            $select .= ' GROUP BY 1, tbpessoa.sexo ORDER BY 1';

            $servidores = $pessoal->select($select);

            # Novo array 
            $arrayResultado = array();
            $arrayGrafico = array();

            # Valores anteriores
            $escolaridadeAnterior = null;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if ($escolaridade <> $escolaridadeAnterior) {
                    if (is_null($escolaridadeAnterior)) {
                        $escolaridadeAnterior = $escolaridade;
                    } else {
                        $arrayResultado[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);
                        $arrayGrafico[] = array($escolaridadeAnterior, $femi, $masc);
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi + $masc);
                    }
                }

                if ($sexo == 'Masculino') {
                    $masc = $contagem;
                    $totalMasc += $masc;
                } else {
                    $femi = $contagem;
                    $totalFemi += $femi;
                }
            }

            $arrayResultado[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);
            $arrayGrafico[] = array($escolaridadeAnterior, $femi, $masc);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            $arrayResultado[] = array("Total", $totalFemi, $totalMasc, $total);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayResultado);
            $tabela->set_titulo("Docentes");
            $tabela->set_label(["Faixa", "Feminino", "Masculino", "Total"]);
            $tabela->set_width([55, 15, 15, 15]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 0,
                    'valor' => "Total",
                    'operador' => '=',
                    'id' => 'estatisticaTotal')));

            $tabela->show();

            ####################################

            $grid3->fechaColuna();
            $grid3->fechaGrid();
            $painel->fecha();
            break;

        ################################################################   

        case "perfil":

            # Abre um callout
            $panel = new Callout();
            $panel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística por perfil";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            # Título
            tituloTable("por Perfil");
            br();

            $estatistica = new Estatistica("perfil");

            $grid = new Grid();
            $grid->abreColuna(7);

            $estatistica->exibeTabelaPorSexo();

            $grid->fechaColuna();
            $grid->abreColuna(5);

            $estatistica->exibeGraficoSimples();

            $grid->fechaColuna();
            $grid->fechaGrid();

            br();

            $estatistica->exibeGraficoPorSexo();

            $panel->fecha();
            break;

        ################################################################        

        case "cargo":

            # Abre um callout
            $panel = new Callout();
            $panel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística por cargo";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            # Título
            tituloTable("por Cargo - Geral");

            ########
            # Formulário de Pesquisa
            $form = new Form('?fase=cargo');

            # Perfil
            $result = $pessoal->select('SELECT idperfil,
                                               nome,
                                               tipo
                                          FROM tbperfil
                                         WHERE tipo <> "Outros"  
                                      ORDER BY tipo, nome');

            array_unshift($result, array("*", 'Todos'));

            $controle = new Input('parametroPerfil', 'combo', 'Perfil:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Perfil');
            $controle->set_array($result);
            $controle->set_autofocus(true);
            $controle->set_optgroup(true);
            $controle->set_valor($parametroPerfil);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            $form->show();

            ########

            $grid3 = new Grid();
            $grid3->abreColuna(4);
            br();

            # Geral - Por Cargo
            $selectGrafico = 'SELECT tbtipocargo.tipo, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbcargo USING (idCargo)
                                                LEFT JOIN tbtipocargo USING (idTipoCargo)
                                                JOIN tbperfil USING (idPerfil)
                               WHERE situacao = 1
                               AND tbperfil.tipo <> "Outros"  ';
            # Perfil
            if ($parametroPerfil <> '*') {
                $selectGrafico .= ' AND tbservidor.idPerfil="' . $parametroPerfil . '"';
            }

            $selectGrafico .= '    GROUP BY tbtipocargo.tipo
                            ORDER BY 2 DESC ';

            $servidores = $pessoal->select($selectGrafico);

            # Exemplo de tabela simples
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_label(["Tipo do Cargo", "Servidores"]);
            $tabela->set_width([80, 20]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_colunaSomatorio(1);
            $tabela->set_totalRegistro(false);
            $tabela->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(2);

            $grid3->fechaColuna();
            $grid3->abreColuna(6);

            #tituloTable("por Cargo");
            $chart = new Chart("Pie", $servidores);
            $chart->set_idDiv("cargo");
            $chart->set_legend(false);
            $chart->set_tamanho($largura = 300, $altura = 300);
            $chart->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(6);

            # Adm/Tec
            $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.idServidor) as jj
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1 ';
            # Perfil
            if ($parametroPerfil <> '*') {
                $selectGrafico .= ' AND tbservidor.idPerfil="' . $parametroPerfil . '"';
            }

            $selectGrafico .= ' AND tbtipocargo.tipo = "Adm/Tec" GROUP BY tbtipocargo.cargo
                        ORDER BY 1 DESC ';

            $servidores = $pessoal->select($selectGrafico);
            tituloTable("Administrativos e Técnicos");
            $chart = new Chart("Pie", $servidores);
            $chart->set_idDiv("administrativos");
            #$chart->set_legend(false);
            $chart->show();

            # Tabela
            $selectGrafico = 'SELECT tbtipocargo.cargo, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                              JOIN tbcargo USING (idCargo)
                                              JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1';
            # Perfil
            if ($parametroPerfil <> '*') {
                $selectGrafico .= ' AND tbservidor.idPerfil="' . $parametroPerfil . '"';
            }

            $selectGrafico .= ' AND tbtipocargo.tipo = "Adm/Tec"
                            GROUP BY tbtipocargo.cargo, tbpessoa.sexo
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);

            # Novo array 
            $arrayEscolaridade = array();

            # Valores anteriores
            $escolaridadeAnterior = null;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if ($escolaridade <> $escolaridadeAnterior) {
                    if (is_null($escolaridadeAnterior)) {
                        $escolaridadeAnterior = $escolaridade;
                    } else {
                        $arrayEscolaridade[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi + $masc);
                    }
                }

                if ($sexo == 'Masculino') {
                    $masc = $contagem;
                    $totalMasc += $masc;
                } else {
                    $femi = $contagem;
                    $totalFemi += $femi;
                }
            }

            $arrayEscolaridade[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            $arrayEscolaridade[] = array("Total", $totalFemi, $totalMasc, $total);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayEscolaridade);
            #$tabela->set_titulo("Adm/Tec");
            $tabela->set_label(["Cargo", "Feminino", "Masculino", "Total"]);
            $tabela->set_width([55, 15, 15, 15]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                    'valor' => "Total",
                    'operador' => '=',
                    'id' => 'estatisticaTotal')));

            $tabela->show();

            $grid3->fechaColuna();
            $grid3->abreColuna(6);

            # Professores
            $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.idServidor) as jj
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1';
            # Perfil
            if ($parametroPerfil <> '*') {
                $selectGrafico .= ' AND tbservidor.idPerfil="' . $parametroPerfil . '"';
            }

            $selectGrafico .= ' AND tbtipocargo.tipo = "Professor" GROUP BY tbtipocargo.cargo
                            ORDER BY 1 DESC ';

            $servidores = $pessoal->select($selectGrafico);
            tituloTable("Professores");
            $chart = new Chart("Pie", $servidores);
            $chart->set_idDiv("professores");
            #$chart->set_legend(false);
            $chart->show();

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Tebela
            $selectGrafico = 'SELECT tbtipocargo.cargo, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                              JOIN tbcargo USING (idCargo)
                                              JOIN tbtipocargo USING (idTipoCargo)
                               WHERE tbservidor.situacao = 1';
            # Perfil
            if ($parametroPerfil <> '*') {
                $selectGrafico .= ' AND tbservidor.idPerfil="' . $parametroPerfil . '"';
            }

            $selectGrafico .= ' AND tbtipocargo.tipo = "Professor"
                            GROUP BY tbtipocargo.cargo, tbpessoa.sexo
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);

            # Novo array 
            $arrayEscolaridade = array();

            # Valores anteriores
            $escolaridadeAnterior = null;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if ($escolaridade <> $escolaridadeAnterior) {
                    if (is_null($escolaridadeAnterior)) {
                        $escolaridadeAnterior = $escolaridade;
                    } else {
                        $arrayEscolaridade[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi + $masc);
                    }
                }

                if ($sexo == 'Masculino') {
                    $masc = $contagem;
                    $totalMasc += $masc;
                } else {
                    $femi = $contagem;
                    $totalFemi += $femi;
                }
            }

            $arrayEscolaridade[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            $arrayEscolaridade[] = array("Total", $totalFemi, $totalMasc, $total);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayEscolaridade);
            #$tabela->set_titulo("Professor");
            $tabela->set_label(["Cargo", "Feminino", "Masculino", "Total"]);
            $tabela->set_width([55, 15, 15, 15]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 0,
                    'valor' => "Total",
                    'operador' => '=',
                    'id' => 'estatisticaTotal')));

            $tabela->show();

            $grid3->fechaColuna();
            $grid3->fechaGrid();
            $panel->fecha();
            break;

        ################################################################        

        case "cargoAdm":

            # Abre um callout
            $panel = new Callout();
            $panel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística por cargo administrativo e técnico";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            # Título
            tituloTable("por Cargo - Administrativo e Tecnicos");
            br();

            $grid3 = new Grid();
            $grid3->abreColuna(12);

            #############################
            # Adm/Tec
            $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.idServidor) as jj
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                                                JOIN tbperfil USING (idPerfil) 
                               WHERE situacao = 1 
                                 AND tbperfil.tipo <> "Outros" 
                                 AND tbtipocargo.tipo = "Adm/Tec" GROUP BY tbtipocargo.cargo
                        ORDER BY 1 DESC ';

            $servidores = $pessoal->select($selectGrafico);
            $chart = new Chart("Pie", $servidores);
            $chart->set_idDiv("administrativos");
            $chart->set_tamanho(800, 500);
            $chart->show();

            # Tabela
            $selectGrafico = 'SELECT tbtipocargo.cargo, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                              JOIN tbcargo USING (idCargo)
                                              JOIN tbtipocargo USING (idTipoCargo)
                                              JOIN tbperfil USING (idPerfil) 
                               WHERE situacao = 1 
                                 AND tbperfil.tipo <> "Outros" 
                               AND tbtipocargo.tipo = "Adm/Tec"
                            GROUP BY tbtipocargo.cargo, tbpessoa.sexo
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);

            # Novo array 
            $arrayEscolaridade = array();

            # Valores anteriores
            $escolaridadeAnterior = null;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if ($escolaridade <> $escolaridadeAnterior) {
                    if (is_null($escolaridadeAnterior)) {
                        $escolaridadeAnterior = $escolaridade;
                    } else {
                        $arrayEscolaridade[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi + $masc);
                    }
                }

                if ($sexo == 'Masculino') {
                    $masc = $contagem;
                    $totalMasc += $masc;
                } else {
                    $femi = $contagem;
                    $totalFemi += $femi;
                }
            }

            $arrayEscolaridade[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            $arrayEscolaridade[] = array("Total", $totalFemi, $totalMasc, $total);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayEscolaridade);
            #$tabela->set_titulo("Geral");
            $tabela->set_label(["Cargo", "Feminino", "Masculino", "Total"]);
            $tabela->set_width([55, 15, 15, 15]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 0,
                    'valor' => "Total",
                    'operador' => '=',
                    'id' => 'estatisticaTotal')));

            #$tabela->show();
            $grid3->fechaColuna();

            #############################
            # Pega os Cargos Administrativos
            $selectCargo = "SELECT idTipoCargo, cargo"
                    . "  FROM tbtipocargo"
                    . " WHERE tipo = 'Adm/Tec' ORDER by 1 desc";

            $dadosCargo = $pessoal->select($selectCargo);

            foreach ($dadosCargo as $cc) {

                $grid3->abreColuna(6);

                # Tabela
                $selectGrafico = 'SELECT tbcargo.nome, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                    FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                                  JOIN tbcargo USING (idCargo)
                                                  JOIN tbperfil USING (idPerfil) 
                                   WHERE situacao = 1 
                                     AND tbperfil.tipo <> "Outros" 
                                     AND idTipoCargo = ' . $cc[0] . '
                                GROUP BY tbcargo.nome, tbpessoa.sexo
                                ORDER BY 1';

                $servidores = $pessoal->select($selectGrafico);

                # Novo array 
                $arrayEscolaridade = array();

                # Valores anteriores
                $escolaridadeAnterior = null;

                # inicia as variáveis
                $masc = 0;
                $femi = 0;
                $totalMasc = 0;
                $totalFemi = 0;
                $total = 0;

                # Modelar o novo array
                foreach ($servidores as $value) {
                    # Carrega as variáveis
                    $escolaridade = $value[0];
                    $sexo = $value[1];
                    $contagem = $value[2];

                    # Verifica se mudou de escolaridade
                    if ($escolaridade <> $escolaridadeAnterior) {
                        if (is_null($escolaridadeAnterior)) {
                            $escolaridadeAnterior = $escolaridade;
                        } else {
                            $arrayEscolaridade[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);
                            $masc = 0;
                            $femi = 0;
                            $escolaridadeAnterior = $escolaridade;
                            $total += ($femi + $masc);
                        }
                    }

                    if ($sexo == 'Masculino') {
                        $masc = $contagem;
                        $totalMasc += $masc;
                    } else {
                        $femi = $contagem;
                        $totalFemi += $femi;
                    }
                }

                $arrayEscolaridade[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));

                $arrayEscolaridade[] = array("Total", $totalFemi, $totalMasc, $total);

                # Tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($arrayEscolaridade);
                $tabela->set_titulo($cc[1]);
                $tabela->set_label(["Cargo", "Feminino", "Masculino", "Total"]);
                $tabela->set_width([55, 15, 15, 15]);
                $tabela->set_align(["left", "center"]);
                $tabela->set_totalRegistro(false);
                $tabela->set_formatacaoCondicional(array(
                    array('coluna' => 0,
                        'valor' => "Total",
                        'operador' => '=',
                        'id' => 'estatisticaTotal')));

                $tabela->show();

                $grid3->fechaColuna();
            }

            $grid3->fechaGrid();
            $panel->fecha();
            break;

        ################################################################################################################################       

        case "diretoria":

            # Abre um callout
            $panel = new Callout();
            $panel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística por diretoria";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            # Título
            tituloTable("por Diretoria");
            br();

            $grid2 = new Grid();
            $grid2->abreColuna(4);

            # Pega os dados
            $selectGrafico = 'SELECT tblotacao.dir, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT  JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                                      JOIN tbperfil USING (idPerfil) 
                               WHERE situacao = 1 
                                 AND tbperfil.tipo <> "Outros" 
                                 AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                 AND ativo
                            GROUP BY tblotacao.dir
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            #$tabela->set_titulo("por Lotação");
            $tabela->set_label(["Diretoria", "Servidores"]);
            $tabela->set_width([80, 20]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_rodape("Total de Servidores: " . $total);
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(8);

            # Chart
            #tituloTable($item[0]);
            $chart = new Chart("Pie", $servidores);
            $chart->set_idDiv('porLotacao');
            $chart->set_legend(false);
            $chart->set_tamanho($largura = 500, $altura = 500);
            $chart->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            # Adm/Tec
            $selectGrafico = 'SELECT tblotacao.dir, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                              LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                              JOIN tbcargo USING (idCargo)
                                              JOIN tbtipocargo USING (idTipoCargo)
                                              JOIN tbperfil USING (idPerfil)
                               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                     AND tbperfil.tipo <> "Outros" 
                                     AND situacao = 1
                                     AND ativo
                                     AND tbtipocargo.tipo = "Adm/Tec"
                            GROUP BY tblotacao.dir, tbpessoa.sexo
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);

            # Novo array 
            $arrayEscolaridade = array();

            # Valores anteriores
            $escolaridadeAnterior = null;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if ($escolaridade <> $escolaridadeAnterior) {
                    if (is_null($escolaridadeAnterior)) {
                        $escolaridadeAnterior = $escolaridade;
                    } else {
                        $arrayEscolaridade[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi + $masc);
                    }
                }

                if ($sexo == 'Masculino') {
                    $masc = $contagem;
                    $totalMasc += $masc;
                } else {
                    $femi = $contagem;
                    $totalFemi += $femi;
                }
            }

            $arrayEscolaridade[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            $arrayEscolaridade[] = ["Total", $totalFemi, $totalMasc, $total];

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayEscolaridade);
            $tabela->set_titulo("Adm/Tec");
            $tabela->set_label(["Lotação", "Feminino", "Masculino", "Total"]);
            $tabela->set_width([55, 15, 15, 15]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 0,
                    'valor' => "Total",
                    'operador' => '=',
                    'id' => 'estatisticaTotal')));

            $tabela->show();

            $grid2->fechaColuna();

            ##########################################

            $grid2->abreColuna(6);

            # Professor
            $selectGrafico = 'SELECT tblotacao.dir, tbpessoa.sexo, count(tbservidor.idServidor) as jj
                                FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                         LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                              JOIN tbcargo USING (idCargo)
                                              JOIN tbtipocargo USING (idTipoCargo)
                                              JOIN tbperfil USING (idPerfil)
                               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                     AND tbperfil.tipo <> "Outros" 
                                     AND situacao = 1
                                     AND ativo
                                     AND tbtipocargo.tipo = "Professor"
                            GROUP BY tblotacao.dir, tbpessoa.sexo
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);

            # Novo array 
            $arrayEscolaridade = array();

            # Valores anteriores
            $escolaridadeAnterior = null;

            # inicia as variáveis
            $masc = 0;
            $femi = 0;
            $totalMasc = 0;
            $totalFemi = 0;
            $total = 0;

            # Modelar o novo array
            foreach ($servidores as $value) {
                # Carrega as variáveis
                $escolaridade = $value[0];
                $sexo = $value[1];
                $contagem = $value[2];

                # Verifica se mudou de escolaridade
                if ($escolaridade <> $escolaridadeAnterior) {
                    if (is_null($escolaridadeAnterior)) {
                        $escolaridadeAnterior = $escolaridade;
                    } else {
                        $arrayEscolaridade[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);
                        $masc = 0;
                        $femi = 0;
                        $escolaridadeAnterior = $escolaridade;
                        $total += ($femi + $masc);
                    }
                }

                if ($sexo == 'Masculino') {
                    $masc = $contagem;
                    $totalMasc += $masc;
                } else {
                    $femi = $contagem;
                    $totalFemi += $femi;
                }
            }

            $arrayEscolaridade[] = array($escolaridadeAnterior, $femi, $masc, $femi + $masc);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            $arrayEscolaridade[] = array("Total", $totalFemi, $totalMasc, $total);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayEscolaridade);
            $tabela->set_titulo("Professor");
            $tabela->set_label(["Lotação", "Feminino", "Masculino", "Total"]);
            $tabela->set_width([55, 15, 15, 15]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 0,
                    'valor' => "Total",
                    'operador' => '=',
                    'id' => 'estatisticaTotal')));

            $tabela->show();

            $grid2->fechaColuna();

            ###

            $grid2->abreColuna(12);

            # Numero de Servidores por Diretoria / Cargo 
            # Pega as diretorias ativas
            $select1 = 'SELECT DISTINCT dir
                        FROM tblotacao
                       WHERE ativo';

            $diretorias = $pessoal->select($select1);

            # Pega os cargos
            $select2 = 'SELECT idtipocargo, cargo
                        FROM tbtipocargo
                    ORDER BY idTipoCargo';

            $cargos = $pessoal->select($select2);
            $numeroCargos = $pessoal->count($select2);

            # Cria um array onde terá os resultados
            $resultado = array();

            # Cria e preenche o array do total da coluna
            $totalColuna = array();
            $totalColuna = array_fill(0, $numeroCargos + 2, 0);

            # Cria e preenche o array do label
            $label = array("Diretoria");
            foreach ($cargos as $cc) {
                $label[] = $cc[1];
            }
            $label[] = "Total";

            # Zera o contador de linha
            $linha = 0;

            # Trata o parametro do perfil transformanto * em nulo
            if ($parametroPerfil == "*") {
                $idPerfil = null;
            } else {
                $idPerfil = $parametroPerfil;
            }

            # Percorre as diretorias
            foreach ($diretorias as $dd) {
                $resultado[$linha][0] = $dd[0];     // Sigoa da Diretoria 
                $coluna = 1;                        // Inicia a coluna
                $totalLinha = 0;                    // Zera totalizador de cada linha
                # Percorre as colunas / Cargos
                foreach ($cargos as $cc) {
                    $quantidade = $pessoal->get_numServidoresAtivosCargoLotacao($cc[0], $dd[0], $idPerfil);    // Pega a quantidade de servidores
                    $resultado[$linha][$coluna] = $quantidade;                                                  // Joga para o array de exibição
                    $totalLinha = $totalLinha + $quantidade;                                                    // Soma o total da linha a quantidade da coluna
                    $totalColuna[$coluna] += $quantidade;                                                       // Soma o total da coluna a quantidade da linha
                    $coluna++;                                                                                  // Incrementa a coluna
                }
                # Faz a última coluna com o total da linha
                $resultado[$linha][$coluna] = $totalLinha;
                $totalColuna[$coluna] += $totalLinha;
                $linha++;
            }
            # Faz a última lina com os totais das colunas
            $resultado[$linha][0] = "Total";
            $coluna = 1;
            foreach ($cargos as $cc) {
                $resultado[$linha][$coluna] = $totalColuna[$coluna];
                $coluna++;
            }
            $resultado[$linha][$coluna] = $totalColuna[$coluna];

            $tabela = new Tabela();
            $tabela->set_titulo("Número de Servidores por Diretoria / Cargo");
            $tabela->set_conteudo($resultado);
            $tabela->set_label($label);
            $tabela->set_totalRegistro(false);
            $tabela->set_align(array("left", "center"));
            $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                    'valor' => "Total",
                    'operador' => '=',
                    'id' => 'estatisticaTotal')));
            $tabela->show();

            ###

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            $panel->fecha();
            break;

        ################################################################################################################################    

        case "gerencia":

            # Abre um callout
            $panel = new Callout();
            $panel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística por gerência";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            # Título
            tituloTable("por Gerência");
            br();

            $grid2 = new Grid();

            # Pega os dados
            $selectGrafico = 'SELECT tblotacao.dir, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT  JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                                      JOIN tbperfil USING (idPerfil)
                               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                 AND tbperfil.tipo <> "Outros" 
                                 AND situacao = 1
                                 AND ativo
                            GROUP BY tblotacao.dir
                            ORDER BY 1';

            $servidores = $pessoal->select($selectGrafico);

            foreach ($servidores as $item) {
                $grid2->abreColuna(4);

                # exibe a tabela
                $selectGrafico2 = 'SELECT tblotacao.ger, count(tbservidor.idServidor) as jj
                                        FROM tbservidor LEFT  JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                                              JOIN tbperfil USING (idPerfil)
                                       WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                                         AND tbperfil.tipo <> "Outros" 
                                         AND situacao = 1
                                         AND ativo
                                         AND tblotacao.dir="' . $item[0] . '" 
                                    GROUP BY tblotacao.ger
                                    ORDER BY 2 desc';

                $servidores = $pessoal->select($selectGrafico2);

                # Chart
                tituloTable($item[0]);
                $chart = new Chart("Pie", $servidores);
                $chart->set_idDiv($item[0]);
                $chart->set_legend(false);
                $chart->show();

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));

                # Exemplo de tabela simples
                $tabela = new Tabela();
                #$tabela->set_titulo($item[0]);
                $tabela->set_conteudo($servidores);
                $tabela->set_label(["Lotação", "Servidores"]);
                $tabela->set_width([80, 20]);
                $tabela->set_align(["left", "center"]);
                $tabela->set_rodape("Total de Servidores: " . $total);
                $tabela->show();

                $grid2->fechaColuna();
            }



            $grid2->fechaGrid();
            break;

        ################################################################################################################################

        case "escolaridade":

            # Sexo por Lotação
            $painel = new Callout();
            $painel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística por escolaridade";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            titulotable("por Escolaridade");
            br();

            $grid3 = new Grid();
            $grid3->abreColuna(12);

            # Adm/Tec
            $selectGrafico = 'SELECT idServidor, tbpessoa.sexo
                                FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                                JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                               WHERE idPerfil = 1
                               AND tbservidor.situacao = 1
                               AND tbtipocargo.tipo = "Adm/Tec"';

            $servidores = $pessoal->select($selectGrafico);

            # Inicia a classe de formação
            $formação = new Formacao();

            # Cria um array para o gráfico
            $elementarMasculino = 0;
            $elementarFeminino = 0;
            $elementarTotal = 0;

            $fundamentalMasculino = 0;
            $fundamentalFeminino = 0;
            $fundamentalTotal = 0;

            $medioMasculino = 0;
            $medioFeminino = 0;
            $medioTotal = 0;

            $superiorMasculino = 0;
            $superiorFeminino = 0;
            $superiorTotal = 0;

            $especializacaoMasculino = 0;
            $especializacaoFeminino = 0;
            $especializacaoTotal = 0;

            $mestradoMasculino = 0;
            $mestradoFeminino = 0;
            $mestradoTotal = 0;

            $doutoradoMasculino = 0;
            $doutoradoFeminino = 0;
            $doutoradoTotal = 0;

            $masculinoTotal = 0;
            $femininoTotal = 0;

            # Percorre o array preenchendo os valores
            foreach ($servidores as $value) {

                # Carrega as variáveis
                $escolaridade = $formação->get_escolaridade($value[0]);
                $sexo = $value[1];

                /* Preenche os array de acordo com a escolaridade e o sexo
                 *  2	Elementar
                 *  4	Fundamental
                 *  6	Médio
                 *  8	Superior
                 *  9	Latu Senso/MBA
                 *  10	Mestrado
                 *  11	Doutorado
                 *  12	Extensão                 * 
                 */

                switch ($escolaridade) {

                    # Elementar
                    case 2:
                        if ($sexo == 'Masculino') {
                            $elementarMasculino++;
                            $masculinoTotal++;
                        } else {
                            $elementarFeminino++;
                            $femininoTotal++;
                        }
                        $elementarTotal++;
                        break;

                    # Fundamental    
                    case 4:
                        if ($sexo == 'Masculino') {
                            $fundamentalMasculino++;
                            $masculinoTotal++;
                        } else {
                            $fundamentalFeminino++;
                            $femininoTotal++;
                        }
                        $fundamentalTotal++;
                        break;

                    # Médio    
                    case 6:
                        if ($sexo == 'Masculino') {
                            $medioMasculino++;
                            $masculinoTotal++;
                        } else {
                            $medioFeminino++;
                            $femininoTotal++;
                        }
                        $medioTotal++;
                        break;

                    # Superior    
                    case 8:
                        if ($sexo == 'Masculino') {
                            $superiorMasculino++;
                            $masculinoTotal++;
                        } else {
                            $superiorFeminino++;
                            $femininoTotal++;
                        }
                        $superiorTotal++;
                        break;

                    # Latu sensu / MBA
                    case 9:
                        if ($sexo == 'Masculino') {
                            $especializacaoMasculino++;
                            $masculinoTotal++;
                        } else {
                            $especializacaoFeminino++;
                            $femininoTotal++;
                        }
                        $especializacaoTotal++;
                        break;

                    # Mestrado    
                    case 10:
                        if ($sexo == 'Masculino') {
                            $mestradoMasculino++;
                            $masculinoTotal++;
                        } else {
                            $mestradoFeminino++;
                            $femininoTotal++;
                        }
                        $mestradoTotal++;
                        break;

                    # Doutorado
                    case 11:
                        if ($sexo == 'Masculino') {
                            $doutoradoMasculino++;
                            $masculinoTotal++;
                        } else {
                            $doutoradoFeminino++;
                            $femininoTotal++;
                        }
                        $doutoradoTotal++;
                        break;
                }
            }

            # Forma o array da tabela
            $arrayEscolaridade[] = array("Doutorado", $doutoradoFeminino, $doutoradoMasculino, $doutoradoTotal);
            $arrayEscolaridade[] = array("Mestrado", $mestradoFeminino, $mestradoMasculino, $mestradoTotal);
            $arrayEscolaridade[] = array("Especialização", $especializacaoFeminino, $especializacaoMasculino, $especializacaoTotal);
            $arrayEscolaridade[] = array("Superior", $superiorFeminino, $superiorMasculino, $superiorTotal);
            $arrayEscolaridade[] = array("Médio", $medioFeminino, $medioMasculino, $medioTotal);
            $arrayEscolaridade[] = array("Fundamental", $fundamentalFeminino, $fundamentalMasculino, $fundamentalTotal);
            $arrayEscolaridade[] = array("Elementar", $elementarFeminino, $elementarMasculino, $elementarTotal);
            $arrayEscolaridade[] = array("Total", $femininoTotal, $masculinoTotal, $femininoTotal + $masculinoTotal);

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($arrayEscolaridade);
            $tabela->set_titulo("Servidores Estatutários Adm/Tec");
            $tabela->set_label(["Escolaridade", "Feminino", "Masculino", "Total"]);
            $tabela->set_width([55, 15, 15, 15]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 0,
                    'valor' => "Total",
                    'operador' => '=',
                    'id' => 'estatisticaTotal')));

            $tabela->show();

            $grid3->fechaColuna();
            $grid3->fechaGrid();
            $painel->fecha();
            break;

        ################################################################

        case "nacionalidade":

            # Abre um callout
            $panel = new Callout();
            $panel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística por nacionalidade";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            # Título
            tituloTable("por Nacionalidade");
            br();

            $grid = new Grid();
            $grid->abreColuna(6);

            $estatistica = new Estatistica("nacionalidade");
            $estatistica->exibeTabelaPorSexo("por Sexo (Todos os Servidores Ativos)");

            $grid->fechaColuna();
            $grid->abreColuna(6);

            $estatistica = new Estatistica("nacionalidade");
            $estatistica->exibeTabelaPorTipoCargo("por Cargo (Estatutários Ativos)");

            $grid->fechaColuna();
            $grid->abreColuna(6);

            $estatistica = new Estatistica("nacionalidade", false);
            $estatistica->exibeTabelaPorTipoCargo("por Cargo (Ativos e Inativos)");

            $grid->fechaColuna();
            $grid->fechaGrid();
            $painel->fecha();
            break;

        ################################################################   

        case "estadoCivil":

            # Abre um callout
            $panel = new Callout();
            $panel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística por estado civil";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            # Título
            tituloTable("por Estado Civil");
            br();

            $grid = new Grid();
            $grid->abreColuna(6);

            $estatistica = new Estatistica("estadoCivil");
            $estatistica->exibeTabelaPorSexo();

            $grid->fechaColuna();
            $grid->abreColuna(6);

            $estatistica->exibeGraficoSimples(400);

            $grid->fechaColuna();
            $grid->fechaGrid();
            $painel->fecha();
            break;

        ###########################################################   

        case "cidade":

            # Abre um callout
            $panel = new Callout();
            $panel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística por cidade de moradia";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            # Título
            titulotable("por Cidade de Moradia");
            br();

            $grid = new Grid();
            $grid->abreColuna(5);

            $estatistica = new Estatistica("cidade");
            $estatistica->exibeTabelaSimples();

            $grid->fechaColuna();
            $grid->abreColuna(7);

            $estatistica->exibeGraficoSimples(500);

            $grid->fechaColuna();
            $grid->fechaGrid();
            $painel->fecha();
            break;

        ################################################################ 


        case "temporalCargo":
            titulo("Número de Servidores que Trabalharam na UENF em " . $ano);

            $grid2 = new Grid("center");
            $grid2->abreColuna(12);

            # Formulário do Ano
            $form = new Form('?fase=temporalCargo');

            # Preenche o array
            for ($i = 2000; $i <= date("Y"); $i++) {
                $listaAnos[] = $i;
            }

            $controle = new Input('ano', 'combo', 'Ano:', 1);
            $controle->set_size(5);
            $controle->set_title('Ano:');
            $controle->set_array($listaAnos);
            $controle->set_valor($ano);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            $grid2 = new Grid();
            $grid2->abreColuna(4);

            # Pega os dados
            $selectGrafico = 'SELECT tbtipocargo.tipo, count(tbservidor.idServidor) as jj
                                FROM tbservidor LEFT JOIN tbcargo USING (idCargo)
                                                LEFT JOIN tbtipocargo USING (idTipoCargo)
                                                JOIN tbperfil USING (idPerfil)
                               WHERE YEAR(dtadmissao) <= "' . $ano . '" 
                                 AND ((dtdemissao IS null) OR (YEAR(dtdemissao) >= "' . $ano . '"))
                                 AND tbperfil.tipo <> "Outros" 
                            GROUP BY tbtipocargo.tipo
                            ORDER BY 2 DESC ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Exemplo de tabela simples
            $tabela = new Tabela();
            $tabela->set_titulo("por Cargo (Temporal)");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(["Cargo", "Servidores"]);
            $tabela->set_width([80, 20]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_rodape("Total de Servidores: " . $total);
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(8);

            $chart = new Chart("Pie", $servidores);
            #$chart->set_tamanho(700,300);
            $chart->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            hr();

            ############################################################################################

            $grid2 = new Grid();
            $grid2->abreColuna(6);

            # Adm/Tec
            $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.idServidor) as jj
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                                                JOIN tbperfil USING (idPerfil)
                               WHERE YEAR(dtadmissao) <= "' . $ano . '" 
                                 AND ((dtdemissao IS null) OR (YEAR(dtdemissao) >= "' . $ano . '"))
                                 AND tbperfil.tipo <> "Outros" 
                                 AND tbtipocargo.tipo = "Adm/Tec"
                            GROUP BY tbtipocargo.cargo
                            ORDER BY 1 DESC ';

            $admTec = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($admTec, "jj"));

            # Exemplo de tabela simples
            $tabela = new Tabela();
            $tabela->set_titulo("Administrativos e Técnicos");
            $tabela->set_conteudo($admTec);
            $tabela->set_label(["Cargo", "Servidores"]);
            $tabela->set_width([80, 20]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_rodape("Total de Servidores: " . $total);
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->abreColuna(6);

            # Professores
            $selectGrafico = 'SELECT tbtipocargo.cargo, count(tbservidor.idServidor) as jj
                                FROM tbservidor JOIN tbcargo USING (idCargo)
                                                JOIN tbtipocargo USING (idTipoCargo)
                                                JOIN tbperfil USING (idPerfil)
                               WHERE YEAR(dtadmissao) <= "' . $ano . '" 
                                 AND ((dtdemissao IS null) OR (YEAR(dtdemissao) >= "' . $ano . '"))
                                 AND tbperfil.tipo <> "Outros" 
                                 AND tbtipocargo.tipo = "Professor"
                            GROUP BY tbtipocargo.cargo
                            ORDER BY 1 DESC ';

            $servidores = $pessoal->select($selectGrafico);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Exemplo de tabela simples
            $tabela = new Tabela();
            $tabela->set_titulo("Professores");
            $tabela->set_conteudo($servidores);
            $tabela->set_label(["Cargo", "Servidores"]);
            $tabela->set_width([80, 20]);
            $tabela->set_align(["left", "center"]);
            $tabela->set_rodape("Total de Servidores: " . $total);
            $tabela->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();

            hr();
            ############################################################################################

            $grid2 = new Grid();

            $cargo = array("Profissional de Nível Superior", "Profissional de Nível Médio", "Profissional de Nível Fundamental", "Profissional de Nível Elementar");

            foreach ($cargo as $valor) {
                $grid2->abreColuna(3);

                # exibe a tabela
                $selectGrafico = 'SELECT tbcargo.nome, count(tbservidor.idServidor) as jj
                                    FROM tbservidor JOIN tbcargo USING (idCargo)
                                                    JOIN tbtipocargo USING (idTipoCargo)
                                                    JOIN tbperfil USING (idPerfil)
                                   WHERE YEAR(dtadmissao) <= "' . $ano . '" 
                                     AND ((dtdemissao IS null) OR (YEAR(dtdemissao) >= "' . $ano . '"))
                                     AND tbtipocargo.cargo = "' . $valor . '"
                                     AND tbperfil.tipo <> "Outros" 
                                GROUP BY tbcargo.nome
                                ORDER BY 2 DESC ';

                $servidores = $pessoal->select($selectGrafico);

                # Soma a coluna do count
                $total = array_sum(array_column($servidores, "jj"));

                # Exemplo de tabela simples
                $tabela = new Tabela();
                $tabela->set_titulo($valor);
                $tabela->set_conteudo($servidores);
                $tabela->set_label(["Cargo", "Servidores"]);
                $tabela->set_width([80, 20]);
                $tabela->set_align(["left", "center"]);
                $tabela->set_rodape("Total de Servidores: " . $total);
                $tabela->show();

                $grid2->fechaColuna();
            }


            $grid2->fechaGrid();

            hr();
            break;

####################################################################################################            

        case "cargoGerencia":

            # Abre o painel
            $painel = new Callout();
            $painel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística por Diretoria/Gerência x Cargo/Função";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            titulotable("por Diretoria/Gerência x Cargo/Função");
            br();

            ########
            # Formulário de Pesquisa
            $form = new Form('?fase=cargoGerencia');

            # Lotação
            $result = $pessoal->select('SELECT DISTINCT DIR, DIR
                                      FROM tblotacao
                                     WHERE ativo
                                  ORDER BY DIR');
            array_unshift($result, array("*", 'Todas'));

            $controle = new Input('parametroLotacao', 'combo', 'Diretoria/Centro:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_autofocus(true);
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            # Perfil
            $result = $pessoal->select($selectPerfil);
            array_unshift($result, array("*", 'Todos'));
            
            $controle = new Input('parametroPerfil', 'combo', 'Perfil:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Perfil');
            $controle->set_array($result);
            $controle->set_valor($parametroPerfil);
            $controle->set_optgroup(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(6);
            $form->add_item($controle);

            $form->show();

            ########
            # Monta o select
            $select = 'SELECT CONCAT(IFnull(tblotacao.dir,"")," - ",IFnull(tblotacao.ger,"")) lotacao,                          
                          CONCAT(tbtipocargo.sigla," - ",tbcargo.nome) efetivo, 
                          count(tbservidor.idServidor) as jj
                    FROM tbservidor LEFT  JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                          JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                          JOIN tbcargo USING (idCargo)
                                          JOIN tbtipocargo USING (idTipoCargo)
                                          JOIN tbperfil USING (idPerfil)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbperfil.tipo <> "Outros" 
                     AND situacao = 1
                     AND ativo';

            if ($parametroLotacao <> '*') {
                $select .= ' AND tblotacao.dir="' . $parametroLotacao . '"';
            }

            if ($parametroPerfil <> '*') {
                $select .= ' AND tbservidor.idPerfil="' . $parametroPerfil . '"';
            }

            $select .= ' GROUP BY lotacao, efetivo
                     ORDER BY lotacao, efetivo, tbcargo.nome';
            #echo $select;
            $servidores = $pessoal->select($select);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_label(["Gerência / Laboratório", "Cargo Efetivo", "Nº de Servidores"]);
            $tabela->set_align(["left", "left"]);
            $tabela->set_rodape("Total de Servidores: " . $total);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $painel->fecha();
            break;

####################################################################################################            

        case "professorDiretoria":

            # Abre o painel
            $painel = new Callout();
            $painel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística de professores por Diretoria";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            titulotable("Professores por Diretoria");
            br();

            ########
            # Monta o select
            $select = 'SELECT tblotacao.dir,
                              CONCAT(tbtipocargo.sigla," - ",tbcargo.nome) efetivo, 
                              count(tbservidor.idServidor) as jj
                         FROM tbservidor LEFT  JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                               JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                               JOIN tbcargo USING (idCargo)
                                               JOIN tbtipocargo USING (idTipoCargo)
                                               JOIN tbperfil USING (idPerfil)
                   WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbperfil.tipo <> "Outros"  
                     AND situacao = 1
                     AND ativo
                     AND tbtipocargo.tipo = "Professor"
                GROUP BY tblotacao.dir, efetivo
                ORDER BY tblotacao.dir, efetivo, tbcargo.nome';
            #echo $select;
            $servidores = $pessoal->select($select);

            # Soma a coluna do count
            $total = array_sum(array_column($servidores, "jj"));

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($servidores);
            $tabela->set_label(["Diretoria", "Cargo", "Nº de Servidores"]);
            #$tabela->set_width(array(80,20));
            $tabela->set_align(["left", "left"]);
            $tabela->set_rodape("Total de Servidores: " . $total);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->show();

            $painel->fecha();
            break;

####################################################################################################                        

        case "filhos":

            # Abre o painel
            $painel = new Callout();
            $painel->abre();

            # Grava no log a atividade
            $atividade = "Visualizou a área de estatística por filhos";
            $intra->registraLog($idUsuario, date("Y-m-d H:i:s"), $atividade, null, null, 7);

            titulotable("Servidores Ativos");
            br();

            ########
            # Servidores ativos na Uenf
            $mulheres = $pessoal->get_numServidoresAtivosSexo("Feminino");
            $homens = $pessoal->get_numServidoresAtivosSexo("Masculino");

            # Servidores com filhos ativos na Uenf
            $maes = $pessoal->get_numServidoresAtivosSexoFilhos("Feminino");
            $pais = $pessoal->get_numServidoresAtivosSexoFilhos("Masculino");

            # Porcentagem
            $pmae = ($maes * 100) / $mulheres;
            $ppai = ($pais * 100) / $homens;

            # Tabela
            $tabela = new Tabela();
            $tabela->set_conteudo(array(
                ["Feminino", $mulheres, $maes, number_format($pmae, 2, ',', '') . " %"],
                ["Masculino", $homens, $pais, number_format($ppai, 2, ',', '') . " %"],
                ["Total", $mulheres + $homens, $pais + $maes, null]));

            $tabela->set_label(["Sexo", "Servidores", "Servidores Com Filhos", "Servidores Com Filhos (%)"]);
            $tabela->set_width([25, 25, 25, 25]);
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 0,
                    'valor' => "Total",
                    'operador' => '=',
                    'id' => 'estatisticaTotal')));
            $tabela->set_totalRegistro(false);
            $tabela->show();

            $painel->fecha();
            break;

####################################################################################################                
    }

    # Fecha o grid
    if (!$rel) {
        $grid1->fechaColuna();
        $grid1->fechaGrid();
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}