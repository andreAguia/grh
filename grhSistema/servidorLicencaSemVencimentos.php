<?php

/**
 * Histórico de Licença Sem Vencimentos de um Servidor
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $lsv = new LicencaSemVencimentos();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Histórico de licenças sem vencimentos";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica de onde veio
    $origem = get_session("origem");

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

    # Rotina em Jscript
    $script = '<script type="text/javascript" language="javascript">
        
            $(document).ready(function(){
            
                // Quando muda a data de término
                 $("#dtTermino").change(function(){
                    var dt1 = $("#dtInicial").val();
                    var dt2 = $("#dtTermino").val();
                    
                    data1 = new Date(dt1);
                    data2 = new Date(dt2);
                    
                    dias = (data2 - data1)/(1000*3600*24)+1;

                    $("#numDias").val(dias);
                  });                  

                 // Quando muda o período 
                 $("#numDias").change(function(){
                   
                    var dt1 = $("#dtInicial").val();
                    var numDias = $("#numDias").val();
                    
                    data1 = new Date(dt1);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));
                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");
            
                    $("#dtTermino").val(formatado);
                  });
                  
                // Quando muda a data Inicial
                $("#dtInicial").change(function(){
                   
                    var dt1 = $("#dtInicial").val();
                    var numDias = $("#numDias").val();
                    
                    data1 = new Date(dt1);
                    data2 = new Date(data1.getTime() + (numDias * 24 * 60 * 60 * 1000));
                    
                    formatado = data2.getFullYear() + "-" + (data2.getMonth() + 1).toString().padStart(2, "0") + "-" + data2.getDate().toString().padStart(2, "0");
            
                    $("#dtTermino").val(formatado);
                  });
                  
                // Quando muda a data de retorno
                 $("#dtRetorno").change(function(){
                    
                    var dt1 = $("#dtRetorno").val();
                    
                    if(dt1 != ""){
                        data1 = new Date(dt1);                    
                        data1.setDate(data1.getDate() - 0);                    
                        formatado = data1.getFullYear() + "-" + (data1.getMonth() + 1).toString().padStart(2, "0") + "-" + data1.getDate().toString().padStart(2, "0");

                        $("#dtTermino").val(formatado);

                        var dt1 = $("#dtInicial").val();
                        var dt2 = $("#dtTermino").val();

                        data1 = new Date(dt1);
                        data2 = new Date(dt2);

                        dias = (data2 - data1)/(1000*3600*24)+1;

                        $("#numDias").val(dias);
                    }
                    
                  });      
                  
                });
             </script>';

    # Começa uma nova página
    $page = new Page();
    if ($fase == "editar") {
        $page->set_jscript($script);
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);

    $objeto->set_rotinaExtraListar("exibeRegraStatusLSV");
    $objeto->set_rotinaExtraEditar("exibeRegraStatusLSV");

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Hstórico de Licença / Afastamentos Sem Vencimentos');

    # botão de voltar da lista
    if (empty($origem)) {
        $objeto->set_voltarLista('servidorMenu.php');
    } else {
        $objeto->set_voltarLista($origem);
    }

    # select da lista
    $objeto->set_selectLista("SELECT idLicencaSemVencimentos,
                                     idLicencaSemVencimentos,
                                     CASE tipo
                                         WHEN 1 THEN 'Inicial'
                                         WHEN 2 THEN 'Renovação'
                                         ELSE '--'
                                     END,
                                     idTpLicenca,
                                     idLicencaSemVencimentos,
                                     idLicencaSemVencimentos, 
                                     idLicencaSemVencimentos,
                                     idLicencaSemVencimentos
                                FROM tblicencasemvencimentos
                          WHERE idServidor={$idServidorPesquisado}
                       ORDER BY dtSolicitacao desc, dtInicial desc");

    # select do edita
    $objeto->set_selectEdita("SELECT idTpLicenca,
                                     tipo,
                                     dtSolicitacao,
                                     processo,
                                     dtPublicacao,
                                     pgPublicacao,
                                     dtInicial,
                                     numDias,
                                     dtTermino,
                                     dtRetorno,
                                     dtPublicacaoRetorno,
                                     pgPublicacaoRetorno,
                                     optouContribuir,
                                     crp,
                                     dtCrp,
                                     numCrp,
                                     numSeiCrp,
                                     obs,
                                     idServidor
                                FROM tblicencasemvencimentos
                               WHERE idLicencaSemVencimentos = {$id}");

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
    $objeto->set_label(["#", "Status", "Tipo", "Tipo", "Dados", "Período", "Rioprevidência", "Documentos"]);
    #$objeto->set_width([10, 5, 15, 20, 20, 5, 20]);
    $objeto->set_align([null, "center", "center", "left", "left", "left"]);
    $objeto->set_classe([null, "LicencaSemVencimentos", null, "LicencaSemVencimentos", "LicencaSemVencimentos", "LicencaSemVencimentos", "LicencaSemVencimentos", "LicencaSemVencimentos"]);
    $objeto->set_metodo([null, "exibeStatus", null, "get_nomeLicenca", "exibeDados", "exibePeriodo", "exibeRioprevidencia", "exibeBotaoDocumentos"]);

    $objeto->set_formatacaoCondicional(array(
        array('coluna' => 1,
            'valor' => 'Em Aberto',
            'operador' => '=',
            'id' => 'emAberto'),
        array('coluna' => 1,
            'valor' => 'Arquivado',
            'operador' => '=',
            'id' => 'arquivado'),
        array('coluna' => 1,
            'valor' => 'Aguardando CRP',
            'operador' => '=',
            'id' => 'agurdando'),
        array('coluna' => 1,
            'valor' => 'INCOMPLETO',
            'operador' => '=',
            'id' => 'incompleto'),
        array('coluna' => 1,
            'valor' => 'Vigente',
            'operador' => '=',
            'id' => 'vigenteReducao')
    ));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tblicencasemvencimentos');

    # Nome do campo id
    $objeto->set_idCampo('idLicencaSemVencimentos');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega os dados da combo licenca
    $result = $pessoal->select('SELECT idTpLicenca, tbtipolicenca.nome
                                  FROM tbtipolicenca
                                 WHERE (idTpLicenca = 5) 
                                    OR (idTpLicenca = 8) 
                                    OR (idTpLicenca = 16)
                                    OR (idTpLicenca = 37)
                              ORDER BY 2');
    array_unshift($result, array(null, ' -- Selecione o Tipo de Afastamento ou Licença --')); # Adiciona o valor de nulo
    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'idTpLicenca',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'size' => 50,
            'array' => $result,
            'required' => true,
            'autofocus' => true,
            'title' => 'Tipo do Adastamento/Licença.',
            'col' => 10,
            'linha' => 1),
        array('nome' => 'tipo',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'array' => array(array(null, null),
                array(1, "Inicial"),
                array(2, "Renovação")),
            'required' => true,
            'size' => 2,
            'valor' => 0,
            'col' => 2,
            'title' => 'Se é inicial ou renovação.',
            'linha' => 1),
        array('nome' => 'dtSolicitacao',
            'label' => 'Solicitado em:',
            'tipo' => 'data',
            'size' => 30,
            'title' => 'A data da Solicitação.',
            'col' => 3,
            'linha' => 2),
        array('nome' => 'processo',
            'label' => 'Processo:',
            'tipo' => 'texto',
            'size' => 30,
            'col' => 4,
            'title' => 'Número do Processo',
            'linha' => 2),
        array('nome' => 'dtPublicacao',
            'label' => 'Data da Publicação:',
            'tipo' => 'data',
            'size' => 10,
            'col' => 3,
            'title' => 'A Data da Publicação.',
            'linha' => 2),
        array('nome' => 'pgPublicacao',
            'label' => 'Página:',
            'tipo' => 'texto',
            'size' => 5,
            'col' => 2,
            'title' => 'A página da Publicação no DOERJ.',
            'linha' => 2),
        array('nome' => 'dtInicial',
            'label' => 'Data Inicial:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data do início.',
            'linha' => 4),
        array('nome' => 'numDias',
            'label' => 'Dias:',
            'tipo' => 'numero',
            'min' => 1,
            'size' => 5,
            'title' => 'Número de dias.',
            'col' => 2,
            'linha' => 4),
        array('nome' => 'dtTermino',
            'label' => 'Data de Termino:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data de Termino.',
            'linha' => 4),
        array('nome' => 'dtRetorno',
            'label' => 'Data de Retorno:',
            'tipo' => 'data',
            'size' => 10,
            'col' => 3,
            'title' => 'Data do início.',
            'linha' => 6),
        array('nome' => 'dtPublicacaoRetorno',
            'label' => 'Data da Publicação de Retorno:',
            'tipo' => 'data',
            'size' => 10,
            'col' => 3,
            'title' => 'A Data da Publicação.',
            'linha' => 6),
        array('nome' => 'pgPublicacaoRetorno',
            'label' => 'Página:',
            'tipo' => 'texto',
            'size' => 5,
            'col' => 2,
            'title' => 'A página da Publicação no DOERJ.',
            'linha' => 6),
        array('linha' => 7,
            'col' => 3,
            'nome' => 'optouContribuir',
            'title' => 'Informa se o servidor optou em contribuir para o Rioprevidência',
            'label' => 'Optou Pagar o Rioprevidência?',
            'tipo' => 'combo',
            'array' => [[null, null], [1, "Sim"], [2, "Não"]],
            'size' => 20),
        array('linha' => 8,
            'col' => 2,
            'nome' => 'crp',
            'title' => 'informa se entregou CRP',
            'label' => 'entregou CRP',
            'tipo' => 'combo',
            'array' => array(array(false, "Não"),
                array(true, "Sim")),
            'size' => 10),
        array('nome' => 'dtCrp',
            'label' => 'Data de emissão da CRP:',
            'tipo' => 'data',
            'size' => 10,
            'col' => 3,
            'title' => 'Data do início.',
            'linha' => 8),
        array('nome' => 'numCrp',
            'label' => 'Número do CRP:',
            'tipo' => 'texto',
            'size' => 10,
            'col' => 3,
            'title' => 'Número CRP.',
            'linha' => 8),
        array('nome' => 'numSeiCrp',
            'label' => 'Número do documento CRP no SEI:',
            'tipo' => 'texto',
            'size' => 10,
            'col' => 3,
            'title' => 'Número do documento no SEI.',
            'linha' => 8),
        array('linha' => 12,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 3)),
        array('nome' => 'idServidor',
            'label' => 'idServidor',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'linha' => 8)));

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Formação");
    $botaoRel->set_onClick("window.open('../grhRelatorios/servidorLicencaSemVencimentos.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");

    # Status
    $botao2 = new Button("Status");
    $botao2->set_title("Exibe as regras de mudança automática do status");
    $botao2->set_onClick("abreFechaDivId('divRegrasLsv');");

    # Calendário
    $botaoCalendario = new Link("Calendário", "calendario.php");
    $botaoCalendario->set_class('button');
    $botaoCalendario->set_title('Exibe o calendário');
    $botaoCalendario->set_target("_calenmdario");

    # Procedimentos
    $botaoProcedimentos = new Link("Procedimentos", "?fase=procedimentos");
    $botaoProcedimentos->set_class('button');
    $botaoProcedimentos->set_title('Exibe os procedimentos');
    $botaoProcedimentos->set_target("_blank4");

    $objeto->set_botaoListarExtra([$botaoRel, $botao2, $botaoProcedimentos, $botaoCalendario]);
    $objeto->set_botaoEditarExtra([$botao2, $botaoProcedimentos]);

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
            $objeto->gravar($id, "servidorLicencaSemVencimentosExtra.php");
            break;

        ################################################################################################################
        # Carta Reassunção
        case "cartaReassuncao" :

            # Pega os Dados
            $dados = $lsv->get_dados($id);
            $dtRetorno = $dados["dtRetorno"];
            $dtPublicacao = $dados['dtPublicacao'];
            $pgPublicacao = $dados['pgPublicacao'];

            # Chefia imediata desse servidor
            $idChefiaImediataDestino = $pessoal->get_chefiaImediata($idServidorPesquisado);
            $nomeGerenteDestino = $pessoal->get_nome($idChefiaImediataDestino);
            $gerenciaImediataDescricao = $pessoal->get_chefiaImediataDescricao($idServidorPesquisado);

            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(12);
            br();

            # Título
            tituloTable("Carta de Reassunção de Servidor");
            br();

            # Monta o formulário para confirmação dos dados necessários a emissão da CI
            $form = new Form("../grhRelatorios/lsv.cartaReassuncao.php?id={$id}");

            # dtRetorno
            $controle = new Input('dtRetorno', 'data', 'Data do Retorno', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_valor($dtRetorno);
            $controle->set_autofocus(true);
            $controle->set_title('A data do retorno do servidor.');
            $form->add_item($controle);

            # dtPublicacao
            $controle = new Input('dtPublicacao', 'data', 'Data da Publicação:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_valor($dtPublicacao);
            #$controle->set_required(true);
            $controle->set_title('A data da publicação no DOERJ.');
            $form->add_item($controle);

            # pgPublicacao
            $controle = new Input('pgPublicacao', 'texto', 'Página:', 1);
            $controle->set_size(5);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_valor($pgPublicacao);
            #$controle->set_required(true);
            $controle->set_title('A pag da publicação no DOERJ.');
            $form->add_item($controle);

            # Chefia
            $controle = new Input('chefia', 'texto', 'Chefia:', 1);
            $controle->set_size(200);
            $controle->set_linha(3);
            $controle->set_col(12);
            $controle->set_valor($nomeGerenteDestino);
            #$controle->set_required(true);
            $controle->set_title('O nome da chefia imediata.');
            $form->add_item($controle);

            # Cargo
            $controle = new Input('cargo', 'texto', 'Cargo:', 1);
            $controle->set_size(200);
            $controle->set_linha(4);
            $controle->set_col(12);
            $controle->set_valor($gerenciaImediataDescricao);
            #$controle->set_required(true);
            $controle->set_title('O Cargo em comissão da chefia.');
            $form->add_item($controle);

            # submit
            $controle = new Input('imprimir', 'submit');
            $controle->set_valor('Salvar & Imprimir');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ############################################################################

        case "procedimentos" :

            br();
            $procedimento = new Procedimento();
            $procedimento->exibeProcedimentoSubCategoria("Licença Sem Vencimentos");
            break;

        ############################################################################    
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}