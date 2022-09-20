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
    $aposentadoria = new Aposentadoria();

    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou a área de aposentadoria";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', $aposentadoria->get_ultimoAnoAposentadoria()));

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    # Cria um menu
    $menu = new MenuBar();

    # Voltar
    $botaoVoltar = new Link("Voltar", "grh.php");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Voltar a página anterior');
    $botaoVoltar->set_accessKey('V');
    $menu->add_link($botaoVoltar, "left");
    $menu->show();

    $grid2 = new Grid();
    $grid2->abreColuna(12, 3);

    $aposentadoria->exibeMenu(9);

    # Exibe regras
    $permanente = new AposentadoriaCompulsoria();
    $permanente->exibeRegras();

    $grid2->fechaColuna();
    $grid2->abreColuna(12, 9);

    #######################################3
    switch ($fase) {
        case "":
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

        #######################################    

        case "lista" :

            # Idade obrigatória
            $idade = $intra->get_variavel("aposentadoria.compulsoria.idade");

            # Formulário de Pesquisa
            $form = new Form('?');

            # Cria um array com os anos possíveis
            $anos = arrayPreenche(date("Y") - 2, date("Y") + 20);

            $controle = new Input('parametroAno', 'combo');
            $controle->set_size(6);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anos);
            $controle->set_valor($parametroAno);
            $controle->set_autofocus(true);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            # Exibe a lista

            $select = "SELECT month(dtNasc),  
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              TIMESTAMPDIFF(YEAR,tbpessoa.dtNasc,CURDATE()),
                              ADDDATE(dtNasc, INTERVAL {$idade} YEAR),
                              tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                        WHERE tbservidor.situacao = 1
                          AND idPerfil = 1
                          AND ({$parametroAno} - YEAR(tbpessoa.dtNasc) = {$idade})                    
                     ORDER BY dtNasc";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);
            $titulo = "Servidores Estatutários Ativos que Fazem / Fizeram  {$idade} anos em {$parametroAno}";

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label(['Mês', 'Servidor', 'Lotação', "Idade", "Fará / Fez {$idade}"]);
            $tabela->set_align(['center', 'left', 'center', 'center', 'center']);
            $tabela->set_titulo($titulo);
            $tabela->set_classe([null, "Pessoal", "Pessoal"]);
            $tabela->set_metodo([null, "get_nomeECargo", "get_lotacao"]);
            $tabela->set_funcao(["get_nomeMes", null, null, null, "date_to_php"]);
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editar');

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 3,
                    'valor' => 74,
                    'operador' => '>',
                    'id' => 'indeferido')
            ));

            $tabela->show();

            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;

        #######################################    

        case "editar" :
            br(8);
            aguarde();

            # Informa o $id Servidor
            set_session('idServidorPesquisado', $id);

            # Informa a origem
            set_session('origem', 'areaAposentadoria_previsaoCompulsoriaPorAno.php');

            # Carrega a página específica
            loadPage('servidorMenu.php');
            break;
    }

    $grid2->fechaColuna();
    $grid2->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}