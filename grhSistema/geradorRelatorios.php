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
$postIdMatricula = post('postIdMatricula');
$postNome = post('postNome');
$postNomeCargo = post('postNomeCargo');
$postNomeCargoId = post('postNomeCargoId');
$postNomeCargoLotacao = post('postNomeCargoLotacao');
$postNomeCargoLotacaoId = post('postNomeCargoLotacaoId');
$postNomeCargoLotacaoPerfil = post('postNomeCargoLotacaoPerfil');
$postNomeCargoLotacaoPerfilSituacao = post('postNomeCargoLotacaoPerfilSituacao');
$postCargo = post('postCargo');
$postComissao = post('postComissao');
$postLotacao = post('postLotacao');
$postPerfil = post('postPerfil');
$postSituacao = post('postSituacao');
$postConcurso = post('postConcurso');
$postDtAdmissao = post('postDtAdmissao');
$postDtSaida = post('postDtSaida');
$postEmailUenf = post('postEmailUenf');
$postEndereco = post('postEndereco');
$postCpf = post('postCpf');

$postPai = post('postPai');
$postMae = post('postMae');
$postNacionalidade = post('postNacionalidade');
$postNaturalidade = post('postNaturalidade');
$postDtNascimento = post('postDtNascimento');
$postSexo = post('postSexo');
$postIdade = post('postIdade');

$postAssinatura = post('postAssinatura');

