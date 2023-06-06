<?php

/**
 * Cadastro de Plano de Cargos e Salários
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
    $plano = new PlanoCargos();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de plano de cargos e vencimentos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id', get_session('idPlano')));
    set_session('idPlano', $id);

    # Pega o parametro de pesquisa (se tiver)
    $parametro = post('parametro', retiraAspas(get_session('sessionParametro')));
    set_session('sessionParametro', $parametro);

    # Começa uma nova página
    $page = new Page();
    if ($fase == "upload") {
        $page->set_ready('$(document).ready(function(){
                                $("form input").change(function(){
                                    $("form p").text(this.files.length + " arquivo(s) selecionado");
                                });
                            });');
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "exibeRelatorio") {
        AreaServidor::cabecalho();
    }

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Plano de Cargos & Vencimentos');

    # bot?o de voltar da lista
    $objeto->set_voltarLista('grh.php');

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista("SELECT idPlano,
                                      numDecreto,
                                      servidores,
                                      dtDecreto,
                                      dtPublicacao,
                                      dtVigencia,
                                      CASE planoAtual
                                            WHEN 1 THEN 'Vigente'
                                            ELSE 'Antigo'
                                       end,
                                      idPlano,
                                      idPlano,
                                      idPlano
                                 FROM tbplano
                                WHERE numDecreto LIKE '%{$parametro}%'
                                   OR idPlano LIKE '%{$parametro}%'
                             ORDER BY planoAtual desc, dtPublicacao desc, numDecreto desc");

    # select do edita
    $objeto->set_selectEdita("SELECT numDecreto,
                                     servidores,
                                     planoAtual,
                                     dtDecreto,
                                     dtPublicacao,
                                     dtVigencia,
                                     link,
                                     obs
                                FROM tbplano
                               WHERE idPlano = {$id}");

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Dá acesso a exclusão somente ao administrador
    if (Verifica::acesso($idUsuario, 1)) {
        $objeto->set_linkExcluir('?fase=excluir');
        $objeto->set_label(["id", "Decreto / Lei", "Servidores", "Data do Decreto / Lei", "Publicação no DOERJ", "Data da Vigência", "Plano Atual", "DO", "Tabela", "Gerenciar"]);
    } else {
        $objeto->set_label(["id", "Decreto / Lei", "Servidores", "Data do Decreto / Lei", "Publicação no DOERJ", "Data da Vigência", "Plano Atual", "DO", "Tabela"]);
    }

    # Parametros da tabela    
    $objeto->set_align(["center", "left"]);
    $objeto->set_funcao([null, null, null, "date_to_php", "date_to_php", "date_to_php"]);
    $objeto->set_classe([null, null, null, null, null, null, null, 'PlanoCargos', 'PlanoCargos']);
    $objeto->set_metodo([null, null, null, null, null, null, null, 'exibeLei', 'exibeBotaoTabela']);

    # Botão de Gerenciar Tabela (somente admin)
    if (Verifica::acesso($idUsuario, 1)) {
        $link = new Link(null, '?fase=gerenciaTabela&id=', 'Gerencia Tabela');
        $link->set_imagem(PASTA_FIGURAS . 'gerenciar.png', 20, 20);
        $objeto->set_link([null, null, null, null, null, null, null, null, null, $link]);
    }

    $objeto->set_formatacaoCondicional(array(
        array('coluna' => 6,
            'valor' => "Antigo",
            'operador' => '=',
            'id' => 'inativo')));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbplano');

    # Nome do campo id
    $objeto->set_idCampo('idPlano');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'col' => 6,
            'nome' => 'numDecreto',
            'label' => 'Decreto ou Lei:',
            'title' => 'Número do Decreto',
            'tipo' => 'texto',
            'required' => true,
            'autofocus' => true,
            'size' => 30),
        array('linha' => 1,
            'nome' => 'servidores',
            'col' => 3,
            'label' => 'Servidores:',
            'title' => 'O plano se refere a qual tipo de servidor.',
            'tipo' => 'combo',
            'array' => array(null, "Todos", "Adm/Tec", "Professor"),
            'padrao' => 'Sim',
            'size' => 10),
        array('linha' => 1,
            'nome' => 'planoAtual',
            'col' => 3,
            'label' => 'Plano atual:',
            'title' => 'Se é o Plano de Cargos atualmente ativo',
            'tipo' => 'combo',
            'array' => array(array('1', 'Sim'), array(null, 'Não')),
            'padrao' => 'Sim',
            'size' => 10),
        array('linha' => 2,
            'col' => 4,
            'nome' => 'dtDecreto',
            'label' => 'Data do Decreto:',
            'title' => 'Data do decreto',
            'tipo' => 'data',
            'required' => true,
            'size' => 15),
        array('linha' => 2,
            'nome' => 'dtPublicacao',
            'col' => 4,
            'label' => 'Data da Publicação:',
            'title' => 'Data da Publicação no DOERJ',
            'tipo' => 'data',
            'required' => true,
            'size' => 15),
        array('linha' => 2,
            'nome' => 'dtVigencia',
            'col' => 4,
            'label' => 'Data da Vigência:',
            'title' => 'Data em que o plano passou a vigorar',
            'tipo' => 'data',
            'required' => true,
            'size' => 15),
        array('linha' => 3,
            'col' => 12,
            'nome' => 'link',
            'label' => 'Nome do arquivo da Lei:',
            'title' => 'texto do Decreto',
            'tipo' => 'texto',
            'bloqueadoEsconde' => true,
            'size' => 250),
        array('linha' => 4,
            'col' => 12,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5))));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);
    
    # Dados da rotina de Upload
    $pasta = PASTA_PLANOCARGOS;
    $nome = "Publicação do Plano de Cargos";
    $tabela = "tbplano";
    $extensoes = ["pdf"];

    # Botão de Upload
    if (!empty($id)) {

        # Botão de Upload
        $botao = new Button("Upload {$nome}");
        $botao->set_url("?fase=upload&id={$id}");
        $botao->set_title("Faz o Upload do {$nome}");
        $botao->set_target("_blank");

        $objeto->set_botaoEditarExtra([$botao]);
    }

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        ################################################################

        case "editar" :
            $objeto->editar($id);
            break;

        ################################################################

        case "exibeTabela" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Imprimir", "?fase=exibeRelatorio&id=" . $id);
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Exibe o relatório desta tabela');
            $menu1->add_link($botaoVoltar, "right");

            # Somente Admin edita
            if (Verifica::acesso($idUsuario, 1)) {
                # Editar
                $botaoVoltar = new Link("Editar", "cadastroTabelaSalarial.php");
                $botaoVoltar->set_class('button');
                $botaoVoltar->set_title('Edita os valores da tabela');
                $menu1->add_link($botaoVoltar, "right");
            }

            $menu1->show();

            $plano->exibeTabela($id, false);

            # guarda na sessio o plano caso deseje editar
            set_session('parametroPlano', $id);

            if ($plano->get_numDadosPlano($id) == 0) {
                $painel = new Callout();
                $painel->abre();

                p("Não há dados a serem exibidos", "center");

                $painel->fecha();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "exibeRelatorio" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            $plano->exibetabela($id, true);

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "gravar" :

            $objeto->$fase($id);
            break;

        ################################################################

        case "excluir" :
            # Verifica se este plano tem algum salário cadastrado nele
            $classe = new Classe();
            if ($classe->get_numSalarios($id) == 0) {
                $objeto->excluir($id);
            } else {
                alert('Este pĺano de cargos tem salário cadastrados.\nNão é possível excluí-lo');
                back(1);
            }
            break;

        ################################################################

        case "gerenciaTabela" :

            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Grava no log a atividade
            $atividade = "Acessou a área da gerência da tabela do plano de cargos {$plano->get_numDecreto($id)}";
            $data = date("Y-m-d H:i:s");
            $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Volta para a tela inicial');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            # Exibe dados do plano
            $plano->exibeDadosPlano($id);
            br();

            $grid = new Grid("center");
            $grid->abreColuna(8);

            tituloTable('Gerenciar Tabela de Plano de Cargos');
            $painel = new Callout();
            $painel->abre();
            br();

            $menu = new MenuGrafico(4);

            $botao = new BotaoGrafico();
            $botao->set_label('Apagar os Dados da Tabela');
            $botao->set_url("?fase=apagaDados");
            $botao->set_imagem(PASTA_FIGURAS . 'apagar.png', 50, 50);
            $botao->set_title('Apaga os dados da Tabela');
            #$botao->set_confirma('Realmente Deseja Apagar os Dados da Tabela?\nEsta Ação NÂO poderá ser desfeita!');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Ver Tabela');
            $botao->set_url("?fase=exibeTabela");
            $botao->set_imagem(PASTA_FIGURAS . "tabela.png", 50, 50);
            $botao->set_title('Exibe a tabela de valores');
            $botao->set_target("_blank");
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Preencher Tabela com Valores Resjustados de Outra (%)');
            $botao->set_url("?fase=porcentagem");
            $botao->set_imagem(PASTA_FIGURAS . 'porcentagem.png', 50, 50);
            $botao->set_title('Preenche uma tabela vazia com os valores reajustados de outra tabela');
            $menu->add_item($botao);

            $botao = new BotaoGrafico();
            $botao->set_label('Importar Servidores para Esta Tabela');
            $botao->set_url("importaPlanoCargos.php");
            $botao->set_imagem(PASTA_FIGURAS . 'importaSalario.png', 50, 50);
            $botao->set_title('Faz a atualização automática dos servidores ativos para esta tabela');
            $menu->add_item($botao);

            $menu->show();
            $painel->fecha();

            $grid->fechaColuna();
            $grid->fechaGrid();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "apagaDados" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?fase=gerenciaTabela");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Volta para a tela inicial');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            # Exibe dados do plano
            $plano->exibeDadosPlano($id);
            br();

            $grid = new Grid("center");
            $grid->abreColuna(8);

            # Verifica se a tabela já não está vazia
            $select = "SELECT idClasse 
                         FROM tbclasse
                         WHERE idPlano = {$id}";

            if ($pessoal->count($select) > 0) {

                # Verifica se existe algum servidor vinculado a essa tabela
                $select = "SELECT idProgressao 
                             FROM tbprogressao JOIN tbclasse USING (idClasse)
                            WHERE idPlano = {$id}";

                if ($pessoal->count($select) > 0) {
                    tituloTable('Os Dados Desta Tabela Não Poderão Ser Apagados');
                    $painel = new Callout("warning");
                    $painel->abre();
                    br();

                    p("Existem {$pessoal->count($select)} registros de servidores vinculados a esse plano de cargos.<br/>Os dados não poderão ser Apagados.", "center");
                    br();

                    $painel->fecha();
                } else {

                    tituloTable('Os Dados Desta Tabela Podem Ser Apagados');
                    $painel = new Callout("warning");
                    $painel->abre();
                    br();

                    p("O Sistema verificou que não existe nenhum registro de servidor vinculado a este plano de cargos.", "center");
                    p("Ele pode ser apagado, mas lembre-se que esta ação não poderá ser desfeita !!", "center");
                    p("Se deseja realmente apagar os dados desta tabela clique em prosseguir", "center");
                    br();

                    # Cria um menu
                    $menu1 = new MenuBar();

                    # Prosseguir
                    $botaoVoltar = new Link("Prosseguir", "?fase=apagaDados2");
                    $botaoVoltar->set_class('button');
                    $botaoVoltar->set_title('Confirma e pressegue');
                    $menu1->add_link($botaoVoltar, "right");

                    $menu1->show();

                    $painel->fecha();
                }
            } else {
                tituloTable('Essa Tabela Já Está Vazia');
                $painel = new Callout("warning");
                $painel->abre();
                br();

                p("Essa tabela já se encontra sem nenhum dado.", "center");
                br();

                $painel->fecha();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "apagaDados2":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Apagando ...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=apagaDados3');
            break;

        ################################################################

        case "apagaDados3" :
            
            # Grava no log a atividade
            $atividade = "Excluiu os valores da tabela do plano de cargos {$plano->get_numDecreto($id)}";
            $data = date("Y-m-d H:i:s");
            $intra->registraLog($idUsuario, $data, $atividade, "tbclasse", null, 3);

            # Apaga a tabela tbsispatri
            $select = "SELECT idClasse
                         FROM tbclasse
                         WHERE idPlano = {$id}";

            $row = $pessoal->select($select);

            $pessoal->set_tabela("tbclasse");
            $pessoal->set_idCampo("idClasse");
            

            foreach ($row as $tt) {
                $pessoal->excluir($tt[0]);
                
                # grava o logo de cada inclusão
                $intra->registraLog($idUsuario, $data, "Excluiu o valor de forma automárica", "tbclasse",$tt[0], 3);
            }
            
            
            
            loadPage("?fase=gerenciaTabela");
            break;

        ################################################################

        case "porcentagem" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?fase=gerenciaTabela");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Volta para a tela inicial');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            # Exibe dados do plano
            $plano->exibeDadosPlano($id);
            br();

            $grid = new Grid("center");
            $grid->abreColuna(8);

            # Verifica se a tabela vazia
            $select = "SELECT idClasse 
                         FROM tbclasse
                         WHERE idPlano = {$id}";

            if ($pessoal->count($select) > 0) {
                tituloTable('Essa Tabela NÃO Está Vazia');
                $painel = new Callout("warning");
                $painel->abre();
                br();

                p("Para importar os dados de uma tabela já existente é necessário que a tabela esteja vazia", "center");
                br();

                $painel->fecha();
            } else {

                tituloTable('Informe o Plano de Cargos Modelo e a Porcentagem de Aumento');
                $callout = new Callout("secondary");
                $callout->abre();
                br();
                $form = new Form("?fase=PorcentagemValida");

                # Preenche o array para o plano de cargos
                $select = "SELECT idPlano,
                                  numDecreto
                             FROM tbplano
                            WHERE idPlano <> {$id}
                         ORDER BY planoAtual desc, dtPublicacao desc, numDecreto desc";

                $itens = $pessoal->select($select);

                # Plano de Cargos
                $controle = new Input('idPlano', 'combo', 'Plano de Cargos Modelo:', 1);
                $controle->set_size(10);
                $controle->set_linha(1);
                $controle->set_col(6);
                $controle->set_array($itens);
                $controle->set_required(true);
                $controle->set_autofocus(true);
                $controle->set_title('A tabela usada como base para a nova tabela');
                $form->add_item($controle);

                # Porcentagem
                $controle = new Input('porcentagem', 'porcentagem', 'Porcentagem:', 1);
                $controle->set_size(5);
                $controle->set_linha(1);
                $controle->set_col(3);
                $controle->set_required(true);
                $controle->set_title('A porcentagem de aumento');
                $form->add_item($controle);

                # submit
                $controle = new Input('submit', 'submit');
                $controle->set_valor('Entrar');
                $controle->set_linha(3);
                $form->add_item($controle);

                $form->show();
                $callout->fecha();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "PorcentagemValida" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "?fase=porcentagem");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Volta para a tela inicial');
            $menu1->add_link($botaoVoltar, "left");

            $menu1->show();

            # Exibe dados do plano a ser preenchido (que está em branco)
            $plano->exibeDadosPlano($id);
            br();

            $grid = new Grid("center");
            $grid->abreColuna(8);

            # Pega o id do plano que será modelo ( o que está preenchido)
            $idPlanoModelo = post("idPlano");
            $porcentagem = str_replace(".", ",", post("porcentagem"));

            # Joga os dados do post para a session
            set_session('idPlanoModelo', $idPlanoModelo);
            set_session('porcentagem', $porcentagem);

            if (empty($idPlanoModelo) OR empty($porcentagem)) {
                alert("Todos os dados devem ser informados!!");
                back(1);
            } else {
                # Pega os dados
                $dados1 = $plano->get_dadosPlano($idPlanoModelo);
                $dados2 = $plano->get_dadosPlano($id);

                tituloTable('Confirmar o procedimento');
                $painel = new Callout("warning");
                $painel->abre();
                br();

                p("O Sistema irá povoar a tabela do plano: <b>{$dados2['numDecreto']}</b><br/>usando, como base, os dados da tabela do plano: <b>{$dados1['numDecreto']}</b><br/>com o aumento de: <b>{$porcentagem}%</b>", "center");

                # Cria um menu
                $menu1 = new MenuBar();

                # Prosseguir
                $botao = new Link("Prosseguir", "?fase=porcentagemAguarda");
                $botao->set_class('button');
                $botao->set_title('Confirma e pressegue');
                $menu1->add_link($botao, "right");

                $menu1->show();

                $painel->fecha();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "porcentagemAguarda":

            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Preenchendo a tabela...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=preencheTabela');
            break;

        ################################################################

        case "preencheTabela" :
            # Limita o tamanho da tela
            # Pega os dados do post
            $idPlanoModelo = get_session('idPlanoModelo');
            $porcentagem = get_session('porcentagem');

            # Pega os valores da tabela modelo
            $select = "SELECT * FROM tbclasse WHERE idPlano = {$idPlanoModelo}";
            $row = $pessoal->select($select);
            $porcentagem = str_replace(",", ".", $porcentagem);

            # Joga os valores para a tabela em branco
            foreach ($row as $item) {
                # Grava os dados
                $campoNome = ["idTipoCargo", "idPlano", "faixa", "valor", "nivel"];
                $campoValor = [$item["idTipoCargo"], $id, $item["faixa"], $item["valor"], $item["nivel"]];
                $pessoal->gravar($campoNome, $campoValor, null, "tbclasse", "idClasse");
            }

            # Pega os valores da tabela nova
            $select = "SELECT * FROM tbclasse WHERE idPlano = {$id}";
            $row = $pessoal->select($select);

            # Grava no log Inicial
            $data = date("Y-m-d H:i:s");

            # Pega os dados
            $dados1 = $plano->get_dadosPlano($idPlanoModelo);
            $dados2 = $plano->get_dadosPlano($id);

            $atividade = "Executou a rotina de preenchimento automático de uma nova tabela de salário."
                    . " Preenchendo a tabela de salário do plano: {$dados2['numDecreto']} usando, como base,"
                    . " os dados da tabela do plano: {$dados1['numDecreto']} com o aumento de: {$porcentagem}%.";

            # grava se tiver atividades para serem gravadas
            $intra->registraLog($idUsuario, $data, $atividade, "tbclasse", null, 1);

            # Efetua o aumento na tabela nova
            foreach ($row as $item) {
                # Grava os dados
                $campoNome = ["valor"];
                $campoValor = [(float) $item["valor"] + (((float) $item["valor"] * $porcentagem) / 100)];
                $pessoal->gravar($campoNome, $campoValor, $item["idClasse"], "tbclasse", "idClasse");

                # grava o logo de cada inclusão
                $intra->registraLog($idUsuario, $data, "Incluiu valor de forma automárica", "tbclasse", $item["idClasse"], 1);
            }

            loadPage("?fase=gerenciaTabela");
            break;

        ################################################################

        case "upload" :
            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(12);

            # Exibe o Título
            if (!file_exists("{$pasta}{$id}.pdf")) {
                br();

                # Título
                tituloTable("Upload do {$nome}");

                # do Log
                $atividade = "Fez o upload do<br>{$nome}";
            } else {
                # Monta o Menu
                $menu = new MenuBar();

                $botaoApaga = new Button("Excluir o Arquivo");
                $botaoApaga->set_url("?fase=apagaDocumento&id={$id}");
                $botaoApaga->set_title("Exclui o Arquivo PDF cadastrado");
                $botaoApaga->set_class("button alert");
                $botaoApaga->set_confirma("Tem certeza que você deseja excluir o arquivo do {$nome}?");
                $menu->add_link($botaoApaga, "right");
                $menu->show();

                # Título
                tituloTable("Substituir o Arquivo Cadastrado");

                # Define o link de voltar após o salvar
                $voltarsalvar = "?fase=uploadTerminado";

                # do Log
                $atividade = "Substituiu o arquivo do {$nome}";
            }

            #####
            # Limita a tela
            $grid->fechaColuna();
            $grid->abreColuna(6);

            # Monta o formulário
            echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='doc'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";

            # Se não existe o programa cria
            if (!file_exists($pasta) || !is_dir($pasta)) {
                mkdir($pasta, 0755);
            }

            # Pega os valores do php.ini
            $postMax = limpa_numero(ini_get('post_max_size'));
            $uploadMax = limpa_numero(ini_get('upload_max_filesize'));
            $limite = menorValor(array($postMax, $uploadMax));

            $texto = "Extensões Permitidas:";
            foreach ($extensoes as $pp) {
                $texto .= " $pp";
            }
            $texto .= "<br/>Tamanho Máximo do Arquivo: $limite M";

            br();
            p($texto, "f14", "center");

            if ((isset($_POST["submit"])) && (!empty($_FILES['doc']))) {
                $upload = new UploadDoc($_FILES['doc'], $pasta, $id, $extensoes);

                # Salva e verifica se houve erro
                if ($upload->salvar()) {

                    # Registra log
                    $Objetolog = new Intra();
                    $data = date("Y-m-d H:i:s");
                    $Objetolog->registraLog($idUsuario, $data, $atividade, $tabela, $id, 8, $idServidorPesquisado);

                    # Fecha a janela aberta
                    loadPage("?fase=uploadTerminado");
                } else {
                    # volta a tela de upload
                    loadPage("?fase=upload&id=$id");
                }
            }

            # Informa caso exista um arquivo com o mesmo nome
            if (file_exists("{$pasta}{$id}.pdf")) {
                p("Já existe um documento para este registro!!<br/>O novo documento irá substituir o antigo !", "puploadMensagem");
                br();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "uploadTerminado" :
            # Informa que o pdf foi substituído
            alert("Arquivo do {$nome} Cadastrado !!");

            # Fecha a janela
            echo '<script type="text/javascript" language="javascript">window.close();</script>';
            break;

        case "apagaDocumento" :

            # Apaga o arquivo (na verdade renomeia)
            if (rename("{$pasta}{$id}.pdf", "{$pasta}apagado_{$id}_" . $intra->get_usuario($idUsuario) . "_" . date("Y.m.d_H:i") . ".pdf")) {
                alert("Arquivo Excluído !!");

                # Registra log
                $atividade = "Excluiu o arquivo do {$nome}";
                $Objetolog = new Intra();
                $data = date("Y-m-d H:i:s");
                $Objetolog->registraLog($idUsuario, $data, $atividade, $tabela, $id, 3, $idServidorPesquisado);

                # Fecha a janela
                echo '<script type="text/javascript" language="javascript">window.close();</script>';
            } else {
                alert("Houve algum problema, O arquivo não pode ser excluído !!");

                # Fecha a janela
                echo '<script type="text/javascript" language="javascript">window.close();</script>';
            }
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
