<?php

/**
 * Histórico de Gratificações Especiais
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Acumulações de cargos públicos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio da área de Redução
    $origem = get_session("origem");

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra(["get_DadosServidor", "exibeDeclaracaoAcumulacao"]);
    $objeto->set_rotinaExtraParametro([$idServidorPesquisado, $idServidorPesquisado]);

    # Rotina extra editar
    $objeto->set_rotinaExtraEditar("exibeDeclaracaoAcumulacao");
    $objeto->set_rotinaExtraEditarParametro($idServidorPesquisado);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Processos de Acumulações de Cargos Públicos');

    # botão de voltar da lista
    if (empty($origem)) {
        $voltar = 'servidorMenu.php';
    } else {
        $voltar = $origem;
    }

    # botão de voltar da lista
    $objeto->set_voltarLista($voltar);

    # select da lista
    $objeto->set_selectLista("SELECT CASE conclusao
                                        WHEN 1 THEN 'Pendente'
                                        WHEN 2 THEN 'Resolvido'
                                        ELSE '--'
                                      END,
                                     idAcumulacao,                                     
                                     idAcumulacao,
                                     idAcumulacao,    
                                     idAcumulacao,    
                                     idAcumulacao
                                FROM tbacumulacao
                               WHERE idServidor = {$idServidorPesquisado}
                            ORDER BY tipoProcesso, dtProcesso");

    # select do edita
    $objeto->set_selectEdita('SELECT processo,
                                     tipoProcesso,   
                                     dtProcesso,
                                     instituicao,
                                     cargo,                                     
                                     matricula,
                                     dtAdmissao,
                                     dtSaida,
                                     motivoSaida,
                                     resultado,
                                     dtPublicacao,
                                     pgPublicacao,
                                     resultado1,
                                     dtPublicacao1,
                                     pgPublicacao1,
                                     resultado2,
                                     dtPublicacao2,
                                     pgPublicacao2,
                                     resultado3,
                                     dtPublicacao3,
                                     pgPublicacao3,                                     
                                     conclusao,
                                     obs,
                                     idServidor
                                FROM tbacumulacao
                               WHERE idAcumulacao = ' . $id);

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    $objeto->set_formatacaoCondicional(array(
        array('coluna' => 0,
            'valor' => 'Resolvido',
            'operador' => '=',
            'id' => 'emAberto'),
        array('coluna' => 0,
            'valor' => 'Pendente',
            'operador' => '=',
            'id' => 'alerta')
    ));

    # Parametros da tabela
    $objeto->set_label(["Conclusão", "Resultado", "Data da<br/>Publicação", "Processo", "Dados do Segundo Vínculo", "Documentos"]);
    $objeto->set_align(["center", "center", "center", "center", "left", "left"]);
    $objeto->set_classe([null, "Acumulacao", "Acumulacao", "Acumulacao", "Acumulacao", "Acumulacao"]);
    $objeto->set_metodo([null, "get_resultado", "exibePublicacao", "exibeProcesso", "exibeDadosOutroVinculo", "exibeBotaoDocumentos"]);

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbacumulacao');

    # Nome do campo id
    $objeto->set_idCampo('idAcumulacao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega os dados da combo motivo de Saída do servidor
    $motivo = $pessoal->select('SELECT idmotivo,
                                       motivo
                                  FROM tbmotivo
                              ORDER BY motivo');

    array_unshift($motivo, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'processo',
            'label' => 'Processo:',
            'tipo' => 'texto',
            'size' => 30,
            'col' => 3,
            'title' => 'Número do Processo',
            'autofocus' => true,
            'linha' => 1),
        array('nome' => 'tipoProcesso',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'array' => array(
                array(null, null),
                array(1, "Principal"),
                array(2, "Relacionado"),
                array(3, "Outros"),
            ),
            'size' => 2,
            'valor' => null,
            'col' => 2,
            'title' => 'Tipo do Processo.',
            'linha' => 1),
        array('nome' => 'dtProcesso',
            'label' => 'Data do Processo:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data de entrada do processo.',
            'linha' => 1),
        array('nome' => 'instituicao',
            'fieldset' => 'Outro Vínculo:',
            'label' => 'Instituição:',
            'tipo' => 'texto',
            'size' => 200,
            'col' => 6,
            'title' => 'Instituição Pública.',
            'linha' => 3),
        array('nome' => 'cargo',
            'label' => 'Cargo:',
            'tipo' => 'texto',
            'size' => 200,
            'col' => 6,
            'title' => 'Cargo na outra Instituição.',
            'linha' => 3),
        array('nome' => 'matricula',
            'label' => 'Matrícula:',
            'tipo' => 'texto',
            'size' => 20,
            'col' => 2,
            'title' => 'Matrícula da outra instituição.',
            'linha' => 4),
        array('nome' => 'dtAdmissao',
            'label' => 'Data de Admissão:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data de admissão da outra instituição',
            'linha' => 4),
        array('nome' => 'dtSaida',
            'label' => 'Data da Saída:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data de aposentadoria na outra instituição',
            'linha' => 4),
        array('linha' => 4,
            'nome' => 'motivoSaida',
            'label' => 'Motivo:',
            'tipo' => 'combo',
            'array' => $motivo,
            'col' => 4,
            'size' => 30,
            'title' => 'Motivo da saida do servidor no outro vínculo.'),
        array('nome' => 'resultado',
            'fieldset' => 'fecha',
            'label' => 'Resultado:',
            'tipo' => 'combo',
            'array' => array(
                array(null, null),
                array(1, "Lícito"),
                array(2, "Ilícito")),
            'size' => 2,
            'valor' => null,
            'col' => 2,
            'title' => 'Resultado.',
            'linha' => 5),
        array('nome' => 'dtPublicacao',
            'label' => 'Data da Publicação:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data da publicação.',
            'linha' => 5),
        array('nome' => 'pgPublicacao',
            'label' => 'Página:',
            'tipo' => 'texto',
            'size' => 10,
            'col' => 2,
            'title' => 'A página da Publicação no DOERJ.',
            'linha' => 5),
        array('nome' => 'resultado1',
            'fieldset' => 'Recursos:',
            'label' => 'Recurso 1:',
            'tipo' => 'combo',
            'array' => array(
                array(null, null),
                array(1, "Lícito"),
                array(2, "Ilícito")),
            'size' => 2,
            'valor' => null,
            'col' => 2,
            'title' => 'Resultado.',
            'linha' => 6),
        array('nome' => 'dtPublicacao1',
            'label' => 'Data da Publicação:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data da publicação.',
            'linha' => 6),
        array('nome' => 'pgPublicacao1',
            'label' => 'Página:',
            'tipo' => 'texto',
            'size' => 10,
            'col' => 2,
            'title' => 'A página da Publicação no DOERJ.',
            'linha' => 6),
        array('nome' => 'resultado2',
            'label' => 'Recurso 2:',
            'tipo' => 'combo',
            'array' => array(
                array(null, null),
                array(1, "Lícito"),
                array(2, "Ilícito")),
            'size' => 2,
            'valor' => null,
            'col' => 2,
            'title' => 'Resultado.',
            'linha' => 7),
        array('nome' => 'dtPublicacao2',
            'label' => 'Data da Publicação:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data da publicação.',
            'linha' => 7),
        array('nome' => 'pgPublicacao2',
            'label' => 'Página:',
            'tipo' => 'texto',
            'size' => 10,
            'col' => 2,
            'title' => 'A página da Publicação no DOERJ.',
            'linha' => 7),
        array('nome' => 'resultado3',
            'label' => 'Recurso 3:',
            'tipo' => 'combo',
            'array' => array(
                array(null, null),
                array(1, "Lícito"),
                array(2, "Ilícito")),
            'size' => 2,
            'valor' => null,
            'col' => 2,
            'title' => 'Resultado.',
            'linha' => 8),
        array('nome' => 'dtPublicacao3',
            'label' => 'Data da Publicação:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data da publicação.',
            'linha' => 8),
        array('nome' => 'pgPublicacao3',
            'label' => 'Página:',
            'tipo' => 'texto',
            'size' => 10,
            'col' => 2,
            'title' => 'A página da Publicação no DOERJ.',
            'linha' => 8),
        array('nome' => 'conclusao',
            'label' => 'Conclusão:',
            'fieldset' => 'fecha',
            'tipo' => 'combo',
            'array' => array(array(null, null),
                array(1, "Pendente"),
                array(2, "Resolvido")),
            'size' => 2,
            'required' => true,
            'valor' => null,
            'col' => 4,
            'title' => 'Conclusão.',
            'linha' => 9),
        array('linha' => 10,
            'col' => 12,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5)),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 11)));

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Acumulação de Cargo");
    $botaoRel->set_url("../grhRelatorios/servidorAcumulacao.php");
    $botaoRel->set_target("_blank");

    # Procedimentos
    $linkBotao3 = new Link("Procedimentos", "?fase=procedimentos");
    $linkBotao3->set_class('button');
    $linkBotao3->set_title('Procedimentos');
    $linkBotao3->set_target("_blank");

    # Site
    $botaoSite = new Button("Site da GRH");
    $botaoSite->set_target('_blank');
    $botaoSite->set_title("Pagina no site da GRH sobre Redução da Carga Horária");
    $botaoSite->set_url("https://uenf.br/dga/grh/gerencia-de-recursos-humanos/acumulacao-de-cargos/");

    # Botão exibe declaração
    $botaoDec = new Button("Declarações");
    $botaoDec->set_title("Exibe as declaração positivas de acumulação deste servidor");
    $botaoDec->set_url("servidorAcumulacaoDeclaracao.php");

    $objeto->set_botaoListarExtra([$botaoRel, $linkBotao3, $botaoSite, $botaoDec]);
    
    # Botão exibe declaração
    $botaoDec = new Button("Declarações");
    $botaoDec->set_title("Exibe as declaração positivas de acumulação deste servidor");
    $botaoDec->set_onClick("abreFechaDivId('divRegrasLsv');");

    $objeto->set_botaoEditarExtra([$linkBotao3, $botaoDec]);

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
        case "editar" :
        case "excluir" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id, "servidorAcumulacaoExtra.php");
            break;

        case "regras" :
            $regra = new Procedimento();
            $regra->exibeProcedimento(24);
            break;

        ###################################################################

        case "procedimentos" :
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            $rotina = new Rotina();
            $rotina->exibeRotina(7);

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ###################################################################
        # Despacho: Solicitação de Documentos
        case "despachoSolicitacaoDocumentos" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # Título
            tituloTable("Despacho: Solicitação de Documentos");
            br();

            # Pega os dados da combo assinatura
            $select = 'SELECT idServidor,
                              tbpessoa.nome
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbhistlot.lotacao = 66
                          AND situacao = 1
                     ORDER BY tbpessoa.nome asc';

            $lista = $pessoal->select($select);

            # Monta o formulário
            $form = new Form("../grhRelatorios/despacho.Acumulacao.SolicitacaoDocumento.php?id={$id}");

            # Assinatura
            $controle = new Input('postAssinatura', 'combo', 'Assinado por:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(12);
            $controle->set_array($lista);
            $controle->set_valor($intra->get_idServidor($idUsuario));
            $controle->set_autofocus(true);
            $controle->set_required(true);
            $controle->set_title('O nome do servidor da GRH que assina o despacho.');
            $form->add_item($controle);

            # submit
            $controle = new Input('salvar', 'submit');
            $controle->set_valor('Imprimir');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ###################################################################
        # Despacho: Despacho Para Análise
        case "despachoAnalise" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # Título
            tituloTable("Despacho Para Análise");
            br();

            # Pega os dados da combo assinatura
            $select = 'SELECT idServidor,
                              tbpessoa.nome
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbhistlot.lotacao = 66
                          AND situacao = 1
                     ORDER BY tbpessoa.nome asc';

            $lista = $pessoal->select($select);

            # Monta o formulário
            $form = new Form("../grhRelatorios/despacho.Acumulacao.Analise.php");

            # Assinatura
            $controle = new Input('postAssinatura', 'combo', 'Assinado por:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(12);
            $controle->set_array($lista);
            $controle->set_valor($intra->get_idServidor($idUsuario));
            $controle->set_autofocus(true);
            $controle->set_required(true);
            $controle->set_title('O nome do servidor da GRH que assina o despacho.');
            $form->add_item($controle);

            # submit
            $controle = new Input('salvar', 'submit');
            $controle->set_valor('Imprimir');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ###################################################################
        # Despacho: Ciência da Licitude
        case "despachoCienciaLicitude" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # Título
            tituloTable("Despacho: Ciência da Licitude");
            br();

            # Pega os dados da combo assinatura
            $select = 'SELECT idServidor,
                              tbpessoa.nome
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbhistlot.lotacao = 66
                          AND situacao = 1
                     ORDER BY tbpessoa.nome asc';

            $lista = $pessoal->select($select);

            # Monta o formulário
            $form = new Form("../grhRelatorios/despacho.Acumulacao.CienciaLicitude.php");

            # Assinatura
            $controle = new Input('postAssinatura', 'combo', 'Assinado por:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(6);
            $controle->set_array($lista);
            $controle->set_valor($intra->get_idServidor($idUsuario));
            $controle->set_autofocus(true);
            $controle->set_required(true);
            $controle->set_title('O nome do servidor da GRH que assina o despacho.');
            $form->add_item($controle);

            # número do documento da publicação no SEI
            $controle = new Input('numDocumento', 'texto', 'Nº do documento da publicação no SEI:', 1);
            $controle->set_size(200);
            $controle->set_linha(1);
            $controle->set_col(6);
            $controle->set_required(true);
            $controle->set_title('O número do documento da publicação no SEI.');
            $form->add_item($controle);

            # submit
            $controle = new Input('salvar', 'submit');
            $controle->set_valor('Imprimir');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ###################################################################
        # Despacho de Conclusão Temporária
        case "despachoConclusaoTemporaria" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # Título
            tituloTable("Despacho de Conclusão Temporária");
            br();

            # Pega os dados da combo assinatura
            $select = 'SELECT idServidor,
                              tbpessoa.nome
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbhistlot.lotacao = 66
                          AND situacao = 1
                     ORDER BY tbpessoa.nome asc';

            $lista = $pessoal->select($select);

            # Monta o formulário
            $form = new Form("../grhRelatorios/despacho.Acumulacao.ConclusaoTemporaria.php");

            # Assinatura
            $controle = new Input('postAssinatura', 'combo', 'Assinado por:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(12);
            $controle->set_array($lista);
            $controle->set_valor($intra->get_idServidor($idUsuario));
            $controle->set_autofocus(true);
            $controle->set_required(true);
            $controle->set_title('O nome do servidor da GRH que assina o despacho.');
            $form->add_item($controle);

            # submit
            $controle = new Input('salvar', 'submit');
            $controle->set_valor('Imprimir');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ###################################################################
        # Despacho: Ciência de Ilicitude
        case "despachoCienciaIlicitude" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # idServidor do chefe
            $idChefiaImediataDestino = $pessoal->get_chefiaImediata($idServidorPesquisado);

            # Nome do chefe
            $nomeGerenteDestino = $pessoal->get_nome($idChefiaImediataDestino);

            # Descrição do cargo
            $gerenciaImediataDescricao = $pessoal->get_chefiaImediataDescricao($idServidorPesquisado);

            # Título
            tituloTable("Despacho: Ciência de Ilicitude");
            br();

            # Pega os dados da combo assinatura
            $select = 'SELECT idServidor,
                              tbpessoa.nome
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbhistlot.lotacao = 66
                          AND situacao = 1
                     ORDER BY tbpessoa.nome asc';

            $lista = $pessoal->select($select);

            # Monta o formulário
            $form = new Form("../grhRelatorios/despacho.Acumulacao.CienciaIlicitude.php?id={$id}");

            # Assinatura
            $controle = new Input('postAssinatura', 'combo', 'Assinado por:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(12);
            $controle->set_array($lista);
            $controle->set_valor($intra->get_idServidor($idUsuario));
            $controle->set_autofocus(true);
            $controle->set_required(true);
            $controle->set_title('O nome do servidor da GRH que assina o despacho.');
            $form->add_item($controle);

            # Chefia
            $controle = new Input('chefia', 'texto', 'Chefia Imediata do Servidor Solicitante:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(12);
            $controle->set_valor($nomeGerenteDestino);
            $controle->set_required(true);
            $controle->set_title('O nome da chefia imediata.');
            $form->add_item($controle);

            # Cargo
            $controle = new Input('cargo', 'texto', 'Cargo da Chefia:', 1);
            $controle->set_size(200);
            $controle->set_linha(3);
            $controle->set_col(12);
            $controle->set_valor($gerenciaImediataDescricao);
            $controle->set_required(true);
            $controle->set_title('O Cargo em comissão da chefia.');
            $form->add_item($controle);

            # número do documento da publicação no SEI
            $controle = new Input('numDocumento', 'texto', 'Número do documento da publicação no SEI:', 1);
            $controle->set_size(200);
            $controle->set_linha(4);
            $controle->set_col(12);
            $controle->set_required(true);
            $controle->set_title('O número do documento da publicação no SEI.');
            $form->add_item($controle);

            # submit
            $controle = new Input('salvar', 'submit');
            $controle->set_valor('Imprimir');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ###################################################################
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}