# Filtros
$parametroCargo = post('parametroCargo');
$parametroCargoComissao = post('parametroCargoComissao');
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

    # Log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Visualizou o gerador personalizado de relatório";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7);
    }

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

    tituloTable("Defina os Perâmetros");

    # Define o tamanho das colunas
    $tamColunas = 3;

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
    $controle->set_col($tamColunas);
    $controle->set_fieldset("Dados Principais:");
    $form->add_item($controle);

    # Matricula
    $controle = new Input('postMatricula', 'simnao', 'Matrícula:', 1);
    $controle->set_size(5);
    $controle->set_title('Matrícula do servidor');
    $controle->set_valor($postMatricula);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Idfuncional & Matricula
    $controle = new Input('postIdMatricula', 'simnao', 'Id/Mat.:', 1);
    $controle->set_size(5);
    $controle->set_title('Idfuncional e Matrícula do servidor na mesma coluna');
    $controle->set_valor($postIdMatricula);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Nome
    $controle = new Input('postNome', 'simnao', 'Nome:', 2);
    $controle->set_size(5);
    $controle->set_title('Nome do Servidor');
    $controle->set_valor($postNome);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Cargo
    $controle = new Input('postCargo', 'simnao', 'Cargo:', 1);
    $controle->set_size(5);
    $controle->set_title('Cargo do Servidor');
    $controle->set_valor($postCargo);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Lotação
    $controle = new Input('postLotacao', 'simnao', 'Lotação:', 1);
    $controle->set_size(5);
    $controle->set_title('Lotação do Servidor');
    $controle->set_valor($postLotacao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Perfil
    $controle = new Input('postPerfil', 'simnao', 'Perfil:', 1);
    $controle->set_size(5);
    $controle->set_title('Perfil do Servidor');
    $controle->set_valor($postPerfil);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Situação
    $controle = new Input('postSituacao', 'simnao', 'Situação:', 1);
    $controle->set_size(5);
    $controle->set_title('Situação do Servidor');
    $controle->set_valor($postSituacao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Nome & Cargo
    $controle = new Input('postNomeCargo', 'simnao', 'Cargo', 1);
    $controle->set_size(5);
    $controle->set_fieldset('Junto com o Nome');
    $controle->set_title('Nome e Cargo do Servidor');
    $controle->set_valor($postNomeCargo);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $controle->set_linha(2);
    $form->add_item($controle);

    # Nome, id & Cargo
    $controle = new Input('postNomeCargoId', 'simnao', 'Car/Id/Lot', 1);
    $controle->set_size(5);
    $controle->set_title('Nome, Id e Cargo do Servidor');
    $controle->set_valor($postNomeCargoId);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $controle->set_linha(2);
    $form->add_item($controle);

    # Nome, Cargo & Lotação
    $controle = new Input('postNomeCargoLotacao', 'simnao', 'Cargo/Lot', 1);
    $controle->set_size(5);
    $controle->set_title('Nome, Cargo e Lotação do Servidor');
    $controle->set_valor($postNomeCargoLotacao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $controle->set_linha(2);
    $form->add_item($controle);

    # Nome, Cargo, Lotação & Id
    $controle = new Input('postNomeCargoLotacaoId', 'simnao', 'Cg/Lo/Id', 1);
    $controle->set_size(5);
    $controle->set_title('Nome, Cargo, Lotação e Id do Servidor');
    $controle->set_valor($postNomeCargoLotacaoId);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $controle->set_linha(2);
    $form->add_item($controle);

    # Nome, Cargo, Lotação & Perfil
    $controle = new Input('postNomeCargoLotacaoPerfil', 'simnao', 'Cg/Lo/Pf', 1);
    $controle->set_size(5);
    $controle->set_title('Nome, Cargo, Lotação e Perfil do Servidor');
    $controle->set_valor($postNomeCargoLotacaoPerfil);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Nome, Cargo, Lotação, Perfil & situação
    $controle = new Input('postNomeCargoLotacaoPerfilSituacao', 'simnao', 'Cg/Lo/Pf/Si', 1);
    $controle->set_size(5);
    $controle->set_title('Nome, Cargo, Lotação, Perfil e Situação do Servidor');
    $controle->set_valor($postNomeCargoLotacaoPerfilSituacao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Cargo em Comissao
    $controle = new Input('postComissao', 'simnao', 'Comissão:', 1);
    $controle->set_size(5);
    $controle->set_fieldset('Outros Dados');
    $controle->set_title('Cargo em Comissão do Servidor');
    $controle->set_valor($postComissao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(3);
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Concurso
    $controle = new Input('postConcurso', 'simnao', 'Concurso:', 1);
    $controle->set_size(5);
    $controle->set_title('Concurso do Servidor');
    $controle->set_valor($postConcurso);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(3);
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Data de Admissão
    $controle = new Input('postDtAdmissao', 'simnao', 'Admissão:', 1);
    $controle->set_size(5);
    $controle->set_title('Data de admissão do Servidor');
    $controle->set_valor($postDtAdmissao);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_linha(3);
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Data de Saída
    $controle = new Input('postDtSaida', 'simnao', 'Saída:', 1);
    $controle->set_size(5);
    $controle->set_title('Data de saída do Servidor');
    $controle->set_valor($postDtSaida);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $controle->set_linha(3);
    $form->add_item($controle);

    # E-mail Uenf
    $controle = new Input('postEmailUenf', 'simnao', 'E-mail:', 1);
    $controle->set_title('E-mail do Servidor');
    $controle->set_valor($postEmailUenf);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $controle->set_linha(4);
    $form->add_item($controle);

    # Endereço
    $controle = new Input('postEndereco', 'simnao', 'Endereço:', 1);
    $controle->set_size(5);
    $controle->set_fieldset('Dados Pessoais');
    $controle->set_title('Endereço do Servidor');
    $controle->set_valor($postEndereco);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Cpf
    $controle = new Input('postCpf', 'simnao', 'Cpf:', 1);
    $controle->set_size(5);
    $controle->set_title('cpf do Servidor');
    $controle->set_valor($postCpf);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);
    
    # Nome do Pai
    $controle = new Input('postPai', 'simnao', 'Pai:', 1);
    $controle->set_size(5);
    $controle->set_title('Nome do pai do Servidor');
    $controle->set_valor($postPai);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Nome da Mãe
    $controle = new Input('postMae', 'simnao', 'Mãe:', 1);
    $controle->set_size(5);
    $controle->set_title('Nome da mãe do Servidor');
    $controle->set_valor($postMae);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Nacionalidade
    $controle = new Input('postNacionalidade', 'simnao', 'Nacionalidade:', 1);
    $controle->set_size(5);
    $controle->set_title('Nacionalidade');
    $controle->set_valor($postNacionalidade);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Naturalidade
    $controle = new Input('postNaturalidade', 'simnao', 'Naturalidade:', 1);
    $controle->set_size(5);
    $controle->set_title('Naturalidade');
    $controle->set_valor($postNaturalidade);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # Data de Nascimento
    $controle = new Input('postDtNascimento', 'simnao', 'Nascimento:', 1);
    $controle->set_size(5);
    $controle->set_title('Nascimento do servidor');
    $controle->set_valor($postDtNascimento);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);

    # sexo
    $controle = new Input('postSexo', 'simnao', 'Sexo:', 1);
    $controle->set_size(5);
    $controle->set_title('Sexo do servidor');
    $controle->set_valor($postSexo);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);
    
    # idade
    $controle = new Input('postIdade', 'simnao', 'Idade:', 1);
    $controle->set_size(5);
    $controle->set_title('Idade do servidor');
    $controle->set_valor($postIdade);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
    $form->add_item($controle);
    
    # Endereço
    $controle = new Input('postAssinatura', 'simnao', 'Assinatura:', 1);
    $controle->set_size(5);
    $controle->set_linha(3);
    $controle->set_fieldset('Outros');
    $controle->set_title('Assinatura do Servidor');
    $controle->set_valor($postAssinatura);
    $controle->set_onChange('formPadrao.submit();');
    $controle->set_col($tamColunas);
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
     *  Cargos em Comissão
     */

    $result = $pessoal->select('SELECT tbtipocomissao.idTipoComissao,concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao)
                                          FROM tbtipocomissao
                                         WHERE ativo
                                      ORDER BY tbtipocomissao.simbolo');
    array_unshift($result, array(null, 'Todos'));

    $controle = new Input('parametroCargoComissao', 'combo', 'Cargo em Comissão:', 1);
    $controle->set_size(30);
    $controle->set_title('Filtra por Cargo em Comissão');
    $controle->set_array($result);
    $controle->set_valor($parametroCargoComissao);
    $controle->set_onChange('formPadrao.submit();');
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

    # IdMatrícula
    if ($postIdMatricula) {
        $field[] = "tbservidor.idServidor";
        $label[] = "IdFuncional / Matrícula";
        $align[] = "center";
        $class[] = "pessoal";
        $method[] = "get_idFuncionalEMatricula";
        $function[] = "";
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

    # Nome e Cargo
    if ($postNomeCargo) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Nome";
        $align[] = "left";
        $class[] = "Pessoal";
        $method[] = "get_nomeECargo";
        $function[] = "";
    }

    # Nome, Id e Cargo
    if ($postNomeCargoId) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Servidor";
        $align[] = "left";
        $class[] = "Pessoal";
        $method[] = "get_nomeECargoEId";
        $function[] = "";
    }

    # Nome, Cargo e Lotação
    if ($postNomeCargoLotacao) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Servidor";
        $align[] = "left";
        $class[] = "Pessoal";
        $method[] = "get_nomeECargoELotacao";
        $function[] = "";
    }

    # Nome, Cargo, Lotação e Id
    if ($postNomeCargoLotacaoId) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Servidor";
        $align[] = "left";
        $class[] = "Pessoal";
        $method[] = "get_nomeECargoELotacaoEId";
        $function[] = "";
    }

    # Nome, Cargo, Lotação e Perfil
    if ($postNomeCargoLotacaoPerfil) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Servidor";
        $align[] = "left";
        $class[] = "Pessoal";
        $method[] = "get_nomeECargoELotacaoEPerfil";
        $function[] = "";
    }

    # Nome, Cargo, Lotação, Perfil e Situação
    if ($postNomeCargoLotacaoPerfilSituacao) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Servidor";
        $align[] = "left";
        $class[] = "Pessoal";
        $method[] = "get_nomeECargoELotacaoEPerfilESituacao";
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

    # Cargo em Comissão
    if ($postComissao) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Comissão";
        $align[] = "left";
        $class[] = "Pessoal";
        $method[] = "get_cargoComissao2";
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

    # Concurso
    if ($postConcurso) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Concurso";
        $align[] = "left";
        $class[] = "Pessoal";
        $method[] = "get_concurso";
        $function[] = "";
    }

    # E-mail Uenf
    if ($postEmailUenf) {
        $field[] = "tbservidor.idServidor";
        $label[] = "E-mail Uenf";
        $align[] = "left";
        $class[] = "Pessoal";
        $method[] = "get_emailUenf";
        $function[] = "";
    }

    # Endereço
    if ($postEndereco) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Endereço";
        $align[] = "left";
        $class[] = "Pessoal";
        $method[] = "get_endereco";
        $function[] = "";
    }

    # Cpf
    if ($postCpf) {
        $field[] = "tbpessoa.idPessoa";
        $label[] = "Cpf";
        $align[] = "center";
        $class[] = "Pessoal";
        $method[] = "get_cpf";
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

    # Nome do Pai
    if ($postPai) {
        $field[] = "tbpessoa.nomePai";
        $label[] = "Nome do Pai";
        $align[] = "left";
        $class[] = "";
        $method[] = "";
        $function[] = "";
    }

    # Nome da Mãe
    if ($postMae) {
        $field[] = "tbpessoa.nomeMae";
        $label[] = "Nome da Mãe";
        $align[] = "left";
        $class[] = "";
        $method[] = "";
        $function[] = "";
    }

    # Nacionalidade
    if ($postNacionalidade) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Nacionalidade";
        $align[] = "left";
        $class[] = "pessoal";
        $method[] = "get_nacionalidade";
        $function[] = "";
    }

    # Naturalidade
    if ($postNaturalidade) {
        $field[] = "tbpessoa.naturalidade";
        $label[] = "Naturalidade";
        $align[] = "left";
        $class[] = "";
        $method[] = "";
        $function[] = "";
    }

    # Data de Nascimento
    if ($postDtNascimento) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Nascimento";
        $align[] = "center";
        $class[] = "pessoal";
        $method[] = "get_dataNascimento";
        $function[] = "";
    }

    # sexo
    if ($postSexo) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Sexo";
        $align[] = "center";
        $class[] = "pessoal";
        $method[] = "get_sexo";
        $function[] = "";
    }
    
    # Idade
    if ($postIdade) {
        $field[] = "tbservidor.idServidor";
        $label[] = "Idade";
        $align[] = "center";
        $class[] = "pessoal";
        $method[] = "get_idade";
        $function[] = "";
    }
    
    # Assinatura
    if ($postAssinatura) {
        $field[] = "'<br/>_______________________________'";
        
        
        $label[] = "Assinatura";
        $align[] = "center";
        $class[] = "";
        $method[] = "";
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

        # Cargo em comissão
        if (!empty($parametroCargoComissao)) {
            $select .= ' LEFT JOIN tbcomissao ON (tbservidor.idServidor = tbcomissao.idServidor)
                         LEFT JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao)';
        }

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

        # cargo em comissão
        if (!empty($parametroCargoComissao)) {
            $select .= " AND tbcomissao.dtExo is null AND tbcomissao.tipo != 3 AND tbtipocomissao.idTipoComissao = '{$parametroCargoComissao}'";
        }

        # Estabelece a ordenação
        $select .= " ORDER BY {$parametroOrdena} {$parametroOrdenaTipo}";

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
        br(15);

        $painel->fecha();
    }


    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
