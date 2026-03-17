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
    $petec = new Petec();

    # Verifica a fase do programa
    $fase = get('fase', "geral");
    $portaria = get('portaria', "geral");

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', 66));
    $parametroInscricao = post('parametroInscricao', get_session('parametroInscricao', "Inscritos"));
    $parametroSituacao = post('parametroSituacao', get_session('parametroSituacao', 'Pendentes'));
    $parametroMarcador = get('parametroMarcador', get_session('parametroMarcador', 0));

    # Joga os parâmetros par as sessions
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroInscricao', $parametroInscricao);
    set_session('parametroSituacao', $parametroSituacao);
    set_session('parametroMarcador', $parametroMarcador);

    # Label da Lotação
    if (is_numeric($parametroLotacao)) {
        $labelLotação = $pessoal->get_nomeLotacao2($parametroLotacao);
    } else { # senão é uma diretoria genérica
        $labelLotação = $parametroLotacao;
    }

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de Petec";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    } else {
        if ($fase == "geral" OR $fase == "481" OR $fase == "473" OR $fase == "418") {

            # Grava no log a atividade
            $atividade = "Pesquisou na área de Petec<br/>Lotação: {$labelLotação}<br/>Inscrição: {$parametroInscricao}<br/>Portaria: {$fase}";
            $data = date("Y-m-d H:i:s");
            $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
        }
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    $grid = new Grid();
    $grid->abreColuna(12);

    ################################################################
    # Cabeçalho da Página
    if ($fase <> "relatorio") {
        AreaServidor::cabecalho();

        br();

        # Cria um menu
        $menu1 = new MenuBar();

        # Voltar
        $botaoVoltar = new Link("Voltar", "grh.php");
        $botaoVoltar->set_class('button');
        $botaoVoltar->set_title('Voltar a página anterior');
        $botaoVoltar->set_accessKey('V');
        $menu1->add_link($botaoVoltar, "left");

        # Geral
        $botao1 = new Link("Geral", "?fase=geral&parametroMarcador=0");
        if ($parametroMarcador == 0) {
            $botao1->set_class('button');
        } else {
            $botao1->set_class('hollow button');
        }
        $menu1->add_link($botao1, "right");

        # Portaria 418/25
        $botao1 = new Link("Portaria 418/25", "?fase=exibeLista&parametroMarcador=4");
        if ($parametroMarcador == "4") {
            $botao1->set_class('button');
        } else {
            $botao1->set_class('hollow button');
        }
        $menu1->add_link($botao1, "right");

        # Portaria 473/25
        $botao1 = new Link("Portaria 473/25", "?fase=exibeLista&parametroMarcador=5");
        if ($parametroMarcador == 5) {
            $botao1->set_class('button');
        } else {
            $botao1->set_class('hollow button');
        }
        $menu1->add_link($botao1, "right");

        # Portaria 481/25
        $botao1 = new Link("Portaria 481/25", "?fase=exibeLista&parametroMarcador=6");
        if ($parametroMarcador == 6) {
            $botao1->set_class('button');
        } else {
            $botao1->set_class('hollow button');
        }
        $menu1->add_link($botao1, "right");

        # Importar
        $botaoImportar = new Link("Importar", "importaPetec.php");
        $botaoImportar->set_class('success button');
        $botaoImportar->set_title('Faz a importação do petec');
        if (Verifica::acesso($idUsuario, 1)) {
            $menu1->add_link($botaoImportar, "right");
        }

        # Relatórios
        $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
        $botaoRel = new Button();
        $botaoRel->set_title("Relatório dos servidores em situação irregular");
        $botaoRel->set_url("?fase=relatorio&parametroMarcador={$parametroMarcador}");
        $botaoRel->set_target("_blank");
        $botaoRel->set_imagem($imagem);

        if ($fase <> "geral") {
            if ($parametroSituacao == "Pendentes" AND $parametroInscricao == "Inscritos") {
                $menu1->add_link($botaoRel, "right");
            }
        }

        $menu1->show();

        # Formulário de Pesquisa
        if ($parametroMarcador == 0) {
            $form = new Form("?fase=geral");
        } else {
            $form = new Form("?fase=exibeLista");
        }

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

        $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
        $controle->set_size(30);
        $controle->set_title('Filtra por Lotação');
        $controle->set_array($result);
        $controle->set_valor($parametroLotacao);
        $controle->set_onChange('formPadrao.submit();');
        $controle->set_linha(1);
        if ($fase <> "geral") {
            $controle->set_col(6);
        } else {
            $controle->set_col(12);
        }
        $form->add_item($controle);

        /*
         *  Inscricão
         */

        $controle = new Input('parametroInscricao', 'combo', "Inscrição", 1);
        $controle->set_size(30);
        $controle->set_title('Filtra por Inscrição');
        $controle->set_array(["Todos", "Inscritos", "NÃO Inscritos"]);
        $controle->set_valor($parametroInscricao);
        $controle->set_onChange('formPadrao.submit();');
        $controle->set_linha(1);
        $controle->set_col(3);

        if ($fase <> "geral") {
            $form->add_item($controle);
        }

        /*
         *  Situação
         */

        $controle = new Input('parametroSituacao', 'combo', "Situação", 1);
        $controle->set_size(30);
        $controle->set_title('Filtra por Situação');
        $controle->set_array(["Pendentes", "Regulares"]);
        $controle->set_valor($parametroSituacao);
        $controle->set_onChange('formPadrao.submit();');
        $controle->set_linha(1);
        $controle->set_col(3);

        if ($fase <> "geral") {
            $form->add_item($controle);
        }

        $form->show();

        # Link para editar o servidor
        $linkservidor = "?fase=editaServidor&portaria={$fase}";
    }

    switch ($fase) {

        #######################################################

        /*
         * Geral
         */

        case "geral" :

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=geral2');
            break;

        ################################################################

        case "geral2" :

            $grid->fechaColuna();

            $grid->abreColuna(12, 12, 3);

            # Quadro de Inscritos
            $petec->exibeQuadroInscritosPetec($parametroLotacao);

            $grid->fechaColuna();

            ##############

            $grid->abreColuna(12, 12, 9);

            # Quadro das Portarias
            $petec->exibeQuadroPortariasPetec2();

            # Monta o select
            $select = "SELECT tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND situacao = 1";

            # Verifica se tem filtro por lotação
            if ($parametroLotacao <> "Todos") {  // senão verifica o da classe
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            $select .= " ORDER BY tbpessoa.nome";

            $result2 = $pessoal->select($select);

            # Define as colunas
            $label[] = "Servidores";
            $align[] = "left";
            $classe[] = "Pessoal";
            $metodo[] = "get_nomeECargoELotacaoEPerfilESituacao";
            $width[] = 30;

            $petecArray = $formacao->get_arrayMarcadores("Petec");

            foreach ($petecArray as $item) {
                $label[] = $item[1];
                $align[] = "center";
                $classe[] = "Petec";
                $metodo[] = "somatorioHoras{$item[0]}"; // Gambiarra para fazer funcionar. Depois eu vejo um modo melhor de fazer isso...
                $width[] = 20;
            }

            $label[] = "Editar";

            $tabela = new Tabela();
            $tabela->set_titulo("Análise Da Entrega de Certificados - Geral");
            $tabela->set_subtitulo($labelLotação);
            $tabela->set_conteudo($result2);

            $tabela->set_label($label);
            $tabela->set_align($align);
            $tabela->set_width($width);

            $tabela->set_classe($classe);
            $tabela->set_metodo($metodo);
            $tabela->set_bordaInterna(true);

            # Botão Editar
            $botao = new Link(null, "{$linkservidor}&id=", 'Acessa o servidor');
            $botao->set_imagem(PASTA_FIGURAS . 'bullet_edit.png', 20, 20);

            # Coloca o objeto link na tabela			
            $tabela->set_link([null, null, null, null, $botao]);
            $tabela->show();

            break;

        ##############################################################################################################
        /*
         * Exibe a lista
         */

        case "exibeLista" :

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=exibeLista2');
            break;

        ################################################################

        case "exibeLista2" :


            # Quadro de Quantidades
            $listaPetec = new ListaPetec($parametroMarcador, $parametroLotacao, $parametroInscricao, $linkservidor);
            $listaPetec->exibeTituloGeral();
            br();

            $grid->fechaColuna();
            $grid->abreColuna(12, 12, 3);

            # Dados da Portaria
            #$petec->exibeDadosPortaria2($idMarcador);
            $listaPetec->exibeQuadroQuantidades();

            $grid->fechaColuna();

            ##############

            $grid->abreColuna(12, 12, 9);

            if ($parametroSituacao == "Pendentes") {

                # Não Entregaram Certificado    
                $listaPetec->exibeNaoEntregaram();

                # Horas Insuficientes
                $listaPetec->exibeHorasInsuficientes();
            } else {

                # Situação Regular
                $listaPetec->exibeSituacaoRegular();
            }
            break;

        ##############################################################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            if ($parametroMarcador == 0) {
                set_session('origem', "areaPetec.php?fase=geral");
            } else {
                set_session('origem', "areaPetec.php?fase=exibeLista");
            }


            

            # Carrega a página específica
            loadPage('servidorFormacao.php');
            break;

        ################################################################
        # Relatório
        case "relatorio" :

            # Título            
            $listaPetec = new ListaPetec($parametroMarcador, $parametroLotacao, $parametroInscricao, null, true);

            # Não Entregaram Certificado            
            $listaPetec->exibeNaoEntregaram();

            # Horas Insuficientes
            $listaPetec->exibeHorasInsuficientes();
            break;
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


