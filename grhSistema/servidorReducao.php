
<?php

/**
 * Controle do Redução de Carega Horária
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
    $reducao = new ReducaoCargaHoraria($idServidorPesquisado);
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Controle da redução da carga horária";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Pega o número do processo (Quando tem)
    $processo = trataNulo($reducao->get_numProcesso());
    $processoAntigo = $reducao->get_numProcessoAntigo();

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica de onde veio
    $origem = get_session("origem");

    # botão de voltar da lista
    if (empty($origem)) {
        $voltar = 'servidorMenu.php';
    } else {
        $voltar = $origem;
    }

    $jscript = '// Pega os valores da pendêencia                
                
                // Verifica o valor da pendência quando o form é carregado
                if($("#pendencia").is(":checked")){
                    $("#dadosPendencia").show();
                    $("#dtEnvioPendencia").show();
                    $("#div9").show();
                }else{
                    $("#dadosPendencia").hide();
                    $("#dtEnvioPendencia").hide();
                    $("#div9").hide();
                }
                
                // Pega os valores do resultado
                var resultado = $("#resultado").val();

                // Verifica o valor do resultado quando o form é carregado
                if(resultado == 1){
                    $("#dtPublicacao").show();
                    $("#dtInicio").show();
                    $("#periodo").show();
                    $("#numCiInicio").show();
                    $("#numCiTermino").show();                    
                    $("#div11").show();
                }else{
                    $("#dtPublicacao").hide();
                    $("#dtInicio").hide();
                    $("#periodo").hide();
                    $("#numCiInicio").hide();
                    $("#numCiTermino").hide();                    
                    $("#div11").hide();
                }
        
                // Verifica o valor da pendência quando se muda o valor do campo
                $("#pendencia").click(function(){
                    
                    if($("#pendencia").is(":checked")){
                        $("#dadosPendencia").show();
                        $("#dtEnvioPendencia").show();
                        $("#div9").show();
                    }else{
                        $("#dadosPendencia").hide();
                        $("#dtEnvioPendencia").hide();
                        $("#div9").hide();
                    }
                });
                
                // Verifica o valor do resultado quando se muda o valor do campo
                $("#resultado").change(function(){
                    var resultado = $("#resultado").val();
                    
                    if(resultado == 1){
                        $("#dtPublicacao").show();
                        $("#dtInicio").show();
                        $("#periodo").show();
                        $("#numCiInicio").show();
                        $("#numCiTermino").show();                    
                        $("#div11").show();
                    }else{
                        $("#dtPublicacao").hide();
                        $("#dtInicio").hide();
                        $("#periodo").hide();
                        $("#numCiInicio").hide();
                        $("#numCiTermino").hide();                    
                        $("#div11").hide();
                    }                
                });
                
';

    # Começa uma nova página
    $page = new Page();

    if ($fase == "editar") {
        $page->set_ready($jscript);
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    if ($fase == "listar") {
        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);

        # Cria um menu
        $menu = new MenuBar();

        # Botão voltar
        $linkBotao1 = new Link("Voltar", $voltar);
        $linkBotao1->set_class('button');
        $linkBotao1->set_title('Volta para a página anterior');
        $linkBotao1->set_accessKey('V');
        $menu->add_link($linkBotao1, "left");

        if ($processo <> "--") {
            if (Verifica::acesso($idUsuario, [1, 2])) {
                # Incluir
                $linkBotao2 = new Link("Incluir", '?fase=editar');
                $linkBotao2->set_class('button');
                $linkBotao2->set_title('Incluir uma nova solicitação de redução');
                $linkBotao2->set_accessKey('I');
                $menu->add_link($linkBotao2, "right");
            }
        }

        # Site
        $botaoSite = new Button("Site da GRH");
        $botaoSite->set_target('_blank');
        $botaoSite->set_title("Pagina no site da GRH sobre Redução da Carga Horária");
        $botaoSite->set_url("http://uenf.br/dga/grh/gerencia-de-recursos-humanos/reducao-de-carga-horaria/");
        $menu->add_link($botaoSite, "right");

        # Legislação
        $botaoLegis = new Button("Legislação");
        $botaoLegis->set_disabled(true);
        $botaoLegis->set_title('Exibe as Legislação pertinente');
        #$botaoLegis->set_onClick("window.open('https://docs.google.com/document/d/e/2PACX-1vRfb7P06MCBHAwd15hKm6KWV4-y0I8yBzlac58uAA-xCHeaL9aCbtSGCgGguZzaPQafvXYvGqWhwG0r/pub','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
        #$menu->add_link($botaoLegis,"right");
        # Relatório
        $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
        $botaoRel = new Button();
        $botaoRel->set_imagem($imagem);
        $botaoRel->set_title("Imprimir Relatório de Histórico de Processo de redução da carga horária");
        $botaoRel->set_url("../grhRelatorios/servidorReducao.php");
        $botaoRel->set_target("_blank");
        $menu->add_link($botaoRel, "right");

        # Fluxograma
        $imagem = new Imagem(PASTA_FIGURAS . 'fluxograma.png', null, 15, 15);
        $botaoFluxo = new Button();
        $botaoFluxo->set_imagem($imagem);
        $botaoFluxo->set_title("Exibe o Fluxograma de todo o processo redução da carga horária");
        $botaoFluxo->set_url("../_diagramas/reducao2.jpg");
        $botaoFluxo->set_target("_blank");
        $menu->add_link($botaoFluxo, "right");

        $menu->show();

        $objeto->set_botaoVoltarLista(false);
        $objeto->set_botaoIncluir(false);

        $grid->fechaColuna();
        $grid->fechaGrid();

        get_DadosServidor($idServidorPesquisado);
    } else {
        # Exibe os dados do Servidor
        $objeto->set_rotinaExtra("get_DadosServidor");
        $objeto->set_rotinaExtraParametro($idServidorPesquisado);
    }

    ################################################################
    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Controle de Redução da Carga Horária');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT CASE tipo
                                         WHEN 1 THEN "Inicial"
                                         WHEN 2 THEN "Renovação"
                                         ELSE "--"
                                     END,
                                     idReducao,
                                     dtSolicitacao,
                                     idReducao,
                                     idReducao,
                                     idReducao,
                                     idReducao,
                                     idReducao,
                                     ADDDATE(dtInicio,INTERVAL periodo MONTH) as dtTermino,
                                     idReducao
                                FROM tbreducao
                               WHERE idServidor = ' . $idServidorPesquisado . '
                            ORDER BY status, dtTermino, dtInicio');

    # select do edita
    $objeto->set_selectEdita('SELECT dtSolicitacao,
                                     tipo,
                                     status,
                                     dtEnvioPericia,
                                     dtChegadaPericia,
                                     dtAgendadaPericia,
                                     pendencia,
                                     resultado,
                                     dtCiencia,
                                     dadosPendencia,
                                     dtEnvioPendencia,                                     
                                     dtPublicacao,
                                     pgPublicacao,
                                     dtInicio,
                                     periodo,
                                     obs,
                                     idServidor
                                FROM tbreducao
                               WHERE idReducao = ' . $id);

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
        array('coluna' => 1,
            'valor' => 'Em Aberto',
            'operador' => '=',
            'id' => 'emAberto'),
        array('coluna' => 1,
            'valor' => 'Arquivado',
            'operador' => '=',
            'id' => 'arquivado'),
        array('coluna' => 1,
            'valor' => 'Vigente',
            'operador' => '=',
            'id' => 'vigenteReducao'),
        array('coluna' => 1,
            'valor' => 'Aguardando Publicação',
            'operador' => '=',
            'id' => 'aguardando')
    ));

    # Parametros da tabela
    $objeto->set_label(array("Tipo", "Status", "Solicitado em:", "Pericia", "Resultado", "Publicação", "Período", "Documentos"));
    #$objeto->set_width(array(10,10,10,20,20,10,10));	
    $objeto->set_align(array("center", "center", "center", "left", "center", "center", "left", "left"));
    $objeto->set_funcao(array(null, null, "date_to_php"));

    $objeto->set_classe(array(null, "ReducaoCargaHoraria", null, "ReducaoCargaHoraria", "ReducaoCargaHoraria", "ReducaoCargaHoraria", "ReducaoCargaHoraria", "ReducaoCargaHoraria"));
    $objeto->set_metodo(array(null, "exibeStatus", null, "exibeDadosPericia", "exibeResultado", "exibePublicacao", "exibePeriodo", "exibeBotaoDocumentos"));

    # Número de Ordem
    $objeto->set_numeroOrdem(true);
    $objeto->set_numeroOrdemTipo("d");

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbreducao');

    # Nome do campo id
    $objeto->set_idCampo('idReducao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(array('nome' => 'dtSolicitacao',
            'label' => 'Solicitado em:',
            'tipo' => 'data',
            'size' => 30,
            'required' => true,
            'autofocus' => true,
            'title' => 'A data da Solicitação.',
            'col' => 3,
            'linha' => 1),
        array('nome' => 'tipo',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'array' => array(array(null, null),
                array(1, "Inicial"),
                array(2, "Renovação")),
            'size' => 2,
            'valor' => 0,
            'required' => true,
            'col' => 2,
            'title' => 'Se é inicial ou renovação.',
            'linha' => 1),
        array('nome' => 'status',
            'label' => 'Status:',
            'tipo' => 'combo',
            'array' => array(
                array(1, "Em Aberto"),
                array(2, "Vigente"),
                array(3, "Arquivado"),
                array(4, "Aguardando Publicação")),
            'size' => 2,
            'valor' => 0,
            'col' => 2,
            'disabled' => true,
            'title' => 'Se a solicitação foi arquivada ou não.',
            'linha' => 1),
        array('nome' => 'dtEnvioPericia',
            'label' => 'Data de Envio:',
            'tipo' => 'data',
            'size' => 10,
            'fieldset' => 'Da Perícia',
            'col' => 3,
            'title' => 'A data do envio do processo à perícia.',
            'linha' => 2),
        array('nome' => 'dtChegadaPericia',
            'label' => 'Data da Chegada:',
            'tipo' => 'data',
            'size' => 10,
            'col' => 3,
            'title' => 'A data da chegada do processo à perícia.',
            'linha' => 2),
        array('nome' => 'dtAgendadaPericia',
            'label' => 'Data Agendada:',
            'tipo' => 'data',
            'size' => 10,
            'col' => 3,
            'title' => 'A data agendada pela perícia.',
            'linha' => 2),
        array('nome' => 'pendencia',
            'label' => 'Há pendências:',
            'tipo' => 'simnao',
            'size' => 5,
            'title' => 'Se há pendências',
            'col' => 3,
            'linha' => 3),
        array('nome' => 'resultado',
            'label' => 'Resultado:',
            'tipo' => 'combo',
            'array' => array(array(null, ""), array(1, "Deferido"), array(2, "Indeferido"), array(3, "Interrompido")),
            'size' => 20,
            'title' => 'Se o processo foi deferido ou indeferido',
            'col' => 3,
            'linha' => 3),
        array('nome' => 'dtCiencia',
            'label' => 'Data da Ciência:',
            'tipo' => 'data',
            'size' => 10,
            'col' => 3,
            'title' => 'A data da ciência do servidor.',
            'linha' => 3),
        array('linha' => 4,
            'col' => 9,
            'nome' => 'dadosPendencia',
            'label' => 'Pendências:',
            'tipo' => 'textarea',
            'fieldset' => 'Das Pendências',
            'title' => 'Quais são as pendências.',
            'size' => array(80, 3)),
        array('nome' => 'dtEnvioPendencia',
            'label' => 'Data de Envio:',
            'tipo' => 'data',
            'size' => 10,
            'col' => 3,
            'title' => 'Data de envio das pendências da Perícia.',
            'linha' => 5),
        array('nome' => 'dtPublicacao',
            'label' => 'Data da Publicação:',
            'tipo' => 'data',
            'size' => 10,
            'col' => 3,
            'title' => 'A Data da Publicação.',
            'fieldset' => 'Quando Deferido',
            'linha' => 6),
        array('nome' => 'pgPublicacao',
            'label' => 'Página:',
            'tipo' => 'texto',
            'size' => 10,
            'col' => 2,
            'title' => 'A página da Publicação no DOERJ.',
            'linha' => 6),
        array('nome' => 'dtInicio',
            'label' => 'Data de Inicio:',
            'tipo' => 'data',
            'size' => 10,
            'col' => 3,
            'title' => 'A data em que o servidor passou a receber o benefício.',
            'linha' => 6),
        array('nome' => 'periodo',
            'label' => 'Período (Meses):',
            'tipo' => 'texto',
            'size' => 10,
            'col' => 2,
            'title' => 'O período em meses do benefício.',
            'linha' => 6),
        array('linha' => 7,
            'col' => 12,
            'nome' => 'obs',
            'label' => 'Obs:',
            'tipo' => 'textarea',
            'fieldset' => 'fecha',
            'title' => 'Observações.',
            'size' => array(80, 4)),
        array('nome' => 'idServidor',
            'label' => 'idServidor',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'linha' => 11)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            # Divide a página em 3 colunas
            $grid = new Grid();

            #########################################################################################################
            # Processo
            $grid->abreColuna(12, 4);

            #$processo = trataNulo($pessoal->get_numProcessoReducao($idServidorPesquisado));
            $painel = new Callout();
            $painel->abre();

            tituloTable("N° do Processo:");
            br();
            p($processo, 'f14', "center");

            # Verifica se tem processo antigo
            if (!is_null($processoAntigo)) {
                p($processoAntigo . "<br/>(Antigo)", "processoAntigoReducao");
            }

            $div = new Div("divEditaProcesso");
            $div->abre();
            if ($processo == "--") {
                $link = new Link("Incluir Processo", 'servidorProcessoReducao.php', "Inclui o número do processo de redução");
            } else {
                $link = new Link("Editar Processo", 'servidorProcessoReducao.php', "Edita o número do processo de redução");
            }
            $link->set_id("editaProcesso");
            $link->show();
            $div->fecha();

            $painel->fecha();

            $grid->fechaColuna();

            #########################################################################################################
            # Contatos
            $grid->abreColuna(12, 4);

            # Pega os telefones
            $telefones = $pessoal->get_telefones($idServidorPesquisado);

            # Pega os Emails
            $emailPessoal = $pessoal->get_emailPessoal($idServidorPesquisado);
            $emailUenf = $pessoal->get_emailUenf($idServidorPesquisado);
            $emailOutro = $pessoal->get_emailOutro($idServidorPesquisado);
            $emails = null;

            # junta os Emails
            if (!vazio($emailOutro)) {
                $emails .= "$emailOutro<br/>";
            }

            if (!vazio($emailPessoal)) {
                $emails .= "$emailPessoal<br/>";
            }

            if (!vazio($emailUenf)) {
                $emails .= "$emailUenf<br/>";
            }

            #$emails = trataNulo($emails);

            $painel = new Callout();
            $painel->abre();

            tituloTable("Contatos:");
            br();

            #p("Telefone(s)","center","f12");
            p($telefones, "center", "f14");
            #p("E-mail(s)","center","f12");
            p($emails, "center", "f14");

            #$div = new Div("divEditaProcesso");
            #$div->abre();
            #    $link = new Link("Editar Contatos",'?fase=editaContatos',"Edita os contatos do servidor");
            #    $link->set_id("editaProcesso");
            #    $link->show();
            #$div->fecha();  

            $painel->fecha();

            $grid->fechaColuna();

            #########################################################################################################
            # Documentos
            $grid->abreColuna(12, 4);

            $painel = new Callout();
            $painel->abre();

            tituloTable("Documentos:");
            br();

            $menu = new Menu();
            #$menu->add_item('titulo','Documentos');

            $menu->add_item("linkWindow", "Despacho ao Protocolo para Abertura de Processo", "servidorMenu.php?fase=despacho");
            $menu->add_item("linkWindow", "Despacho à Reitoria para Assinatura de Ato", "../grhRelatorios/despacho.Reitoria.php");
            $menu->add_item("linkWindow", "Despacho ao SEPOF para Publicação de Ato", "../grhRelatorios/despacho.Publicacao.php");
            $menu->add_item("linkWindow", "Despacho à Chefia/Servidor para Retirada do Ato", "servidorMenu.php?fase=despachoChefia");

            $menu->add_item('linkWindow', 'Declaração de Atribuições', '../grhRelatorios/declaracao.AtribuicoesCargo.php');
            $menu->add_item('linkWindow', 'Declaração de Inquérito Administrativo', '../grhRelatorios/declaracao.InqueritoAdministrativo.php');

            $menu->show();

            $painel->fecha();

            $grid->fechaColuna();
            $grid->fechaGrid();
            $objeto->listar();
            break;

#########################################################################################################

        case "editar" :
        case "excluir" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id, "servidorReducaoExtra.php");
            break;

################################################################################################################
        # Ci Início
        case "ciInicioForm" :

            # Voltar
            botaoVoltar("?");

            # Dados do Servidor
            get_DadosServidor($idServidorPesquisado);

            # Pega os Dados
            $dados = $reducao->get_dados($id);

            $numCiInicio = $dados["numCiInicio"];
            $dtCiInicio = $dados["dtCiInicio"];
            $dtInicio = $dados["dtInicio"];
            $dtPublicacao = $dados["dtPublicacao"];
            $pgPublicacao = $dados["pgPublicacao"];
            $tipo = $dados["tipo"];
            $periodo = $dados["periodo"];
            $processo = $reducao->get_numProcesso($idServidorPesquisado);

            # Chefia imediata desse servidor
            $idChefiaImediataDestino = $pessoal->get_chefiaImediata($idServidorPesquisado);              // idServidor do chefe
            $nomeGerenteDestino = $pessoal->get_nome($idChefiaImediataDestino);                          // Nome do chefe
            $gerenciaImediataDescricao = $pessoal->get_chefiaImediataDescricao($idServidorPesquisado);   // Descrição do cargo
            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(12);

            # Título
            tituloTable("Controle de Redução da Carga Horária<br/>Ci de início");
            $painel = new Callout();
            $painel->abre();

            # Monta o formulário para confirmação dos dados necessários a emissão da CI
            $form = new Form('?fase=ciInicioFormValida&id=' . $id);

            # numCiInicio
            $controle = new Input('numCiInicio', 'texto', 'Ci n°:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(2);
            #$controle->set_required(true);
            $controle->set_autofocus(true);
            $controle->set_valor($numCiInicio);
            $controle->set_title('Número da Ci informando a chefia imediata do servidor da data de início do benefício.');
            $form->add_item($controle);

            # dtCiInicio
            $controle = new Input('dtCiInicio', 'data', 'Data da Ci:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_valor($dtCiInicio);
            #$controle->set_required(true);
            $controle->set_title('A data da CI de inicio.');
            $form->add_item($controle);

            # tipo
            $controle = new Input('tipo', 'combo', 'Tipo:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_array(array(array(null, null),
                array(1, "Inicial"),
                array(2, "Renovação")));
            $controle->set_valor($tipo);
            $controle->set_title('Se é Inicial ou Renovação.');
            $form->add_item($controle);

            # servidor da grh
            $controle = new Input('servidorGrh', 'combo', 'Servidor da GRH que assina a CI:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(4);

            # Cria combo de servidores da GRH
            $select = "SELECT idServidor, tbpessoa.nome 
                 FROM tbpessoa JOIN tbservidor USING (idPessoa) 
                               JOIN tbhistlot USING (idServidor)
                               JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND tbhistlot.lotacao = 66
                 ORDER BY tbpessoa.nome";

            $servGrh = $pessoal->select($select);

            $controle->set_array($servGrh);
            $controle->set_valor($pessoal->get_gerente(66)); // Pega o idServidor do gerente da GRH (66)
            $controle->set_title('Se é Inicial ou Renovação.');
            $form->add_item($controle);

            # Chefia
            $controle = new Input('chefia', 'texto', 'Chefia:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(12);
            $controle->set_valor($nomeGerenteDestino);
            #$controle->set_required(true);
            $controle->set_title('O nome da chefia imediata.');
            $form->add_item($controle);

            # Cargo
            $controle = new Input('cargo', 'texto', 'Cargo:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(12);
            $controle->set_valor($gerenciaImediataDescricao);
            #$controle->set_required(true);
            $controle->set_title('O Cargo em comissão da chefia.');
            $form->add_item($controle);

            # submit
            $controle = new Input('salvar', 'submit');
            $controle->set_valor('Salvar');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            # submit
            $controle = new Input('imprimir', 'submit');
            $controle->set_valor('Salvar & Imprimir');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();
            $painel->fecha();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "ciInicioFormValida" :

            # Pega os Dados
            $dados = $reducao->get_dados($id);

            $numCiInicio = $dados["numCiInicio"];
            $dtCiInicio = $dados["dtCiInicio"];
            $dtInicio = date_to_php($dados['dtInicio']);
            $dtPublicacao = date_to_php($dados['dtPublicacao']);
            $pgPublicacao = $dados["pgPublicacao"];
            $tipo = $dados["tipo"];
            $periodo = $dados["periodo"];
            $processo = $reducao->get_numProcesso($idServidorPesquisado);

            # Pega os dados Digitados
            $botaoEscolhido = get_post_action("salvar", "imprimir");
            $numCiInicioDigitados = vazioPraNulo(post("numCiInicio"));
            $dtCiInicioDigitado = vazioPraNulo(post("dtCiInicio"));
            $tipo = vazioPraNulo(post("tipo"));

            $servidorGrh = post("servidorGrh");
            $chefeDigitado = post("chefia");
            $cargoDigitado = post("cargo");

            # Prepara para enviar por get
            $array = array($chefeDigitado, $cargoDigitado, $servidorGrh);
            $array = serialize($array);

            # Verifica se houve alterações
            $alteracoes = null;
            $atividades = null;

            # Verifica as alterações para o log
            if ($numCiInicio <> $numCiInicioDigitados) {
                $alteracoes .= '[numCiInicio] ' . $numCiInicio . '->' . $numCiInicioDigitados . '; ';
            }
            if ($dtCiInicio <> $dtCiInicioDigitado) {
                $alteracoes .= '[dtCiInicio] ' . date_to_php($dtCiInicio) . '->' . date_to_php($dtCiInicioDigitado) . '; ';
            }

            # Erro
            $msgErro = null;
            $erro = 0;

            # Verifica o número da Ci
            if (vazio($numCiInicioDigitados)) {
                $msgErro .= 'Não tem número de Ci de Início cadastrada!\n';
                $erro = 1;
            }

            # Verifica a data da CI
            if (vazio($dtCiInicioDigitado)) {
                $msgErro .= 'Não tem data da Ci de Início cadastrada!\n';
                $erro = 1;
            }

            # Verifica a data da Publicação
            if (vazio($dtPublicacao)) {
                $msgErro .= 'Não tem data da Publicação cadastrada!\n';
                $erro = 1;
            }

            # Verifica a data de Início
            if (vazio($dtInicio)) {
                $msgErro .= 'Não tem data de início do benefício cadastrada!\n';
                $erro = 1;
            }

            # Verifica o período
            if (vazio($periodo)) {
                $msgErro .= 'O período não foi cadastrado!\n';
                $erro = 1;
            }

            # Salva as alterações
            $pessoal->set_tabela("tbreducao");
            $pessoal->set_idCampo("idReducao");
            $campoNome = array('numCiInicio', 'dtCiInicio', 'tipo');
            $campoValor = array($numCiInicioDigitados, $dtCiInicioDigitado, $tipo);
            $pessoal->gravar($campoNome, $campoValor, $id);
            $data = date("Y-m-d H:i:s");

            # Grava o log das alterações caso tenha
            if (!is_null($alteracoes)) {
                $atividades .= 'Alterou: ' . $alteracoes;
                $tipoLog = 2;
                $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", $id, $tipoLog, $idServidorPesquisado);
            }


            # Exibe o relatório ou salva de acordo com o botão pressionado
            if ($botaoEscolhido == "imprimir") {
                if ($erro == 0) {
                    # Exibe o relatório
                    if ($tipo == 1) {
                        loadPage("../grhRelatorios/reducaoCiInicio.php?id=$id&array=$array", "_blank");
                    } else {
                        loadPage("../grhRelatorios/reducaoCiRenovacao.php?id=$id&array=$array", "_blank");
                    }
                    loadPage("?");
                } else {
                    alert($msgErro);
                    back(1);
                }
            } else {
                loadPage("?");
            }
            break;

################################################################################################################
        # Ci 45 Dias
        case "ci45Form" :

            # Voltar
            botaoVoltar("?");

            # Dados do Servidor
            get_DadosServidor($idServidorPesquisado);

            # Pega os Dados do Banco
            $dados = $reducao->get_dadosCi45($id);

            # Da redução
            $numCi45 = $dados[0];
            $dtCi45 = $dados[1];
            $dtPublicacao = $dados[2];
            $pgPublicacao = $dados[3];

            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(10);
            br(3);

            # Título
            tituloTable("Controle de Redução da Carga Horária<br/>Ci de 45 Dias (ou menos)");
            $painel = new Callout();
            $painel->abre();

            # Monta o formulário para confirmação dos dados necessários a emissão da CI
            $form = new Form('?fase=ci45FormValida&id=' . $id);

            # numCiInicio
            $controle = new Input('numCi45', 'texto', 'Ci n°:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(2);
            #$controle->set_required(true);
            $controle->set_autofocus(true);
            $controle->set_valor($numCi45);
            $controle->set_title('Número da Ci informando que em 45 dias o benefício irá terminar.');
            $form->add_item($controle);

            # dtCiInicio
            $controle = new Input('dtCi45', 'data', 'Data da Ci:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_valor($dtCi45);
            #$controle->set_required(true);
            $controle->set_title('A data da CI de 45 dias.');
            $form->add_item($controle);

            # servidor da grh
            $controle = new Input('servidorGrh', 'combo', 'Servidor da GRH que assina a CI:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(5);

            # Cria combo de servidores da GRH
            $select = "SELECT idServidor, tbpessoa.nome 
                 FROM tbpessoa JOIN tbservidor USING (idPessoa) 
                               JOIN tbhistlot USING (idServidor)
                               JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND tbhistlot.lotacao = 66
                 ORDER BY tbpessoa.nome";

            $servGrh = $pessoal->select($select);

            $controle->set_array($servGrh);
            $controle->set_valor($pessoal->get_gerente(66)); // Pega o idServidor do gerente da GRH (66)
            $controle->set_title('Se é Inicial ou Renovação.');
            $form->add_item($controle);

            # submit
            $controle = new Input('salvar', 'submit');
            $controle->set_valor('Salvar');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            # submit
            $controle = new Input('imprimir', 'submit');
            $controle->set_valor('Salvar & Imprimir');
            $controle->set_linha(5);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();
            $painel->fecha();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "ci45FormValida" :

            # Pega os Dados do Banco
            $dados = $reducao->get_dadosCi45($id);
            $numCi45 = $dados[0];
            $dtCi45 = $dados[1];
            $dtPublicacao = $dados[2];
            $pgPublicacao = $dados[3];

            # Pega os dados Digitados
            $botaoEscolhido = get_post_action("salvar", "imprimir");
            $numCi45Digitados = vazioPraNulo(post("numCi45"));
            $dtCi45Digitado = vazioPraNulo(post("dtCi45"));
            $servidorGrh = post("servidorGrh");

            # Verifica se houve alterações
            $alteracoes = null;
            $atividades = null;

            # Verifica as alterações para o log
            if ($numCi45 <> $numCi45Digitados) {
                $alteracoes .= '[numCi45] ' . $numCi45 . '->' . $numCi45Digitados . '; ';
            }
            if ($dtCi45 <> $dtCi45Digitado) {
                if (vazio($dtCi45Digitado)) {
                    $alteracoes .= '[dtCi45] ' . date_to_php($dtCi45) . '->  ; ';
                } else {
                    $alteracoes .= '[dtCi45] ' . date_to_php($dtCi45) . '->' . date_to_php($dtCi45Digitado) . '; ';
                }
            }

            # Erro
            $msgErro = null;
            $erro = 0;

            # Verifica se apertou o imprimir
            if ($botaoEscolhido == "imprimir") {

                # Verifica o número da Ci
                if (vazio($numCi45Digitados)) {
                    $msgErro .= 'Não tem número de Ci de 45 dias cadastrada!\n';
                    $erro = 1;
                }

                # Verifica a data da CI
                if (vazio($dtCi45Digitado)) {
                    $msgErro .= 'Não tem data da Ci de 45 dias cadastrada!\n';
                    $erro = 1;
                }

                # Verifica a data da Publicação
                if (vazio($dtPublicacao)) {
                    $msgErro .= 'Não tem data da Publicação cadastrada!\n';
                    $erro = 1;
                }
            }

            # Salva as alterações
            $pessoal->set_tabela("tbreducao");
            $pessoal->set_idCampo("idReducao");
            $campoNome = array('numCi45', 'dtCi45');
            $campoValor = array($numCi45Digitados, $dtCi45Digitado);
            $pessoal->gravar($campoNome, $campoValor, $id);
            $data = date("Y-m-d H:i:s");

            # Grava o log das alterações caso tenha
            if (!is_null($alteracoes)) {
                $atividades .= 'Alterou: ' . $alteracoes;
                $tipoLog = 2;
                $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", $id, $tipoLog, $idServidorPesquisado);
            }

            # Exibe o relatório ou salva de acordo com o botão pressionado
            if ($botaoEscolhido == "imprimir") {
                if ($erro == 0) {
                    loadPage("../grhRelatorios/reducaoCi45.php?id={$id}&servidorGrh={$servidorGrh}", "_blank");
                    loadPage("?");
                } else {
                    alert($msgErro);
                    back(1);
                }
            } else {
                loadPage("?");
            }
            break;

################################################################################################################
        # Ci Término
        case "ciTerminoForm" :

            # Voltar
            botaoVoltar("?");

            # Dados do Servidor
            get_DadosServidor($idServidorPesquisado);

            # Pega os Dados
            $dados = $reducao->get_dados($id);

            $numCitermino = $dados["numCiTermino"];
            $dtCitermino = $dados["dtCiTermino"];
            $dtTermino = date_to_php($dados["dtTermino"]);
            $dtPublicacao = date_to_php($dados["dtPublicacao"]);
            $pgPublicacao = $dados["pgPublicacao"];
            $periodo = $dados["periodo"];
            $processo = $reducao->get_numProcesso($idServidorPesquisado);

            # Chefia imediata desse servidor
            $idChefiaImediataDestino = $pessoal->get_chefiaImediata($idServidorPesquisado);              // idServidor do chefe
            $nomeGerenteDestino = $pessoal->get_nome($idChefiaImediataDestino);                          // Nome do chefe
            $gerenciaImediataDescricao = $pessoal->get_chefiaImediataDescricao($idServidorPesquisado);   // Descrição do cargo
            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(10);
            br(3);

            # Título
            titulo("Ci de Término");
            $painel = new Callout();
            $painel->abre();

            # Monta o formulário para confirmação dos dados necessários a emissão da CI
            $form = new Form('?fase=ciTerminoFormValida&id=' . $id);

            # numCi
            $controle = new Input('numCiTermino', 'texto', 'Ci n°:', 1);
            $controle->set_size(20);
            $controle->set_linha(1);
            $controle->set_col(2);
            #$controle->set_required(true);
            $controle->set_autofocus(true);
            $controle->set_valor($numCitermino);
            $controle->set_title('Número da Ci informando a chefia imediata do servidor da data de Término do benefício.');
            $form->add_item($controle);

            # dtCi
            $controle = new Input('dtCiTermino', 'data', 'Data da Ci:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_valor($dtCitermino);
            #$controle->set_required(true);
            $controle->set_title('A data da CI de término.');
            $form->add_item($controle);

            # servidor da grh
            $controle = new Input('servidorGrh', 'combo', 'Servidor da GRH que assina a CI:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(5);

            # Cria combo de servidores da GRH
            $select = "SELECT idServidor, tbpessoa.nome 
                 FROM tbpessoa JOIN tbservidor USING (idPessoa) 
                               JOIN tbhistlot USING (idServidor)
                               JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                WHERE situacao = 1
                  AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND tbhistlot.lotacao = 66
                 ORDER BY tbpessoa.nome";

            $servGrh = $pessoal->select($select);

            $controle->set_array($servGrh);
            $controle->set_valor($pessoal->get_gerente(66)); // Pega o idServidor do gerente da GRH (66)
            $controle->set_title('Se é Inicial ou Renovação.');
            $form->add_item($controle);

            # Chefia
            $controle = new Input('chefia', 'texto', 'Chefia:', 1);
            $controle->set_size(200);
            $controle->set_linha(2);
            $controle->set_col(12);
            $controle->set_valor($nomeGerenteDestino);
            #$controle->set_required(true);
            $controle->set_title('O nome da chefia imediata.');
            $form->add_item($controle);

            # Cargo
            $controle = new Input('cargo', 'texto', 'Cargo:', 1);
            $controle->set_size(200);
            $controle->set_linha(3);
            $controle->set_col(12);
            $controle->set_valor($gerenciaImediataDescricao);
            #$controle->set_required(true);
            $controle->set_title('O Cargo em comissão da chefia.');
            $form->add_item($controle);

            # submit
            $controle = new Input('salvar', 'submit');
            $controle->set_valor('Salvar');
            $controle->set_linha(5);
            $controle->set_col(2);
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

        case "ciTerminoFormValida" :

            # Pega os Dados
            $dados = $reducao->get_dados($id);

            $numCiTermino = $dados["numCiTermino"];
            $dtCiTermino = $dados["dtCiTermino"];
            $dtPublicacao = $dados["dtPublicacao"];
            $pgPublicacao = $dados["pgPublicacao"];
            $periodo = $dados["periodo"];
            $dtInicio = $dados["dtInicio"];
            $processo = $reducao->get_numProcesso($idServidorPesquisado);

            # Pega os dados Digitados
            $botaoEscolhido = get_post_action("salvar", "imprimir");
            $numCiTerminoDigitados = vazioPraNulo(post("numCiTermino"));
            $dtCiTerminoDigitado = vazioPraNulo(post("dtCiTermino"));

            $servidorGrh = post("servidorGrh");
            $chefeDigitado = post("chefia");
            $cargoDigitado = post("cargo");

            # Prepara para enviar por get
            $array = array($chefeDigitado, $cargoDigitado, $servidorGrh);
            $array = serialize($array);

            # Verifica se houve alterações
            $alteracoes = null;
            $atividades = null;

            # Verifica as alterações para o log
            if ($numCiTermino <> $numCiTerminoDigitados) {
                $alteracoes .= '[numCiTermino] ' . $numCiTermino . '->' . $numCiTerminoDigitados . '; ';
            }
            if ($dtCiTermino <> $dtCiTerminoDigitado) {
                $alteracoes .= '[dtCiTermino] ' . date_to_php($dtCiTermino) . '->' . date_to_php($dtCiTerminoDigitado) . '; ';
            }

            # Erro
            $msgErro = null;
            $erro = 0;

            # Verifica o número da Ci
            if (vazio($numCiTerminoDigitados)) {
                $msgErro .= 'Não tem número de Ci de Término cadastrada!\n';
                $erro = 1;
            }

            # Verifica a data da CI
            if (vazio($dtCiTerminoDigitado)) {
                $msgErro .= 'Não tem data da Ci de Término cadastrada!\n';
                $erro = 1;
            }

            # Verifica a data da Publicação
            if (vazio($dtPublicacao)) {
                $msgErro .= 'Não tem data da Publicação cadastrada!\n';
                $erro = 1;
            }

            # Verifica a data de Início
            if (vazio($dtInicio)) {
                $msgErro .= 'Não tem data de início do benefício cadastrada!\n';
                $erro = 1;
            }

            # Verifica o período
            if (vazio($periodo)) {
                $msgErro .= 'O período não foi cadastrado!\n';
                $erro = 1;
            }

            # Salva as alterações
            $pessoal->set_tabela("tbreducao");
            $pessoal->set_idCampo("idReducao");
            $campoNome = array('numCiTermino', 'dtCiTermino');
            $campoValor = array($numCiTerminoDigitados, $dtCiTerminoDigitado);
            $pessoal->gravar($campoNome, $campoValor, $id);
            $data = date("Y-m-d H:i:s");

            # Grava o log das alterações caso tenha
            if (!is_null($alteracoes)) {
                $atividades .= 'Alterou: ' . $alteracoes;
                $tipoLog = 2;
                $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", $id, $tipoLog, $idServidorPesquisado);
            }

            # Exibe o relatório ou salva de acordo com o botão pressionado
            if ($botaoEscolhido == "imprimir") {
                if ($erro == 0) {
                    loadPage("../grhRelatorios/reducaoCiTermino.php?id=$id&array=$array", "_blank");
                    loadPage("?");
                } else {
                    alert($msgErro);
                    back(1);
                }
            } else {
                loadPage("?");
            }
            break;

################################################################################################################
        # Ato Reitor        
        case "atoReitorForm" :

            # Voltar
            botaoVoltar("?");

            # Dados do Servidor
            get_DadosServidor($idServidorPesquisado);

            # Pega os Dados
            $dados = $reducao->get_dadosReducao($id);

            # Da Redução
            $dtAtoReitor = $dados["dtAtoReitor"];
            $dtDespacho = $dados["dtDespacho"];
            $necessidade = null;

            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(10);
            br(3);

            # Título
            titulo("Ato do Reitor");
            $painel = new Callout();
            $painel->abre();

            # Monta o formulário
            $form = new Form('?fase=atoReitorFormValida&id=' . $id);

            # dtCiInicio
            $controle = new Input('dtAtoReitor', 'data', 'Data do Ato do Reitor:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_valor($dtAtoReitor);
            $controle->set_autofocus(true);
            $controle->set_title('A data do Ato do Reitor.');
            $form->add_item($controle);

            # dtDespacho
            $controle = new Input('dtDespacho', 'data', 'Data do Despacho da Perícia:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_valor($dtDespacho);
            #$controle->set_required(true);
            $controle->set_title('A data do Despacho da Perícia.');
            $form->add_item($controle);

            # necessidade
            $controle = new Input('necessidade', 'combo', 'Necessidade:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_array(array(null, "Permanente", "Eventual", "Duradoura"));
            $controle->set_valor($necessidade);
            #$controle->set_required(true);
            $controle->set_title('Como a necessidade foi caracterizada.');
            $form->add_item($controle);

            # submit
            $controle = new Input('salvar', 'submit');
            $controle->set_valor('Salvar');
            $controle->set_linha(5);
            $controle->set_col(2);
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

        case "atoReitorFormValida" :

            # Pega os Dados
            $dados = $reducao->get_dadosReducao($id);
            $dtAtoReitor = $dados["dtAtoReitor"];
            $dtDespacho = $dados["dtDespacho"];
            $periodo = $dados["periodo"];
            $processo = $reducao->get_numProcesso($idServidorPesquisado);

            # Pega os dados Digitados
            $botaoEscolhido = get_post_action("salvar", "imprimir");
            $dtAtoReitorDigitados = vazioPraNulo(post("dtAtoReitor"));
            $dtDespachoDigitado = vazioPraNulo(post("dtDespacho"));
            $necessidade = vazioPraNulo(post("necessidade"));

            # Verifica se houve alterações
            $alteracoes = null;
            $atividades = null;

            # Verifica as alterações para o log
            if ($dtAtoReitor <> $dtAtoReitorDigitados) {
                $alteracoes .= '[dtAtoReitor] ' . date_to_php($dtAtoReitor) . '->' . date_to_php($dtAtoReitorDigitados) . '; ';
            }

            # Verifica as alterações para o log
            if ($dtDespacho <> $dtDespachoDigitado) {
                $alteracoes .= '[dtDespacho] ' . date_to_php($dtDespacho) . '->' . date_to_php($dtDespachoDigitado) . '; ';
            }

            # Erro
            $msgErro = null;
            $erro = 0;

            if ($botaoEscolhido == "imprimir") {
                # Verifica a necessidade
                if (vazio($necessidade)) {
                    $msgErro .= 'A necessidade deve ser informada!\n';
                    $erro = 1;
                }
            }

            # Verifica o número da Ci
            if (vazio($dtAtoReitorDigitados)) {
                $msgErro .= 'Não tem data do Ato do Reitor cadastrada!\n';
                $erro = 1;
            }

            # Verifica a data da Publicação
            if (vazio($dtDespachoDigitado)) {
                $msgErro .= 'A Data do Despacho da Perícia deve ser Preenchida!\n';
                $erro = 1;
            }

            # Salva as alterações
            $pessoal->set_tabela("tbreducao");
            $pessoal->set_idCampo("idReducao");
            $campoNome = array('dtAtoReitor', 'dtDespacho');
            $campoValor = array($dtAtoReitorDigitados, $dtDespachoDigitado);
            $pessoal->gravar($campoNome, $campoValor, $id);
            $data = date("Y-m-d H:i:s");

            # Grava o log das alterações caso tenha
            if (!is_null($alteracoes)) {
                $atividades .= 'Alterou: ' . $alteracoes;
                $tipoLog = 2;
                $intra->registraLog($idUsuario, $data, $atividades, "tbreducao", $id, $tipoLog, $idServidorPesquisado);
            }

            # Exibe o relatório ou salva de acordo com o botão pressionado
            if ($botaoEscolhido == "imprimir") {
                if ($erro == 0) {
                    loadPage("../grhRelatorios/reducaoAtoReitor.php?necessidade={$necessidade}&id={$id}", "_blank");
                    loadPage("?");
                } else {
                    alert($msgErro);
                    back(1);
                }
            } else {
                loadPage("?");
            }
            break;

        ################################################################################################################
        # Despacho para Perícia
        case "despachoPerícia" :

            # Voltar
            #botaoVoltar("?");
            # Dados do Servidor
            #get_DadosServidor($idServidorPesquisado);
            # Pega os dados da redução
            $dados = $reducao->get_dados($id);
            $tipo = $dados["tipo"];

            # Formulário somente para tipo 2
            if ($tipo == 2) {

                # Limita a tela
                $grid = new Grid("center");
                $grid->abreColuna(10);
                br();

                callout("ATENÇÃO:<br/>Quando a solicitação é de renovação, faz-se necessário informar a página do processo, onde se encontra a cópia da publicação do benefício anterior.");

                # Título
                titulo("Redução de Carga Horária - Despacho Para Perícia Médica");
                $painel = new Callout();
                $painel->abre();

                # Monta o formulário
                $form = new Form('?fase=despachoPericiaFormValida&id=' . $id);

                # folha da publicação no processo 
                $controle = new Input('folha', 'texto', 'Página:', 1);
                $controle->set_size(10);
                $controle->set_linha(1);
                $controle->set_col(3);
                $controle->set_autofocus(true);
                $controle->set_title('A página do processo da cópia da publicação.');
                $form->add_item($controle);

                # submit
                $controle = new Input('imprimir', 'submit');
                $controle->set_valor('Imprimir');
                $controle->set_linha(5);
                $controle->set_col(2);
                $form->add_item($controle);

                $form->show();

                $grid->fechaColuna();
                $grid->fechaGrid();
            } else {
                loadPage("../grhRelatorios/reducao.DespachoPericia.php?id=$id");
            }
            break;

        case "despachoPericiaFormValida" :

            # Pega os dados Digitados
            $folha = vazioPraNulo(post("folha"));

            # Erro
            $msgErro = null;
            $erro = 0;

            # Verifica o número da folha
            if (vazio($folha)) {
                $msgErro .= 'Deve digitar o número da folha!\n';
                $erro = 1;
            }

            if ($erro == 0) {
                loadPage("../grhRelatorios/reducao.DespachoPericia.php?folha=$folha&id=$id");
            } else {
                alert($msgErro);
                back(1);
            }
            break;

################################################################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}