<?php

/**
 * Rotina do menu de relatório
 *  
 * By Alat
 */
# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Colunas
$postIdfuncional = post('postIdFuncional', get_session('postIdFuncional'));
$postNome = post('postNome', get_session('postNome'));
$postCargo = post('postCargo', get_session('postCargo'));
$postLotacao = post('postLotacao', get_session('postLotacao'));
$postPerfil = post('postPerfil', get_session('postPerfil'));
$postSituacao = post('postSituacao', get_session('postSituacao'));

# Filtros
$parametroSituacao = post('parametroSituacao', get_session('parametroSituacao', 1));
$parametroPerfil = post('parametroPerfil', get_session('parametroPerfil', 1));

# Oedenação
$parametroOrdenaTipo = post('parametroOrdenaTipo', get_session('parametroOrdenaTipo', 'asc'));
$parametroOrdena = post('parametroOrdena', get_session('parametroOrdena', "idFuncional"));

# Passa para as session
set_session("postIdFuncional", $postIdfuncional);
set_session("postNome", $postNome);
set_session("postCargo", $postCargo);
set_session("postLotacao", $postLotacao);
set_session("postPerfil", $postPerfil);
set_session("postSituacao", $postSituacao);

set_session("parametroSituacao", $parametroSituacao);
set_session("parametroPerfil", $parametroPerfil);

