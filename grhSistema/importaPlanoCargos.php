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
$acesso = Verifica::acesso($idUsuario, 1);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    $plano = new PlanoCargos();

    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id 
    $id = get_session('idPlano');

    # Começa uma nova página
    $page = new Page();
    $page->set_bodyOnLoad('$(document).foundation();');
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Limita o tamanho da tela
    $grid1 = new Grid();
    $grid1->abreColuna(12);

    switch ($fase) {
        case "":
            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "cadastroPlanoCargos.php?fase=gerenciaTabela");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Voltar para página anterior');
            $linkVoltar->set_accessKey('V');
            $menu1->add_link($linkVoltar, "left");

            $menu1->show();

            # Exibe dados do plano
            $plano->exibeDadosPlano($id);
            br();

            $grid = new Grid("center");
            $grid->abreColuna(8);

            # Verifica se existe algum servidor vinculado a essa tabela
            $select = "SELECT idProgressao 
                             FROM tbprogressao JOIN tbclasse USING (idClasse)
                            WHERE idPlano = {$id}";

            if ($pessoal->count($select) > 0) {
                tituloTable('Existem Servidores Vinculados');
                $painel = new Callout("warning");
                $painel->abre();
                br();

                p("Existem {$pessoal->count($select)} registros de servidores vinculados a esse plano de cargos.<br/>"
                        . "Para se importar servidores para essa tabela, é necessário que a mesma não tenha servidores vinculados a ela.", "center");
                br();

                $painel->fecha();
            } else {
                tituloTable('Atenção !');
                $painel = new Callout("warning");
                $painel->abre();
                br();

                p("Esta rotina atualiza o salário de TODOS OS SERVIDORES ESTATUTÁRIOS ATIVOS<br/>"
                        . "que estão cadastrados no PLANO ATUAL para a tabela: {$plano->get_numDecreto($id)},<br/>"
                        . "respeitando o nível / faixa / padrão correspondente.<br/>"
                        . "<br/>Esta atualização só irá ocorrer se o nível / faixa / padrão<br/>"
                        . "da nova tabela corresponder exatamente ao da tabela atual.<br/>"
                        . "<br/>Importante observer que só serão atualizados os servidores estatutários ativos que,<br/>"
                        . "estiverem com o cadastro regularizados, ou seja, com o salário vinculado ao plano atual.", "center");

                br();

                # Cria um menu
                $menu1 = new MenuBar();

                # Prosseguir
                $botaoVoltar = new Link("Prosseguir", "?fase=importa1");
                $botaoVoltar->set_class('button');
                $botaoVoltar->set_title('Confirma e prossegue');
                $menu1->add_link($botaoVoltar, "right");

                $menu1->show();

                $painel->fecha();
            }

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "importa1":


            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Verificando os servidore que serão afetados...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=importa2');
            break;

        ################################################################

        case "importa2":

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $linkVoltar = new Link("Voltar", "?");
            $linkVoltar->set_class('button');
            $linkVoltar->set_title('Voltar para página anterior');
            $linkVoltar->set_accessKey('V');
            $menu1->add_link($linkVoltar, "left");

            $menu1->show();

            # Exibe dados do plano
            $plano->exibeDadosPlano($id);
            br();

            $grid = new Grid("center");
            $grid->abreColuna(8);

            # Pega qual e a tabela atual
            $idPlanoAtual = $plano->get_planoAtual();

            # Servidores Afetados
            $select = "SELECT DISTINCT tbservidor.idServidor, 
                              tbservidor.idServidor,
                              idPlano
                        FROM tbservidor JOIN tbprogressao USING (idServidor)
                                        JOIN tbclasse USING (idClasse)
                                        JOIN tbplano USING (idPlano)
                                        JOIN tbpessoa USING (idPessoa)
                       WHERE tbplano.dtVigencia = (SELECT MAX(dtVigencia) FROM tbprogressao JOIN tbclasse USING(idClasse)
                                                                                               JOIN tbplano USING (idPlano)
                                                                             WHERE tbprogressao.idServidor = tbservidor.idservidor)
                         AND situacao = 1
                         AND idPerfil = 1
                          AND idPlano = {$idPlanoAtual}
                          ORDER BY tbpessoa.nome";

            $servidoresafetados = $pessoal->select($select);
            $numServidoresafetados = $pessoal->count($select);

            # Servidores Não Afetados
            $select = "SELECT DISTINCT tbservidor.idServidor, 
                              tbservidor.idServidor,
                              idPlano
                        FROM tbservidor JOIN tbprogressao USING (idServidor)
                                        JOIN tbclasse USING (idClasse)
                                        JOIN tbplano USING (idPlano)
                                        JOIN tbpessoa USING (idPessoa)                                        
                       WHERE tbplano.dtVigencia = (SELECT MAX(dtVigencia) FROM tbprogressao JOIN tbclasse USING(idClasse)
                                                                                               JOIN tbplano USING (idPlano)
                                                                             WHERE tbprogressao.idServidor = tbservidor.idservidor)
                         AND situacao = 1
                         AND idPerfil = 1
                          AND idPlano <> {$idPlanoAtual}
                          ORDER BY tbpessoa.nome";

            $servidoresNafetados = $pessoal->select($select);
            $numServidoresNafetados = $pessoal->count($select);

            $painel = new Callout("warning");
            $painel->abre();
            p("O Sistema irá atualizar o salário de {$numServidoresafetados} servidores do<br/>"
                    . "plano atual: {$plano->get_numDecreto($idPlanoAtual)}, para o novo plano: {$plano->get_numDecreto($id)}.<br/>"
                    . "{$numServidoresNafetados} servidores não serão afetados.", "center");
            $painel->fecha();

            # Cria um menu
            $menu1 = new MenuBar();

            # Prosseguir
            $botaoVoltar = new Link("Efetuar a Atualização", "?fase=importa3");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_confirma('Você Deseja Realmente Fazer Essa Alteração ?');
            $botaoVoltar->set_title('Começa a atualizaçao de fato');
            $menu1->add_link($botaoVoltar, "right");

            $menu1->show();

            $tab = new Tab(["Servidores Afetados", "Servidores Não Afetados"]);
            $tab->abreConteudo();

            # servidores Afetados
            $tabela = new Tabela();
            $tabela->set_titulo("Servidores Que Terão O Salário Atualizado");
            $tabela->set_subtitulo("{$numServidoresafetados} servidores");
            $tabela->set_conteudo($servidoresafetados);
            $tabela->set_label(["Servidor", "Lotação", "Plano Cadastrador"]);
            $tabela->set_classe(["Pessoal", "Pessoal", "PlanoCargos"]);
            $tabela->set_metodo(["get_nomeECargoSimplesEPerfil", "get_lotacao", "get_numDecreto"]);
            $tabela->set_width([40, 30, 30]);
            $tabela->set_align(["left", "left"]);
            $tabela->show();

            $tab->fechaConteudo();
            $tab->abreConteudo();

            # servidores Não Afetados
            $tabela = new Tabela();
            $tabela->set_titulo("Servidores Não Afetados");
            $tabela->set_subtitulo("{$numServidoresNafetados} servidores");
            $tabela->set_conteudo($servidoresNafetados);
            $tabela->set_label(["Servidor", "Lotação", "Plano Cadastrador"]);
            $tabela->set_classe(["Pessoal", "Pessoal", "PlanoCargos"]);
            $tabela->set_metodo(["get_nomeECargoSimplesEPerfil", "get_lotacao", "get_numDecreto"]);
            $tabela->set_width([40, 30, 30]);
            $tabela->set_align(["left", "left"]);
            $tabela->show();

            $tab->fechaConteudo();
            $tab->show();
            br();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ################################################################

        case "importa3":


            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Fazendo a Importação. Aguarde. Esse procedimento pode demorar um pouco.", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=importa4');
            break;

        ################################################################

        case "importa4":

            # Pega qual e a tabela atual
            $idPlanoAtual = $plano->get_planoAtual();

            # Pega os dados do novo plano
            $dados = $plano->get_dadosPlano($id);
            
            # Servidores Afetados
             $select = "SELECT DISTINCT tbservidor.idServidor, 
                               tbclasse.faixa
                        FROM tbservidor LEFT JOIN tbprogressao USING (idServidor)
                                        LEFT JOIN tbclasse USING (idClasse)
                                        JOIN tbplano USING (idPlano)
                                        JOIN tbpessoa USING (idPessoa)
                       WHERE tbplano.dtVigencia = (SELECT MAX(dtVigencia) FROM tbprogressao JOIN tbclasse USING(idClasse)
                                                                                            JOIN tbplano USING (idPlano)
                                                                             WHERE tbprogressao.idServidor = tbservidor.idservidor)
                         AND situacao = 1
                         AND idPerfil = 1
                          AND idPlano = {$idPlanoAtual}
                          ORDER BY tbpessoa.nome";

            $servidoresafetados = $pessoal->select($select);
            $numServidoresafetados = $pessoal->count($select);
            echo $numServidoresafetados;
            

            foreach ($servidoresafetados as $item) {

//                # Campos
//                $campos = ["idServidor", "idTpProgressao", "idClasse", "dtPublicacao", "dtInicial", "dtImportacao"];
//
//                # Valores
//                $valor = [$item["idServidor"], 5, $plano->get_idClasse($id, $item['faixa']), $dados["dtPublicacao"], $dados["dtVigencia"], date("Y-m-d")];
//
//                # Grava
//                $pessoal->gravar($campos, $valor, null, "tbprogressao", "idProgressao");
            }
            #loadPage("?");
            break;

        ################################################################
    }

    # Fecha o grid
    $grid1->fechaColuna();
    $grid1->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}    