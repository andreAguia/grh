<?php

/**
 * Área de Progressão
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

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
        $atividade = "Visualizou a área de progressão e enquadramento";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros    
    $parametroCargo = post('parametroCargo', get_session('parametroCargo', 6));

    # Joga os parâmetros par as sessions   
    set_session('parametroCargo', $parametroCargo);

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

            # tabela
            $botao = new Button("Tabela", "tabelaSalarial.php");
            $botao->set_title("Exibe a tabela salarial do plano de cargos requisitado");
            $botao->set_target("_blank");
            $menu1->add_link($botao, "right");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/progressaoporcargo.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");

            $menu1->show();

            ##############
            # Pega os dados da combo cargo
            $result = $pessoal->select('SELECT idTipoCargo, 
                                               cargo
                                          FROM tbtipocargo
                                      ORDER BY idTipoCargo');

            # Formulário de Pesquisa
            $form = new Form('?');

            # Nivel do Cargo    
            $controle = new Input('parametroCargo', 'combo', 'Cargo:', 1);
            $controle->set_size(20);
            $controle->set_title('Cargo');
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(6);
            $controle->set_array($result);
            $controle->set_autofocus(true);
            $form->add_item($controle);

            $form->show();

            ##############
            # Pega os dados
            $select = 'SELECT tbservidor.idFuncional,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                LEFT JOIN tbperfil USING (idPerfil)
                                LEFT JOIN tbcargo USING (idCargo)                     
                                     JOIN tbtipocargo USING (idTipoCargo) 
               WHERE tbservidor.situacao = 1
                 AND (idPerfil = 1 OR idPerfil = 4)
                 AND tbtipocargo.idTipoCargo = "' . $parametroCargo . '"
            ORDER BY tbpessoa.nome';

            $result = $pessoal->select($select);

            $tabela = new Tabela();
            $tabela->set_titulo('Servidores Administrativos e Técnicos Ativos Com a Última Progressão / Enquadramento');
            $tabela->set_label(array('IdFuncional', 'Servidor', 'Salário Atual', 'Data Inicial', 'Análise'));
            $tabela->set_align(array("center", "left", "left"));
            $tabela->set_funcao(array(null, null, "exibeDadosSalarioAtual"));

            $tabela->set_classe(array(null, "pessoal", null, "Progressao", "Progressao"));
            $tabela->set_metodo(array(null, "get_nomeECargoELotacao", null, "get_dtInicialAtual", "analisaServidor"));

            $tabela->set_conteudo($result);

            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');

            $tabela->set_formatacaoCondicional(array(
                array('coluna'   => 4,
                    'valor'    => 'Tem Direito a Progressão por Antiguidade',
                    'operador' => '=',
                    'id'       => 'podeProgredir'),
                array('coluna'   => 4,
                    'valor'    => 'Aparentemente Tudo Certo',
                    'operador' => '=',
                    'id'       => 'naoEstaUltimo'),
                array('coluna'   => 4,
                    'valor'    => 'Plano ERRADO',
                    'operador' => '=',
                    'id'       => 'planoErrado'),
                array('coluna'   => 4,
                    'valor'    => 'Não Tem Salário Cadastrado',
                    'operador' => '=',
                    'id'       => 'planoErrado'),
                array('coluna'   => 4,
                    'valor'    => 'Não Pode Progredir',
                    'operador' => '=',
                    'id'       => 'tanofim')
            ));
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
            set_session('origem', 'areaProgressao.php');

            # Carrega a página específica
            loadPage('servidorProgressao.php');
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}