set_session("parametroOrdenaTipo", $parametroOrdenaTipo);
set_session("parametroOrdena", $parametroOrdena);

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 9, 10]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Verifica a fase do programa
    $fase = get('fase');

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    ################################################################

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

            loadPage('?fase=exibeLista');
            break;

        ################################################################

        case "exibeLista":

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar", "grhRelatorios.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar, "left");
            $menu1->show();

            titulo("Gerador de Relatórios Personalizados");
            br();
            
            $grid->fechaColuna();
            $grid->abreColuna(6);
            
            tituloTable("Atenção");
            callout("Esta rotina ainda está sendo desenvolvida.<br/>Peço que aguarde alguns dias para utilizá-la.");
            
            $grid->fechaColuna();
            $grid->abreColuna(6);
            
            $grid->fechaColuna();
            $grid->abreColuna(12);
            br();

            # Monta o formulário
            $form = new Form('?');

            /*
             * Dados Gerais
             */

            # IdFuncional
            $controle = new Input('postIdFuncional', 'simnao', 'IdFuncional:', 1);
            $controle->set_size(5);
            $controle->set_title('Idfuncional');
            $controle->set_valor("Sim");
            $controle->set_disabled(true);
            $controle->set_readonly(true);
            $controle->set_linha(1);
            $controle->set_col(1);
            $controle->set_fieldset("Informe as Colunas:");
            $form->add_item($controle);

            # Nome
            $controle = new Input('postNome', 'simnao', 'Nome:', 1);
            $controle->set_size(5);
            $controle->set_title('Nome do Servidor');
            $controle->set_valor($postNome);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(1);
            $form->add_item($controle);

            # Cargo
            $controle = new Input('postCargo', 'simnao', 'Cargo:', 1);
            $controle->set_size(5);
            $controle->set_title('Cargo do Servidor');
            $controle->set_valor($postCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(1);
            $form->add_item($controle);

            # Lotação
            $controle = new Input('postLotacao', 'simnao', 'Lotação:', 1);
            $controle->set_size(5);
            $controle->set_title('Lotação do Servidor');
            $controle->set_valor($postLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(1);
            $form->add_item($controle);

            # Perfil
            $controle = new Input('postPerfil', 'simnao', 'Perfil:', 1);
            $controle->set_size(5);
            $controle->set_title('Perfil do Servidor');
            $controle->set_valor($postPerfil);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(1);
            $form->add_item($controle);

            # Situação
            $controle = new Input('postSituacao', 'simnao', 'Situação:', 1);
            $controle->set_size(5);
            $controle->set_title('Situação do Servidor');
            $controle->set_valor($postSituacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(1);
            $form->add_item($controle);

            #################################### Ordenação #######################################
            # Ordenação
            $ordena = [
                ["idfuncional", "IdFuncional"],
                ["nome", "Nome"]
            ];

            # Campo
            $controle = new Input('parametroOrdena', 'combo', 'Ordenação', 1);
            $controle->set_size(20);
            $controle->set_title('Ano da assinatura do contrato');
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(7);
            $controle->set_col(3);
            $controle->set_valor($parametroOrdena);
            $controle->set_array($ordena);
            $controle->set_fieldset("Ordenação:");
            $form->add_item($controle);

            # Tipo
            $controle = new Input('parametroOrdenaTipo', 'combo', 'Tipo', 1);
            $controle->set_size(20);
            $controle->set_title('Ano da assinatura do contrato');
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(7);
            $controle->set_col(2);
            $controle->set_valor($parametroOrdenaTipo);
            $controle->set_array([
                ["asc", "asc"],
                ["desc", "desc"],
            ]);
            $form->add_item($controle);

            #################################### Filtro #######################################

            /*
             * Situação
             */

            # Pega os dados
            $comboSituacao = $pessoal->select('SELECT idSituacao, situacao
                                         FROM tbsituacao
                                     ORDER BY idSituacao');

            array_unshift($comboSituacao, array(null, "Todos"));

            # Situação
            $controle = new Input('parametroSituacao', 'combo', 'Situação:', 1);
            $controle->set_size(20);
            $controle->set_title('Situação');
            $controle->set_valor($parametroSituacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(8);
            $controle->set_col(2);
            $controle->set_array($comboSituacao);
            $controle->set_fieldset("Informe o Filtro:");
            $form->add_item($controle);

            /*
             * Perfil
             */

            # Pega os dados
            $comboPerfil = $pessoal->select('SELECT idPerfil, nome
                                         FROM tbperfil
                                     ORDER BY idPerfil');

            array_unshift($comboPerfil, array(null, "Todos"));

            # Situação
            $controle = new Input('parametroPerfil', 'combo', 'Perfil:', 1);
            $controle->set_size(20);
            $controle->set_title('Situação');
            $controle->set_valor($parametroPerfil);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(8);
            $controle->set_col(2);
            $controle->set_array($comboPerfil);
            $controle->set_fieldset("Informe o Filtro:");
            $form->add_item($controle);

            $form->show();

            #################################### Monta os Arrays #######################################
            #IdFuncional
            $field[] = "idFuncional";
            $label[] = "IdFuncional";
            $align[] = "center";
            $class[] = "";
            $method[] = "";
            $function[] = "";

            # Nome
            if ($postNome) {
                $field[] = "nome";
                $label[] = "Nome";
                $align[] = "left";
                $class[] = "";
                $method[] = "";
                $function[] = "";
            }

            # Cargo
            if ($postCargo) {
                $field[] = "idServidor";
                $label[] = "Cargo";
                $align[] = "left";
                $class[] = "Pessoal";
                $method[] = "get_cargo";
                $function[] = "";
            }

            # Lotação
            if ($postLotacao) {
                $field[] = "idServidor";
                $label[] = "Lotação";
                $align[] = "left";
                $class[] = "Pessoal";
                $method[] = "get_lotacao";
                $function[] = "";
            }

            # Perfil
            if ($postPerfil) {
                $field[] = "idServidor";
                $label[] = "Perfil";
                $align[] = "center";
                $class[] = "Pessoal";
                $method[] = "get_perfil";
                $function[] = "";
            }

            # Situação
            if ($postSituacao) {
                $field[] = "idServidor";
                $label[] = "Situação";
                $align[] = "center";
                $class[] = "Pessoal";
                $method[] = "get_situacao";
                $function[] = "";
            }



            #################################### Monta o Select #######################################

            /*
             * Select
             */

            if (count($field) > 0) {

                # Monta o select
                $select = "SELECT ";

                foreach ($field as $item) {
                    $select .= "{$item},";
                }

                $select = rtrim($select, ',');

                # Adiciona as tabelas
                $select .= " FROM tbservidor JOIN tbpessoa USING (idPessoa)";

                # Adiciona filtro
                $select .= " WHERE true";

                if (!empty($parametroSituacao)) {
                    $select .= " AND situacao = {$parametroSituacao}";
                }
                
                if (!empty($parametroPerfil)) {
                    $select .= " AND idPerfil = {$parametroPerfil}";
                }

                # Estabelece a ordenação
                $select .= " ORDER BY {$parametroOrdena} {$parametroOrdenaTipo}";

                #echo $select;

                $row = $pessoal->select($select);
            } else {
                $row = null;
            }
            br();

            # Cria as session para o relatório
            set_session("sessionSelect", $select);
            set_session("sessionLabel", $label);
            set_session("sessionAlign", $align);
            set_session("sessionClass", $class);
            set_session("sessionMethod", $method);
            set_session("sessionFunction", $function);

            # Cria um menu
            $menu1 = new MenuBar();

            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/gerador.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel, "right");
            $menu1->show();

            $tabela = new Tabela();
            $tabela->set_titulo("Relatório");
            $tabela->set_label($label);
            $tabela->set_align($align);
            #$tabela->set_width($width);
            $tabela->set_classe($class);
            $tabela->set_metodo($method);
            $tabela->set_funcao($function);
            $tabela->set_conteudo($row);
            $tabela->show();

            break;

        ################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
