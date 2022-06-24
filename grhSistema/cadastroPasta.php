<?php

/**
 * Controle de Pastas Funcionais
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
        $atividade = "Visualizou o controle de pastas funcionanais";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros    
    $parametroNome = retiraAspas(post('parametroNome', get_session('parametroNome')));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));
    $parametroSituacao = retiraAspas(post('parametroSituacao', get_session('parametroSituacao', true)));
    $parametroPasta = retiraAspas(post('parametroPasta', get_session('parametroPasta', "TD")));

    # Joga os parâmetros par as sessions
    set_session('parametroNome', $parametroNome);
    set_session('parametroLotacao', $parametroLotacao);
    set_session('parametroSituacao', $parametroSituacao);
    set_session('parametroPasta', $parametroPasta);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Limita a Tela 
    $grid = new Grid();
    $grid->abreColuna(12);
    br();

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

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            ###
            # Formulário de Pesquisa
            $form = new Form('?');

            # Nome    
            $controle = new Input('parametroNome', 'texto', 'Nome:', 1);
            $controle->set_size(100);
            $controle->set_title('Filtra por Nome');
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

            # Situação
            $situacao = array(
                array(1, "Ativo"),
                array(2, "Não Ativo")
            );

            $controle = new Input('parametroSituacao', 'combo', 'Situação:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Situação');
            $controle->set_array($situacao);
            $controle->set_valor($parametroSituacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            #Com ou sem pasta
            $pastas = array(array("TD", "Todas"),
                array("CP", "Com pasta"),
                array("SP", "Sem Pasta"));

            $controle = new Input('parametroPasta', 'combo', 'Pastas:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Pastas');
            $controle->set_array($pastas);
            $controle->set_valor($parametroPasta);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            ###
            # Pega o time inicial
            $time_start = microtime(true);

            # Se for todos
            if ($parametroPasta == "TD") {
                $select = "SELECT idServidor,
                              CASE tbpasta.tipo
                                   WHEN 1 THEN 'Documento' 
                                   WHEN 2 THEN 'Processo'
                              END,
                              tbpasta.descricao,
                              tbpasta.idPasta,
                              tbpasta.idPasta
                         FROM tbpasta right JOIN tbservidor USING (idServidor)
                                            JOIN tbpessoa USING (idPessoa)                                            
                                            JOIN tbhistlot USING (idServidor)
                                            JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";
            }

            # Se for SEM pasta
            if ($parametroPasta == "CP") {
                $select = "SELECT idServidor,
                                  CASE tbpasta.tipo
                                       WHEN 1 THEN 'Documento' 
                                       WHEN 2 THEN 'Processo'
                                  END,
                                  tbpasta.descricao,
                                  tbpasta.idPasta,
                                  tbpasta.idPasta
                             FROM tbpasta JOIN tbservidor USING (idServidor)
                                          JOIN tbpessoa USING (idPessoa)
                                          JOIN tbhistlot USING (idServidor)
                                          JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                            WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";
            }

            # Se for SEM pasta
            if ($parametroPasta == "SP") {
                $select = "SELECT idServidor,
                                  null,
                                  null,
                                  null,
                                  null
                             FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                             JOIN tbhistlot USING (idServidor)
                                             JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                            WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                              AND idServidor NOT IN (Select idServidor FROM tbpasta)";
            }

            # nome
            if (!vazio($parametroNome)) {
                $select .= " AND tbpessoa.nome LIKE '%{$parametroNome}%'";
            }

            # Lotação
            if (($parametroLotacao <> "*") AND ($parametroLotacao <> "")) {
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND (tblotacao.idlotacao = '{$parametroLotacao}')";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
                }
            }

            # Situação
            if ($parametroSituacao == 1) {
                $select .= " AND tbservidor.situacao = 1";
            } else {
                $select .= " AND tbservidor.situacao <> 1";
            }

            $select .= " ORDER BY tbpessoa.nome";

            $resumo = $pessoal->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Servidor", "Tipo", "Descrição", "Ver"));
            $tabela->set_align(array("left", "left", "left"));
            $tabela->set_width(array(30, 10, 50, 5));

            $tabela->set_classe(array("Pessoal", null, null, "PastaFuncional"));
            $tabela->set_metodo(array("get_nomeECargoELotacao", null, null, "exibePasta"));

            $tabela->set_titulo("Controle de Pasta Funcional");

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            if (!vazio($parametroNome)) {
                $tabela->set_textoRessaltado($parametroNome);
            }

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');
            $tabela->show();

            # Pega o time final
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            p(number_format($time, 4, '.', ',') . " segundos", "right", "f10");
            break;

        ################################################################

        case "editaServidor" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'cadastroPasta.php');

            # Carrega a página específica
            loadPage('servidorPasta.php?fase=listar');
            break;

        ################################################################
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


