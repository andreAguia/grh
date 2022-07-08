<?php

/**
 * Área de Aposentadoria
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
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', date('Y')));
    $parametroMes = post('parametroMes', get_session('parametroMes', date('m')));
    $parametroNome = post('parametroNome', retiraAspas(get_session('parametroNome')));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', 66));

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);
    set_session('parametroMes', $parametroMes);
    set_session('parametroNome', $parametroNome);
    set_session('parametroLotacao', $parametroLotacao);

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de auxílio transporte";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

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

            # Cria um menu
            $menu = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu->add_link($botaoVoltar, "left");

            $menu->show();

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

            array_unshift($result, array(null, "Todos"));

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(5);
            $form->add_item($controle);

            # Cria um array com os anos possíveis
            $anoInicial = 1999;
            $anoAtual = date('Y');
            $anoExercicio = arrayPreenche($anoInicial, $anoAtual, "d");

            $controle = new Input('parametroAno', 'combo', 'Ano:', 1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anoExercicio);
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $controle = new Input('parametroMes', 'combo', 'Mês:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra pelo Mês');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            ################################################################
            # Pega os dados
            $select = "SELECT tbservidor.idfuncional,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              CONCAT(tbservidor.idServidor,'-','{$parametroMes}','-','{$parametroAno}')
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao) 
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND situacao = 1";

            # Pesquisa por nome
            if (!empty($parametroNome)) {
                $select .= " AND tbpessoa.nome LIKE '%{$parametroNome}%'";
            }

            # lotacao
            if (!empty($parametroLotacao)) {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= " AND tblotacao.idlotacao = {$parametroLotacao}";
                } else { # senão é uma diretoria genérica
                    $select .= " AND tblotacao.DIR = '{$parametroLotacao}'";
                }
            }

            $select .= " ORDER BY tbpessoa.nome";

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Área de Auxílio Transporte');
            $tabela->set_label(["IdFuncional", "Servidor", "Lotação", "Situação"]);
            $tabela->set_width([10, 30, 30, 30]);
            $tabela->set_conteudo($result);
            $tabela->set_align(["center", "left", "left", "left"]);
            $tabela->set_classe([null, "pessoal", "pessoal"]);
            $tabela->set_metodo([null, "get_nomeECargoEPerfil", "get_lotacao"]);
            $tabela->set_funcao([null, null, null, "exibeSituacaoAuxilioTransporte"]);
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');
            $tabela->show();
            break;

        ################################################################
        # Chama o menu do Servidor que se quer editar
        case "editaServidor" :

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAuxilioTransporte.php');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;

        ################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


