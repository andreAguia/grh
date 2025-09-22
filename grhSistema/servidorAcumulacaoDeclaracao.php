<?php

/**
 * Cadastro de Feriados
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
        $atividade = "Cadastro do servidor - Controle de entrega de declaração de acumulações de cargos públicos";
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
    if ($fase == "despachoCorrecao") {
        $page->set_jscript('<script>CKEDITOR.replace("dados");</script>');
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra(["get_DadosServidor", "exibeProcessosAcumulacao"]);
    $objeto->set_rotinaExtraParametro([$idServidorPesquisado, $idServidorPesquisado]);

    # Rotina extra editar
    $objeto->set_rotinaExtraEditar("exibeProcessosAcumulacao");
    $objeto->set_rotinaExtraEditarParametro($idServidorPesquisado);

    # Rotina extra editar
    $objeto->set_rotinaExtraListar("exibeDocumentosDeclaracaoAcumulacao");
    $objeto->set_rotinaExtraListarParametro($idServidorPesquisado);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Declaração Anual de Acumulação de Cargo Público');

    # botão de voltar da lista
    if (empty($origem)) {
        $voltar = 'servidorMenu.php';
    } else {
        $voltar = $origem;
    }

    # botão de voltar da lista
    $objeto->set_voltarLista($voltar);

    # select da lista
    $objeto->set_selectLista("SELECT anoReferencia,
                       dtEntrega, 
                       IF(acumula,'<span id=\'vermelho\'>SIM</span>','<span id=\'verde\'>Não</span>'),
                       processo,
                       obs,
                       idAcumulacaoDeclaracao,
                       idAcumulacaoDeclaracao
                  FROM tbacumulacaodeclaracao 
                WHERE idServidor = {$idServidorPesquisado}
                ORDER BY anoReferencia desc");

    # select do edita
    $objeto->set_selectEdita('SELECT anoReferencia,
                                     dtEntrega,
                                     processo,
                                     acumula,
                                     obs,
                                     idServidor
                                FROM tbacumulacaodeclaracao
                               WHERE idAcumulacaoDeclaracao = ' . $id);

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["Referência", "Entregue em", "Acumula?", "Processo", "Obs"]);
    $objeto->set_width([10, 15, 10, 20, 35]);
    $objeto->set_align(["center", "center", "center", "left", "left"]);
    $objeto->set_funcao([null, "date_to_php"]);

    $objeto->set_formatacaoCondicional(array(
        array('coluna' => 2,
            'valor' => 'SIM',
            'operador' => '=',
            'id' => 'problemas')));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbacumulacaodeclaracao');

    # Nome do campo id
    $objeto->set_idCampo('idAcumulacaoDeclaracao');

    # servidor
    $servidor = $pessoal->select('SELECT idServidor, tbpessoa.nome
                                   FROM tbservidor JOIN tbpessoa USING(idPessoa)
                                  WHERE idPerfil = 1
                               ORDER BY situacao, tbpessoa.nome');
    array_unshift($servidor, [null, null]);

    $declaracao = new AcumulacaoDeclaracao();
    $anoDisponível = $declaracao->getProximoAnoReferencia($idServidorPesquisado);

    # Cria um array com os anos possíveis
    $anoInicial = 2019;
    $anoAtual = date('Y');
    $anoExercicio = arrayPreenche($anoInicial, $anoAtual + 1, "d");

    # Campos para o formulario
    $objeto->set_campos(array(
        array(
            'linha' => 1,
            'nome' => 'anoReferencia',
            'label' => 'Ano Referência:',
            'tipo' => 'combo',
            'array' => $anoExercicio,
            'padrao' => $anoDisponível,
            'required' => true,
            'col' => 2,
            'size' => 8),
        array(
            'nome' => 'dtEntrega',
            'label' => 'Data da Entrega:',
            'tipo' => 'date',
            'size' => 20,
            'required' => true,
            "autofocus" => true,
            'title' => 'Data da entega',
            'col' => 3,
            'padrao' => date('Y-m-d'),
            'linha' => 1),
        array(
            'linha' => 1,
            'nome' => 'processo',
            'label' => 'Processo:',
            'tipo' => 'texto',
            'required' => true,
            'col' => 4,
            'size' => 50),
        array(
            'linha' => 1,
            'nome' => 'acumula',
            'label' => 'Acumula?:',
            'tipo' => 'simnao',
            'col' => 3,
            'size' => 5),
        array(
            'linha' => 2,
            'col' => 12,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5)),
        array(
            'nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 3)));

    # Procedimentos
    $botaoProcedimentos = new Link("Procedimentos", "?fase=procedimentos");
    $botaoProcedimentos->set_class('button');
    $botaoProcedimentos->set_title('Procedimentos');
    $botaoProcedimentos->set_target("_blank");

    # Site
    $botaoSite = new Link("Site", "https://uenf.br/dga/grh/gerencia-de-recursos-humanos/acumulacao-de-cargos/declaracao-anual-de-acumulacao-de-cargos/");
    $botaoSite->set_class('button');
    $botaoSite->set_title('Site da GRH');
    $botaoSite->set_target("_blank");

    # Botão exibe Processos
    $botaoDec = new Button("Processos de ACP");
    $botaoDec->set_title("Exibe os Processos de acumulação deste servidor");
//    $botaoDec->set_onClick("abreFechaDivId('divRegrasLsv');");
    $botaoDec->set_url("servidorAcumulacao.php");

    $objeto->set_botaoListarExtra([$botaoProcedimentos, $botaoSite, $botaoDec]);

    # Botão exibe Processos
    $botaoDec = new Button("Processos de ACP");
    $botaoDec->set_title("Exibe os Processos de acumulação deste servidor");
    $botaoDec->set_onClick("abreFechaDivId('divRegrasLsv');");

    $objeto->set_botaoEditarExtra([$botaoProcedimentos, $botaoSite, $botaoDec]);

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################
    switch ($fase) {

        case "" :
        case "listar" :

            $objeto->listar();
            break;

        case "editar" :
        case "excluir" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id, "servidorAcumulacaoDeclaracaoExtra.php");
            break;

        ###################################################################

        case "procedimentos" :
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            $procedimento = new Procedimento();
            $procedimento->exibeProcedimentoSubCategoria("Declaração Anual de Acumulação");

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ###################################################################
        # Despacho: Solicitação de Declaração Pendente
        case "despachoDeclaracaoPendente" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            $declaracao = new AcumulacaoDeclaracao();

            # Título
            tituloTable("Despacho: Solicitação de Declaração Pendente");
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
            $form = new Form("../grhRelatorios/despacho.Acumulacao.DeclaracaoPendente.php");

            # Assinatura
            $controle = new Input('postAssinatura', 'combo', 'Assinado por:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(8);
            $controle->set_array($lista);
            $controle->set_valor($intra->get_idServidor($idUsuario));
            $controle->set_autofocus(true);
            $controle->set_required(true);
            $controle->set_title('O nome do servidor da GRH que assina o despacho.');
            $form->add_item($controle);

            # Ano da Declaração Pendente
            $controle = new Input('anoDeclaracao', 'texto', 'Ano da Declaração Pendente:', 1);
            $controle->set_size(50);
            $controle->set_linha(2);
            $controle->set_col(8);
            $controle->set_required(true);
            $controle->set_title('O Ano de referência da declaração pendente.');
            $form->add_item($controle);

            # Número do Processo
            $controle = new Input('processo', 'texto', 'Processo:', 1);
            $controle->set_size(50);
            $controle->set_linha(3);
            $controle->set_col(8);
            $controle->set_valor($declaracao->getUltimoProcessoCadastrado($idServidorPesquisado));
            $controle->set_required(true);
            $controle->set_title('O Número do processo da declaração deste servidor');
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
        # Despacho: Solicitação de Correção
        case "despachoCorrecao" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            $declaracao = new AcumulacaoDeclaracao();

            # Título
            tituloTable("Despacho: Solicitação de Correção");
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
            $form = new Form("../grhRelatorios/despacho.Acumulacao.DeclaracaoCorrecao.php");

            # Assinatura
            $controle = new Input('postAssinatura', 'combo', 'Assinado por:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(8);
            $controle->set_array($lista);
            $controle->set_valor($intra->get_idServidor($idUsuario));
            $controle->set_autofocus(true);
            $controle->set_required(true);
            $controle->set_title('O nome do servidor da GRH que assina o despacho.');
            $form->add_item($controle);

            # Ano da Declaração Pendente
            $controle = new Input('anoDeclaracao', 'texto', 'Ano(s) da Declaração:', 1);
            $controle->set_size(50);
            $controle->set_linha(2);
            $controle->set_col(8);
            $controle->set_required(true);
            $controle->set_title('O Ano de referência da declaração a ser corrigida.');
            $form->add_item($controle);

            # número do documento da publicação no SEI
            $controle = new Input('numDocumento', 'texto', 'Nº do documento da publicação no SEI:', 1);
            $controle->set_size(200);
            $controle->set_linha(3);
            $controle->set_col(6);
            $controle->set_required(true);
            $controle->set_title('O número do documento da publicação no SEI.');
            $form->add_item($controle);

            # Dados a Serem Corrigidos
            $controle = new Input('dados', 'editor', 'Dados a Serem Corrigidos:', 1);
            $controle->set_size([80, 5]);
            $controle->set_linha(4);
            $controle->set_col(12);
            $controle->set_required(true);
            $controle->set_title('Os dados a serem corrigidos');
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