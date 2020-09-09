<?php

/**
 * Cadastro de Banco
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
    $vaga = new Vaga();
    $concurso = new Concurso();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de vagas de docentes";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega os parâmetros
    $parametroCentro = get('parametroCentro', get_session('parametroCentro'));
    $parametroStatus = get('parametroStatus', get_session('parametroStatus'));
    $parametroNumero = soNumeros(post('parametroNumero'));

    if ($parametroCentro == "Todos") {
        $parametroCentro = null;
    }

    # Joga os parâmetros par as sessions    
    set_session('parametroCentro', $parametroCentro);
    set_session('parametroStatus', $parametroStatus);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo

    if (!vazio($parametroCentro)) {
        $objeto->set_nome("Vagas de Docentes do $parametroCentro");
    } else {
        $objeto->set_nome("Vagas de Docentes");
    }

    # select do edita
    $objeto->set_selectEdita('SELECT centro,
                                     idCargo
                                FROM tbvaga
                               WHERE idVaga = ' . $id);

    # Caminhos
    #$objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');
    #$objeto->set_linkExcluir('?fase=excluir');
    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbvaga');

    # Nome do campo id
    $objeto->set_idCampo('idVaga');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo cargo
    $cargo = $pessoal->select('SELECT idcargo,nome
                                 FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                                              LEFT JOIN tbarea USING (idarea)
                                WHERE idCargo = 128 OR idCargo = 129              
                             ORDER BY tbtipocargo.cargo,tbarea.area,nome');

    array_unshift($cargo, array(0, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'col' => 2,
            'nome' => 'centro',
            'label' => 'Centro:',
            'tipo' => 'combo',
            'array' => array(null, "CCT", "CCTA", "CCH", "CBB"),
            'required' => true,
            'autofocus' => true,
            'size' => 30),
        array('linha' => 1,
            'col' => 4,
            'nome' => 'idCargo',
            'label' => 'Cargo:',
            'tipo' => 'combo',
            'array' => $cargo,
            'required' => true,
            'size' => 30)));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    $objeto->set_botaoVoltarLista(false);
    $objeto->set_botaoIncluir(false);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Incluir
            $botaoInserir = new Button("Incluir Nova Vaga", "?fase=incluir");
            $botaoInserir->set_title("Incluir");
            $menu1->add_link($botaoInserir, "right");

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/acumulacao.geral.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            #$menu1->add_link($botaoRel,"right");

            $menu1->show();

            ###

            $grid->fechaColuna();
            $grid->abreColuna(3);

            $vaga->menu($parametroCentro);

            $grid->fechaColuna();
            $grid->abreColuna(9);

            br(5);
            aguarde();
            br();
            p("Aguarde...", "center");

            $grid->fechaColuna();
            $grid->fechaGrid();

            loadPage('?fase=exibeLista');
            break;

################################################################

        case "exibeLista" :

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            # Incluir
            $botaoInserir = new Button("Incluir Nova Vaga", "?fase=incluir");
            $botaoInserir->set_title("Incluir");
            $menu1->add_link($botaoInserir, "right");

            # Total de Vagas
            $botaoInserir = new Button("Exibe Total de Vagas", "?fase=exibeTotal");
            $botaoInserir->set_title("Incluir");
            #$menu1->add_link($botaoInserir,"right");
            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/acumulacao.geral.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            #$menu1->add_link($botaoRel,"right");

            $menu1->show();

            ###

            $grid->fechaColuna();
            $grid->abreColuna(3);

            # Campo de Pesquisa
            $form = new Form('?fase=editarVagaPorNumero');

            $controle = new Input("parametroNumero", "texto");
            $controle->set_size(10);
            $controle->set_placeholder("Número da Vaga");
            $controle->set_autofocus(true);
            #$controle->set_valor($parametroNumero);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_col(12);
            $form->add_item($controle);
            $form->show();

            $vaga->menu($parametroCentro);

            if (!vazio($parametroCentro)) {
                $vaga->exibeVagasDisponiveis($parametroCentro);
                $vaga->exibeVagasOcupadas($parametroCentro);
            }

            $grid->fechaColuna();
            $grid->abreColuna(9);

            #$objeto->listar();
            # Só exibe se tiver sido escolhido um centro
            if (!vazio($parametroCentro)) {

                # Exibe as tabs
                echo '<ul class="tabs" data-tabs id="example-tabs">';
                echo '<li class="tabs-title is-active"><a href="#disponiveis" aria-selected="true">Disponíveis</a></li>';
                echo '<li class="tabs-title"><a data-tabs-target="ocupadas" href="#ocupadas">Ocupadas</a></li>';

                echo '</ul>';

                $select = "SELECT idVaga,
                                  centro,
                                  tbcargo.nome,
                                  idVaga,
                                  idVaga,
                                  idVaga,
                                  idVaga,
                                  idVaga,
                                  idVaga,
                                  idVaga,
                                  idVaga
                             FROM tbvaga LEFT JOIN tbcargo USING (idCargo)
                            WHERE true 
                              AND centro = '$parametroCentro' 
                         ORDER BY centro,idCargo desc";

                $result = $pessoal->select($select);

                # Inicia o array para a tabela
                $arrayDisponível = array();
                $arrayOcupado = array();

                # Percorre o array retirando as vagas ocupadas
                foreach ($result as $rr) {

                    # Pega o status da vaga
                    $status = $vaga->get_status($rr[3]);

                    if ($status == "Disponível") {
                        $arrayDisponível[] = $rr;
                    } else {
                        $arrayOcupado[] = $rr;
                    }
                }

                #####

                echo '<div class="tabs-content" data-tabs-content="example-tabs">';

                # Vagas Disponíveis
                $div1 = new Div('disponiveis', 'tabs-panel is-active');
                $div1->abre();

                $tabela = new Tabela();

                # Titulo
                if (!vazio($parametroCentro)) {
                    $titulo = "Vagas de Docentes do $parametroCentro";
                } else {
                    $titulo = "Vagas de Docentes";
                }

                $tabela->set_titulo($titulo . " - Disponíveis");
                $tabela->set_conteudo($arrayDisponível);

                $tabela->set_label(array("Vaga", "Centro", "Cargo", "Status", "Lab.Origem", "Problemas", "Último Ocupante", "Obs", "Num. de Concursos", "Editar"));
                #$tabela->set_width(array(5,10,20,10,30,25));
                $tabela->set_align(array("center"));

                $tabela->set_classe(array(null, null, null, "Vaga", "Vaga", "Vaga", "Vaga", "Vaga", "Vaga"));
                $tabela->set_metodo(array(null, null, null, "get_status", "get_nomeLaboratorioOrigem", "temProblema", "get_servidorOcupante", "get_obsOcupante", "get_numConcursoVaga"));

                $tabela->set_formatacaoCondicional(array(array('coluna' => 3,
                        'valor' => 'Disponível',
                        'operador' => '=',
                        'id' => 'emAberto'),
                    array('coluna' => 3,
                        'valor' => 'Ocupada',
                        'operador' => '=',
                        'id' => 'alerta')
                ));

                $tabela->set_excluirCondicional('?fase=excluir', 0, 8, "==");

                # Botão de Editar concursos
                $botao1 = new BotaoGrafico();
                $botao1->set_label('');
                $botao1->set_title('Editar o Concurso');
                $botao1->set_url("?fase=editarConcurso&id=");
                $botao1->set_imagem(PASTA_FIGURAS . 'ver.png', 20, 20);

                # Coloca o objeto link na tabela			
                $tabela->set_link(array(null, null, null, null, null, null, null, null, null, $botao1));

                #$tabela->set_numeroOrdem(true);
                $tabela->set_idCampo('idVaga');
                $tabela->show();

                $div1->fecha();

                #####
                # Vagas Ocupadas
                $div2 = new Div('ocupadas', 'tabs-panel');
                $div2->abre();

                $tabela = new Tabela();

                # Titulo
                if (!vazio($parametroCentro)) {
                    $titulo = "Vagas de Docentes do $parametroCentro";
                } else {
                    $titulo = "Vagas de Docentes";
                }

                $tabela->set_titulo($titulo . " - Ocupadas");
                $tabela->set_conteudo($arrayOcupado);

                $tabela->set_label(array("Vaga", "Centro", "Cargo", "Status", "Lab.Origem", "Problemas", "Último Ocupante", "Obs", "Num. de Concursos", "Editar"));
                #$tabela->set_width(array(5,10,20,10,30,25));
                $tabela->set_align(array("center"));

                $tabela->set_classe(array(null, null, null, "Vaga", "Vaga", "Vaga", "Vaga", "Vaga", "Vaga"));
                $tabela->set_metodo(array(null, null, null, "get_status", "get_nomeLaboratorioOrigem", "temProblema", "get_servidorOcupante", "get_obsOcupante", "get_numConcursoVaga"));

                $tabela->set_formatacaoCondicional(array(array('coluna' => 3,
                        'valor' => 'Disponível',
                        'operador' => '=',
                        'id' => 'emAberto'),
                    array('coluna' => 3,
                        'valor' => 'Ocupada',
                        'operador' => '=',
                        'id' => 'alerta')
                ));

                $tabela->set_excluirCondicional('?fase=excluir', 0, 8, "==");

                # Botão de Editar concursos
                $botao1 = new BotaoGrafico();
                $botao1->set_label('');
                $botao1->set_title('Editar o Concurso');
                $botao1->set_url("?fase=editarConcurso&id=");
                $botao1->set_imagem(PASTA_FIGURAS . 'ver.png', 20, 20);

                # Coloca o objeto link na tabela			
                $tabela->set_link(array(null, null, null, null, null, null, null, null, null, $botao1));

                #$tabela->set_numeroOrdem(true);            
                $tabela->set_idCampo('idVaga');
                $tabela->show();

                $div2->fecha();

                echo '</div>';
            } else {
                $vaga->exibeDashboard();
            }

            #####

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
            
        case "editarVagaPorNumero" :
            set_session('idVaga', $parametroNumero);
            loadPage("cadastroVagaHistorico.php");
            break;    

        case "editarConcurso" :
            set_session('idVaga', $id);
            loadPage("cadastroVagaHistorico.php");
            break;

        case "editar" :
            #$objeto->set_linkListar("?fase=editarConcurso&id=".$id);
            $objeto->set_voltarForm("?fase=editarConcurso&id=" . $id);
            $objeto->editar($id);
            break;

        case "incluir" :
            $objeto->editar();
            break;

        case "excluir" :
        case "gravar" :
            $objeto->$fase($id);
            break;

        #############################################################
        ## DEPRECATED
        ############################################333333333333

        case "exibeTotal" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            ###

            tituloTable("Total de Vagas");
            br();

            ###

            $grid->fechaColuna();
            $grid->abreColuna(4);

            $vaga->exibeTotalVagas($parametroCentro, "o");

            $grid->fechaColuna();
            $grid->abreColuna(4);

            $vaga->exibeTotalVagas($parametroCentro, "d");

            $grid->fechaColuna();
            $grid->abreColuna(4);

            $vaga->exibeTotalVagas($parametroCentro);

            $grid->fechaColuna();
            $grid->fechaGrid();

            br();

            $grid = new Grid("center");
            $grid->abreColuna(6);

            $concurso->exibeQuadroDocentesSemConcurso();

            $grid->fechaColuna();
            $grid->fechaGrid();
            hr();
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}