<?php

/**
 * Controle do Redução de Carega Horária
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

    $jscript = '                
                // Pega os valores do resultado
                var resultado = $("#resultado").val();

                // Verifica o valor do resultado quando o form é carregado
                if(resultado == 1){
                    $("#dtPublicacao").show();
                    $("#dtInicio").show();
                    $("#periodo").show();
                    $("#numCiInicio").show();
                    $("#numCiTermino").show();                    
                    $("#div3").show();
                }else{
                    $("#dtPublicacao").hide();
                    $("#dtInicio").hide();
                    $("#periodo").hide();
                    $("#numCiInicio").hide();
                    $("#numCiTermino").hide();                    
                    $("#div3").hide();
                }
                
                // Verifica o valor do resultado quando se muda o valor do campo
                $("#resultado").change(function(){
                    var resultado = $("#resultado").val();
                    
                    if(resultado == 1){
                        $("#dtPublicacao").show();
                        $("#dtInicio").show();
                        $("#periodo").show();
                        $("#numCiInicio").show();
                        $("#numCiTermino").show();                    
                        $("#div3").show();
                    }else{
                        $("#dtPublicacao").hide();
                        $("#dtInicio").hide();
                        $("#periodo").hide();
                        $("#numCiInicio").hide();
                        $("#numCiTermino").hide();                    
                        $("#div3").hide();
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

        # Procedimentos
        $linkBotao3 = new Link("Procedimentos", "?fase=procedimentos");
        $linkBotao3->set_class('button');
        $linkBotao3->set_title('Procedimentos da readaptação');
        $linkBotao3->set_target("_blank");
        $menu->add_link($linkBotao3, "right");

        # Relatório
        $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
        $botaoRel = new Button();
        $botaoRel->set_imagem($imagem);
        $botaoRel->set_title("Imprimir Relatório de Histórico de Processo de redução da carga horária");
        $botaoRel->set_url("../grhRelatorios/servidorReducao.php");
        $botaoRel->set_target("_blank");
        $menu->add_link($botaoRel, "right");

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
    $objeto->set_selectEdita('SELECT tipo,
                                     status,
                                     resultado,                                
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
    $objeto->set_label(["Tipo", "Status", "Resultado", "Publicação", "Período", "Documentos"]);
    $objeto->set_align(["center", "center", "center", "center", "left", "left"]);
    $objeto->set_classe([null, "ReducaoCargaHoraria", "ReducaoCargaHoraria", "ReducaoCargaHoraria", "ReducaoCargaHoraria", "ReducaoCargaHoraria"]);
    $objeto->set_metodo([null, "exibeStatus", "exibeResultado", "exibePublicacao", "exibePeriodo", "exibeBotaoDocumentos"]);

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
    $objeto->set_campos(array(
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
        array('nome' => 'resultado',
            'label' => 'Resultado:',
            'tipo' => 'combo',
            'array' => [
                [null, ""],
                [1, "Deferido"],
                [2, "Indeferido"],
                [3, "Interrompido"]
            ],
            'size' => 20,
            'title' => 'Se o processo foi deferido ou indeferido',
            'col' => 3,
            'linha' => 1),
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

    ###################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            # Divide a página em 3 colunas
            $grid = new Grid();

            ###################################################################
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

            ###################################################################
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

            ###################################################################
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

        ###################################################################

        case "editar" :
        case "excluir" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id, "servidorReducaoExtra.php");
            break;

        ###################################################################
        # Ato Reitor        
        case "atoReitor" :

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
            $grid->abreColuna(12);

            # Título
            titulotable("Ato do Reitor");
            br();

            # Monta o formulário
            $form = new Form('?fase=atoReitorValida&id=' . $id);

            # dtCiInicio
            $controle = new Input('dtAtoReitor', 'data', 'Data do Ato do Reitor:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_valor($dtAtoReitor);
            $controle->set_autofocus(true);
            $controle->set_title('A data do Ato do Reitor.');
            $form->add_item($controle);

            # dtDespacho
            $controle = new Input('dtDespacho', 'data', 'Data do Despacho da Perícia:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(4);
            $controle->set_valor($dtDespacho);
            #$controle->set_required(true);
            $controle->set_title('A data do Despacho da Perícia.');
            $form->add_item($controle);

            # necessidade
            $controle = new Input('necessidade', 'combo', 'Necessidade:', 1);
            $controle->set_size(10);
            $controle->set_linha(1);
            $controle->set_col(4);
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

        case "atoReitorValida" :

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
                    loadPage("../grhRelatorios/reducao.ato.php?necessidade={$necessidade}&id={$id}", "_blank");
                    loadPage("?");
                } else {
                    alert($msgErro);
                    back(1);
                }
            } else {
                loadPage("?");
                echo "<script>window.close();</script>";
            }
            break;

        ###################################################################

        case "procedimentos" :
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            $procedimento = new Procedimento();
            $procedimento->exibeProcedimentoSubCategoria("Redução de Carga Horária");

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ###################################################################
        # Despacho de Conclusão temporária
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
            $form = new Form("../grhRelatorios/despacho.RCH.conclusaoTemporaria.php");

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
        # Despacho à Reitoria
        case "despachoReitoria" :

            # Limita a tela
            $grid = new Grid();
            $grid->abreColuna(12);
            br();

            # Título
            tituloTable("Despacho à Reitoria");
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
            $form = new Form("../grhRelatorios/despacho.RCH.reitoria.php");

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
        # Despacho: Início da Concessão
        case "despachoInicio" :

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
            tituloTable("Despacho: Início da Concessão");
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
            $form = new Form("../grhRelatorios/despacho.RCH.inicioConcessao.php?id={$id}");

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
        # Despacho: Aviso de Término
        case "despachoTermino" :

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
            tituloTable("Despacho: Aviso de Término");
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
            $form = new Form("../grhRelatorios/despacho.RCH.avisoTermino.php?id={$id}");

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
            $controle->set_linha(2);
            $controle->set_col(12);
            $controle->set_valor($gerenciaImediataDescricao);
            $controle->set_required(true);
            $controle->set_title('O Cargo em comissão da chefia.');
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