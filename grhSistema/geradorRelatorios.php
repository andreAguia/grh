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
$postIdfuncional = post('postIdFuncional');
$postMatricula = post('postMatricula');
$postNome = post('postNome');
$postCargo = post('postCargo');
$postLotacao = post('postLotacao');
$postPerfil = post('postPerfil');
$postSituacao = post('postSituacao');
$postDtAdmissao = post('postDtAdmissao');
$postDtSaida = post('postDtSaida');

# Filtros
$parametroCargo = post('parametroCargo');
$parametroLotacao = post('parametroLotacao');
$parametroSituacao = post('parametroSituacao', 1);
$parametroPerfil = post('parametroPerfil', 1);

# Oedenação
$parametroOrdenaTipo = post('parametroOrdenaTipo', 'asc');
$parametroOrdena = post('parametroOrdena', "tbpessoa.nome");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 9, 10]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);

    ################################################################
    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    $botaoVoltar = new Link("Voltar", "grhRelatorios.php");
    $botaoVoltar->set_class('button');
    $botaoVoltar->set_title('Voltar a página anterior');
    $botaoVoltar->set_accessKey('V');
    $menu1->add_link($botaoVoltar, "left");

    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_title("Relatório dessa pesquisa");
    $botaoRel->set_url("../grhRelatorios/gerador.php");
    $botaoRel->set_target("_blank");
    $botaoRel->set_imagem($imagem);
    $menu1->add_link($botaoRel, "right");

    $menu1->show();

    titulo("Gerador de Relatórios Personalizados");
    br();

    $grid->fechaColuna();
    $grid->abreColuna(4);

    tituloTable("Atenção");
    callout("Esta rotina ainda está sendo desenvolvida.<br/>Peço que aguarde alguns dias para utilizá-la.");

    # Monta o formulário
    $form = new Form('?');

    /*
     * Dados Gerais
     */

    # IdFuncional
    $controle = new Input('postIdFuncional', 'simnao', 'IdFuncional:', 1);
    $controle->set_size(5);
    $controle->set_title('Idfuncional');
    $controle->set_valor($postIdfuncional);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col(4);
    $controle->set_fieldset("Informe as Colunas:");
    $form->add_item($controle);

    # Matricula
    $controle = new Input('postMatricula', 'simnao', 'Matrícula:', 1);
    $controle->set_size(5);
    $controle->set_title('Matrícula do servidor');
    $controle->set_valor($postMatricula);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col(4);
    $form->add_item($controle);

    # Nome
    $controle = new Input('postNome', 'simnao', 'Nome:', 1);
    $controle->set_size(5);
    $controle->set_title('Nome do Servidor');
    $controle->set_valor($postNome);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col(4);
    $form->add_item($controle);

    # Cargo
    $controle = new Input('postCargo', 'simnao', 'Cargo:', 1);
    $controle->set_size(5);
    $controle->set_title('Cargo do Servidor');
    $controle->set_valor($postCargo);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col(4);
    $form->add_item($controle);

    # Lotação
    $controle = new Input('postLotacao', 'simnao', 'Lotação:', 1);
    $controle->set_size(5);
    $controle->set_title('Lotação do Servidor');
    $controle->set_valor($postLotacao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col(4);
    $form->add_item($controle);

    # Perfil
    $controle = new Input('postPerfil', 'simnao', 'Perfil:', 1);
    $controle->set_size(5);
    $controle->set_title('Perfil do Servidor');
    $controle->set_valor($postPerfil);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col(4);
    $form->add_item($controle);

    # Situação
    $controle = new Input('postSituacao', 'simnao', 'Situação:', 1);
    $controle->set_size(5);
    $controle->set_title('Situação do Servidor');
    $controle->set_valor($postSituacao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col(4);
    $form->add_item($controle);

    # Data de Admissão
    $controle = new Input('postDtAdmissao', 'simnao', 'Admissão:', 1);
    $controle->set_size(5);
    $controle->set_title('Admissão do Servidor');
    $controle->set_valor($postDtAdmissao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col(4);
    $form->add_item($controle);

    # Data de Admissão
    $controle = new Input('postDtSaida', 'simnao', 'Saída:', 1);
    $controle->set_size(5);
    $controle->set_title('data de saída do Servidor');
    $controle->set_valor($postDtSaida);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col(4);
    $form->add_item($controle);

    #################################### Filtro #######################################

    /*
     * Cargo
     */

    # Pega os dados
    $result1 = $pessoal->select('SELECT tbcargo.idCargo, 
                                                concat(tbtipocargo.cargo," - ",tbarea.area," - ",tbcargo.nome) as cargo
                                           FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                                                        LEFT JOIN tbarea USING (idArea)    
                                       ORDER BY 2');

    # cargos por nivel
    $result2 = $pessoal->select('SELECT cargo,cargo FROM tbtipocargo WHERE cargo <> "Professor Associado" AND cargo <> "Professor Titular" ORDER BY 2');

    # junta os dois
    $result = array_merge($result2, $result1);

    # acrescenta Professor
    array_unshift($result, array('Professor', 'Professores'));

    # acrescenta todos
    array_unshift($result, array(null, 'Todos'));

    $controle = new Input('parametroCargo', 'combo', 'Cargo - Área - Função:', 1);
    $controle->set_size(30);
    $controle->set_title('Filtra por Cargo');
    $controle->set_array($result);
    $controle->set_valor($parametroCargo);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_fieldset("Informe o Filtro:");
    $controle->set_linha(1);
    $controle->set_col(12);
    $form->add_item($controle);

    /*
     *  Lotação
     */

    $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
    array_unshift($result, array(null, 'Todos'));

    $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
    $controle->set_size(30);
    $controle->set_title('Filtra por Lotação');
    $controle->set_array($result);
    $controle->set_valor($parametroLotacao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(2);
    $controle->set_col(12);
    $form->add_item($controle);

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
    $controle->set_linha(3);
    $controle->set_col(6);
    $controle->set_array($comboSituacao);
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
    $controle->set_linha(3);
    $controle->set_col(6);
    $controle->set_array($comboPerfil);
    $form->add_item($controle);

    #################################### Ordenação #######################################
    # Ordenação
    $ordena = [
        ["idfuncional", "IdFuncional"],
        ["tbpessoa.nome", "Nome"]
    ];

    # Campo
    $controle = new Input('parametroOrdena', 'combo', 'Ordenação', 1);
    $controle->set_size(20);
    $controle->set_title('Ano da assinatura do contrato');
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(6);
    $controle->set_valor($parametroOrdena);
    $controle->set_array($ordena);
    $controle->set_fieldset("Informe a Ordenação:");
    $form->add_item($controle);

    # Tipo
    $controle = new Input('parametroOrdenaTipo', 'combo', 'Tipo', 1);
    $controle->set_size(20);
    $controle->set_title('Ano da assinatura do contrato');
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(1);
    $controle->set_col(6);
    $controle->set_valor($parametroOrdenaTipo);
    $controle->set_array([
        ["asc", "asc"],
        ["desc", "desc"],
    ]);
    $form->add_item($controle);

    $form->show();

    #################################### Monta os Arrays #######################################
    #IdFuncional
    if ($postIdfuncional) {
        $field[] = "idFuncional";
        $label[] = "IdFuncional";
        $align[] = "center";
        $class[] = "";
        $method[] = "";
        $function[] = "";
    }

    # Matrícula
    if ($postMatricula) {
        $field[] = "matricula";
        $label[] = "Matrícula";
        $align[] = "center";
        $class[] = "";
        $method[] = "";
        $function[] = "dv";
    }

    # Nome
    if ($postNome) {
        $field[] = "tbpessoa.nome";
        $label[] = "Nome";
        $align[] = "left";
        $class[] = "";
        $method[] = "";
        $function[] = "";
    }

    # Cargo
    if ($postCargo) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Cargo";
        $align[] = "left";
        $class[] = "Pessoal";
        $method[] = "get_cargo";
        $function[] = "";
    }

    # Lotação
    if ($postLotacao) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Lotação";
        $align[] = "left";
        $class[] = "Pessoal";
        $method[] = "get_lotacao";
        $function[] = "";
    }

    # Perfil
    if ($postPerfil) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Perfil";
        $align[] = "center";
        $class[] = "Pessoal";
        $method[] = "get_perfil";
        $function[] = "";
    }

    # Admissão
    if ($postDtAdmissao) {
        $field[] = "tbservidor.dtAdmissao";
        $label[] = "Admissão";
        $align[] = "center";
        $class[] = "";
        $method[] = "";
        $function[] = "date_to_php";
    }

    # Saída
    if ($postDtSaida) {
        $field[] = "tbservidor.dtDemissao";
        $label[] = "Saída";
        $align[] = "center";
        $class[] = "";
        $method[] = "";
        $function[] = "date_to_php";
    }

    # Situação
    if ($postSituacao) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Situação";
        $align[] = "center";
        $class[] = "Pessoal";
        $method[] = "get_situacao";
        $function[] = "";
    }

    #################################### Monta a tabela #######################################

    $grid->fechaColuna();
    $grid->abreColuna(8);

    /*
     * Select
     */

    # Monta o select
    if (isset($field)) {

        # Monta o select
        $select = "SELECT ";

        foreach ($field as $item) {
            $select .= "{$item},";
        }

        $select = rtrim($select, ',');

        # Adiciona as tabelas
        $select .= " FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                        LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                        LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                        LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                                        LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)";

        # Adiciona filtro
        $select .= " WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

        # Situação
        if (!empty($parametroSituacao)) {
            $select .= " AND situacao = {$parametroSituacao}";
        }

        # Perfil
        if (!empty($parametroPerfil)) {
            $select .= " AND idPerfil = {$parametroPerfil}";
        }

        # Lotacao
        if (!empty($parametroLotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($parametroLotacao)) {
                $select .= " AND (tblotacao.idlotacao = '{$parametroLotacao}')";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
            }
        }

        # Cargo
        if (!empty($parametroCargo)) {
            if (is_numeric($parametroCargo)) {
                $select .= " AND (tbcargo.idcargo = {$parametroCargo})";
            } else { # senão é nivel do cargo
                if ($parametroCargo == "Professor") {
                    $select .= " AND (tbcargo.idcargo = 128 OR  tbcargo.idcargo = 129)";
                } else {
                    $select .= " AND (tbtipocargo.cargo = '{$parametroCargo}')";
                }
            }
        }


        # Estabelece a ordenação
        $select .= " ORDER BY {$parametroOrdena} {$parametroOrdenaTipo}";

        #echo $select;

        $row = $pessoal->select($select);

        # Cria as session para o relatório
        set_session("sessionSelect", $select);
        set_session("sessionLabel", $label);
        set_session("sessionAlign", $align);
        set_session("sessionClass", $class);
        set_session("sessionMethod", $method);
        set_session("sessionFunction", $function);

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
    } else {
        tituloTable("Relatório");
        $painel = new Callout();
        $painel->abre();
        
        br(8);
        p("Nenhum registro encontrado", "center", "f16");
        br(8);
        
        $painel->fecha();
    }


    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
