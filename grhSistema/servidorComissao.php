<?php

/**
 * Dados de Cargos em Comissão
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados    
    $pessoal = new Pessoal();
    $cargoComissao = new CargoComissao();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Histórico dos cargos em comissão";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o origem quando vier do cadastro de Cargo em comissão
    $origem = get_session('origem');

    # Ordem da tabela
    $orderCampo = get('orderCampo');
    $orderTipo = get('orderTipo');

    # Rotina em jscript
    $script = '
            <script type="text/javascript" language="javascript">

                    $(document).ready(function(){
                        $("#idTipoComissao").change(function(){
                            $("#idDescricaoComissao").load("servidorComissaoExtraCombo.php?tipo="+$("#idTipoComissao").val());
                            $("#idAnterior").load("servidorComissaoExtraCombo2.php?tipo="+$("#idTipoComissao").val()+"&descricao="+$("#idDescricaoComissao").val());
                        })
                    })
                    
                    $(document).ready(function(){
                    
                        $("#idDescricaoComissao").change(function(){
                            $("#idAnterior").load("servidorComissaoExtraCombo2.php?tipo="+$("#idTipoComissao").val()+"&descricao="+$("#idDescricaoComissao").val());
                        })
                    })


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

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Histórico dos Cargos em Comissão');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # ordenação
    if (is_null($orderCampo)) {
        $orderCampo = "3";
    }

    if (is_null($orderTipo)) {
        $orderTipo = 'desc';
    }

    ## Rotina abaixo retirado para "tirar o gesso" 
    # Retira o botão de inclusão quando o servidor já tem cargo em comissão em aberto.
    #if(!is_null($pessoal->get_cargoComissao($idServidorPesquisado))){
    #    # Retira o botão de incluir
    #    $objeto->set_botaoIncluir(false);
    #    
    #    # Informa o porquê
    #    $mensagem = "O botão de Incluir sumiu! Porque? Esse servidor já tem um cargo em comissão.<br/>"
    #               ."Somente será permitido a inserção de um novo cargo quanfo for informado a data de término do cargo atual.";
    #    $objeto->set_rotinaExtraListar("callout");
    #    $objeto->set_rotinaExtraListarParametro($mensagem);
    #}
    # select da lista
    $objeto->set_selectLista('SELECT idComissao,
                                     idComissao,
                                     idComissao,
                                     idComissao,
                                     idComissao,
                                     obs
                                FROM tbcomissao
                               WHERE idServidor = ' . $idServidorPesquisado . '
                            ORDER BY dtNom desc');

    # select do edita
    $objeto->set_selectEdita('SELECT idTipoComissao,
                                     idDescricaoComissao,
                                     tipo,
                                     idAnterior,
                                     outraOrigem,
                                     dtNom,
                                     dtAtoNom,
                                     numProcNom,
                                     dtPublicNom,
                                     dtExo,
                                     dtAtoExo,
                                     numProcExo,
                                     dtPublicExo,
                                     obs,
                                     idServidor
                                FROM tbcomissao
                               WHERE idComissao = ' . $id);

    # ordem da lista
    #$objeto->set_orderCampo($orderCampo);
    #$objeto->set_orderTipo($orderTipo);
    #$objeto->set_orderChamador('?fase=listar');
    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Cargo", "Nomeação", "Exoneração", "Documentos", "Ocupante Anterior", "Obs"));
    $objeto->set_width(array(18, 16, 16, 15, 16, 14));
    $objeto->set_align(array("left", "left", "left", "left", "left", "left"));
    #$objeto->set_funcao(array(null, "date_to_php", "date_to_php"));

    $objeto->set_classe(array("CargoComissao", "CargoComissao", "CargoComissao", "CargoComissao", "CargoComissao"));
    $objeto->set_metodo(array("exibeCargoCompleto", "exibeDadosNomeacao", "exibeDadosExoneracao", "exibeBotaoDocumentos", "exibeOcupanteAnterior"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbcomissao');

    # Nome do campo id
    $objeto->set_idCampo('idComissao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Pega os dados da combo tipo de Comissão
    $tipoComissao = $pessoal->select('SELECT idTipoComissao,
                                         CONCAT(tbtipocomissao.simbolo," - (",tbtipocomissao.descricao,")") as comissao
                                    FROM tbtipocomissao
                                ORDER BY ativo desc, simbolo');

    array_unshift($tipoComissao, array(null, null));

    # Pega os dados da descrição
    if (is_null($id)) {
        $descricao = $pessoal->select('SELECT idDescricaoComissao,
                                              tbdescricaocomissao.descricao
                                         FROM tbdescricaocomissao JOIN tbtipocomissao USING (idTipoComissao)
                                     ORDER BY tbtipocomissao.simbolo, tbtipocomissao.descricao,  tbdescricaocomissao.descricao');
    } else {
        $comissao = $cargoComissao->get_dados($id);

        $descricao = $pessoal->select('SELECT idDescricaoComissao,
                                              tbdescricaocomissao.descricao
                                         FROM tbdescricaocomissao JOIN tbtipocomissao USING (idTipoComissao)
                                        WHERE tbdescricaocomissao.idTipoComissao = ' . $comissao["idTipoComissao"] . ' 
                                     ORDER BY tbtipocomissao.simbolo, tbtipocomissao.descricao,  tbdescricaocomissao.descricao');
    }

    array_unshift($descricao, array(null, null));

    # Pega os dados do ocupante anterior
    if (is_null($id)) {
        $ocupanteAnterior = null;
    } else {
        $comissao = $cargoComissao->get_dados($id);

        # Primeiro select pega os cargos com a mesma descrição do $id
        $selectOcupante1 = 'SELECT idComissao,
                                  CONCAT(DATE_FORMAT(dtNom,"%d/%m/%Y")," - ",DATE_FORMAT(dtExo,"%d/%m/%Y")," | ",tbpessoa.nome," | ",tbperfil.nome) as ff,
                                  tbdescricaocomissao.descricao
                             FROM tbcomissao as tb1 JOIN tbservidor USING (idServidor) 
                                                    JOIN tbpessoa USING (idPessoa)
                                                    JOIN tbperfil USING (idPerfil)
                                                    JOIN tbdescricaocomissao USING (idDescricaoComissao)';
        # Seleciona somente os de mesmo cargo
        $selectOcupante1 .= ' WHERE tbdescricaocomissao.idTipoComissao = ' . $comissao["idTipoComissao"];

        # Seleciona somente os exinerados
        $selectOcupante1 .= ' AND tbdescricaocomissao.idDescricaoComissao = ' . $comissao["idDescricaoComissao"];

        # Seleciona somente os exinerados
        $selectOcupante1 .= ' AND dtExo IS NOT null';

        # Não pode o anterior ser o próprio mandato atual
        $selectOcupante1 .= ' AND idComissao <> ' . $id;

        # Impede que o mandato já escolhido apareça
        $selectOcupante1 .= ' AND tb1.idComissao NOT IN (SELECT idAnterior FROM tbcomissao as tb2 WHERE idAnterior IS NOT null AND tb2.idComissao <> ' . $id . ')';

        # Ordena pela descrição e data de nomeação para facilitar o agrupamento
        $selectOcupante1 .= ' ORDER BY tbdescricaocomissao.descricao, dtNom desc';
        $ocupanteAnterior1 = $pessoal->select($selectOcupante1);

        # Segundo select pega os cargos que sao diferentes do $id, pois existe 
        # remota possibilidade do cargp anterior ser de outra descrição
        $selectOcupante2 = 'SELECT idComissao,
                                  CONCAT(DATE_FORMAT(dtNom,"%d/%m/%Y")," - ",DATE_FORMAT(dtExo,"%d/%m/%Y")," | ",tbpessoa.nome," | ",tbperfil.nome) as ff,
                                  tbdescricaocomissao.descricao
                             FROM tbcomissao as tb1 JOIN tbservidor USING (idServidor) 
                                                    JOIN tbpessoa USING (idPessoa)
                                                    JOIN tbperfil USING (idPerfil)
                                                    JOIN tbdescricaocomissao USING (idDescricaoComissao)';
        # Seleciona somente os de mesmo cargo
        $selectOcupante2 .= ' WHERE tbdescricaocomissao.idTipoComissao = ' . $comissao["idTipoComissao"];

        # Seleciona somente os exinerados
        $selectOcupante2 .= ' AND tbdescricaocomissao.idDescricaoComissao <> ' . $comissao["idDescricaoComissao"];

        # Seleciona somente os exinerados
        $selectOcupante2 .= ' AND dtExo IS NOT null';

        # Não pode o anterior ser o próprio mandato atual
        $selectOcupante2 .= ' AND idComissao <> ' . $id;

        # Impede que o mandato já escolhido apareça
        $selectOcupante2 .= ' AND tb1.idComissao NOT IN (SELECT idAnterior FROM tbcomissao as tb2 WHERE idAnterior IS NOT null AND tb2.idComissao <> ' . $id . ')';

        # Ordena pela descrição e data de nomeação para facilitar o agrupamento
        $selectOcupante2 .= ' ORDER BY tbdescricaocomissao.descricao, dtNom desc';
        $ocupanteAnterior2 = $pessoal->select($selectOcupante2);

        # Junta os arrays
        $ocupanteAnterior = array_merge($ocupanteAnterior1, $ocupanteAnterior2);

        array_unshift($ocupanteAnterior, [null, null]);
    }

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'idTipoComissao',
            'label' => 'Tipo da Cargo em Comissão:',
            'tipo' => 'combo',
            'required' => true,
            'autofocus' => true,
            'array' => $tipoComissao,
            'size' => 20,
            'col' => 3,
            'title' => 'Tipo dp Cargo em Comissão',
            'linha' => 1),
        array('linha' => 1,
            'col' => 7,
            'nome' => 'idDescricaoComissao',
            'label' => 'Descrição do Cargo:',
            'tipo' => 'combo',
            'array' => $descricao,
            'size' => 100),
        array('nome' => 'tipo',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'array' => array(array(0, "Padrão"), array(1, "Pro Tempore"), array(2, "Designado")),
            'required' => true,
            'size' => 20,
            'col' => 2,
            'title' => 'Informa se é pro tempore, ou seja, temporário para terminar mandato. (mandato tampão)',
            'linha' => 1),
        array('linha' => 2,
            'col' => 6,
            'nome' => 'idAnterior',
            'label' => 'Ocupante Anterior (da Uenf):',
            'tipo' => 'combo',
            'optgroup' => true,
            'array' => $ocupanteAnterior,
            'size' => 100),
        array('nome' => 'outraOrigem',
            'label' => 'Outra Origem do Cargo (de fora da Uenf):',
            'tipo' => 'texto',
            'size' => 250,
            'col' => 6,
            'title' => 'Processo de Exoneração',
            'linha' => 2),
        array('nome' => 'dtNom',
            'label' => 'Data da Nomeação:',
            'fieldset' => 'Nomeação',
            'tipo' => 'data',
            'size' => 20,
            'required' => true,
            'title' => 'Data da Nomeação.',
            'col' => 3,
            'linha' => 2),
        array('nome' => 'dtAtoNom',
            'label' => 'Data do Ato do Reitor:',
            'title' => 'Data do Ato do Reitor da Nomeação',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'linha' => 2),
        array('nome' => 'numProcNom',
            'label' => 'Processo:',
            'tipo' => 'processo',
            'size' => 30,
            'title' => 'Número do Processo',
            'col' => 3,
            'linha' => 2),
        array('nome' => 'dtPublicNom',
            'label' => 'Data da Publicação:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data da Publicação no DOERJ.',
            'linha' => 2),
        array('nome' => 'dtExo',
            'label' => 'Data da Exoneração:',
            'fieldset' => 'Exoneração',
            'tipo' => 'data',
            'col' => 3,
            'size' => 20,
            'title' => 'Data da Exoneração.',
            'linha' => 3),
        array('nome' => 'dtAtoExo',
            'label' => 'Data do Ato do Reitor:',
            'title' => 'Data do Ato do Reitor da Exoneraçao',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'linha' => 3),
        array('nome' => 'numProcExo',
            'label' => 'Processo:',
            'tipo' => 'texto',
            'size' => 30,
            'col' => 3,
            'title' => 'Processo de Exoneração',
            'linha' => 3),
        array('nome' => 'dtPublicExo',
            'label' => 'Data da Publicação:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data da Publicação no DOERJ.',
            'linha' => 3),
        array('linha' => 4,
            'nome' => 'obs',
            'col' => 12,
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'fieldset' => 'fecha',
            'size' => array(80, 4)),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 5)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Botão Extra
    $botaoVagas = new Button("Vagas", "?fase=vagas");
    $botaoVagas->set_title('Exibe a disponibilidade dos cargos em comissão');
    $botaoVagas->set_accessKey('a');

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Cargo em Comissão");
    $botaoRel->set_url("../grhRelatorios/servidorComissao.php");
    $botaoRel->set_target("_blank");

    $objeto->set_botaoListarExtra(array($botaoRel, $botaoVagas));

    # Constroi o link de voltar de acordo com a origem
    if (!vazio($origem)) {
        $objeto->set_linkListar($origem);
        $objeto->set_voltarForm($origem);
    }


    ################################################################

    switch ($fase) {

        case "" :
        case "listar" :
            $objeto->listar();
            break;

        ######################################   

        case "editar" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cadastrar Descrição - visivel somente para adm
            if (Verifica::acesso($idUsuario, 1)) {

                $botao1 = new Button("Descrição");
                $botao1->set_title("Cadastra uma nova Descrição");
                $botao1->set_target("_blank");
                $botao1->set_url("cadastroDescricaoComissao.php?fase=editar&origem=servidor");

                $objeto->set_botaoEditarExtra(array($botao1));
            }

            $objeto->editar($id);

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        ######################################

        case "excluir" :
            $objeto->$fase($id);
            break;

        ######################################

        case "gravar" :
            $objeto->gravar($id, 'servidorComissaoExtra.php');
            break;

        ######################################

        case "atoNomeacao" :
            # Verifica se o campo ocupante anterior foi preenchido
            $comissao = $cargoComissao->get_dados($id);
            $ocupanteAnterior = $comissao['ocupanteAnterior'];
            $dtAtoNom = date_to_php($comissao['dtAtoNom']);
            $msgErro = null;
            $erro = 0;

            # Verifica se tem ocupante anterior esta preenchido
            if (is_null($ocupanteAnterior)) {
                $msgErro .= 'O campo Ocupante Anterior deve estar preenchido!\n';
                $erro = 1;
            }

            # Verifica se a data do ato do reitor de nomeaçao esta preenchido
            if (is_null($dtAtoNom)) {
                $msgErro .= 'A data do ato do reitor de nomeaçao deve estar preenchida!\n';
                $erro = 1;
            }

            # Verifica se tem algum erro
            if ($erro == 0) {
                loadPage('../grhRelatorios/comissao.AtoNomeacao.php?id=' . $id, '_blank');
            } else {
                alert($msgErro);
                back(1);
            }

            loadPage('?');
            break;

        ################################################################################################################
        # Ato Nomeação
        case "atoNomeacao2" :

            # Voltar
            botaoVoltar("?");

            # Dados do Servidor
            get_DadosServidor($idServidorPesquisado);

            # Pega os Dados
            $dados = $cargoComissao->get_dados($id);
            $ocupanteAnterior = $dados['ocupanteAnterior'];
            $dtAtoNom = $dados['dtAtoNom'];
            $descricao = $cargoComissao->get_descricaoCargo($id);

            if (vazio($ocupanteAnterior)) {
                $ocupanteAnterior = $cargoComissao->get_ocupanteAnterior($id);
            }


            # Limita a tela
            $grid = new Grid("center");
            $grid->abreColuna(10);
            br(3);

            # Título
            tituloTable("Ato de Nomeação de Cargo Em Comissão<br/>" . $descricao);
            $painel = new Callout();
            $painel->abre();

            # Monta o formulário para confirmação dos dados necessários a emissão da CI
            $form = new Form('?fase=atoNomeacaoFormValida&id=' . $id);

            # ocupante Anterior
            $controle = new Input('ocupanteAnterior', 'texto', 'Ocupente Anterior:', 1);
            $controle->set_size(200);
            $controle->set_linha(1);
            $controle->set_col(12);
            #$controle->set_required(true);
            $controle->set_autofocus(true);
            $controle->set_valor($ocupanteAnterior);
            $controle->set_title('Ocupante Anterior.');
            $form->add_item($controle);

            # DAta do Ato de Nomeação
            $controle = new Input('dtAtoNom', 'data', 'Data do Ato de Nomeação:', 1);
            $controle->set_size(10);
            $controle->set_linha(2);
            $controle->set_col(3);
            $controle->set_valor($dtAtoNom);
            #$controle->set_required(true);
            $controle->set_title('A data do Ato de Nomeação.');
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

            $cargoComissao->exibeNomeadosDescricao($id);

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "atoNomeacaoFormValida" :

            # Pega os Dados
            $dados = $cargoComissao->get_dados($id);
            $ocupanteAnterior = $dados['ocupanteAnterior'];
            $dtAtoNom = $dados['dtAtoNom'];

            # Pega os dados Digitados
            $botaoEscolhido = get_post_action("salvar", "imprimir");
            $ocupanteAnteriorDigitado = vazioPraNulo(post("ocupanteAnterior"));
            $dtAtoNomDigitado = vazioPraNulo(post("dtAtoNom"));

            # Prepara para enviar por get
            $array = array($ocupanteAnteriorDigitado, $dtAtoNomDigitado);
            $array = serialize($array);

            # Verifica se houve alterações
            $alteracoes = null;
            $atividades = null;

            # Verifica as alterações para o log
            if ($ocupanteAnterior <> $ocupanteAnteriorDigitado) {
                $alteracoes .= "[numCiInicio] $ocupanteAnterior -> $ocupanteAnteriorDigitados; ";
            }
            if ($dtAtoNom <> $dtAtoNomDigitado) {
                $alteracoes .= "[dtAtoNom] " . date_to_bd($dtAtoNom) . "->" . date_to_bd($dtAtoNomDigitado) . "; ";
            }

            # Erro
            $msgErro = null;
            $erro = 0;

            # Verifica o número da Ci
            if (vazio($ocupanteAnteriorDigitado)) {
                $msgErro .= 'O ocuppante anterior deve ser informado!\n';
                $erro = 1;
            }

            # Verifica a data da CI
            if (vazio($dtAtoNomDigitado)) {
                $msgErro .= 'Não tem data do Ato de Nomeação cadastrada!\n';
                $erro = 1;
            }

            # Salva as alterações
            $pessoal->set_tabela("tbcomissao");
            $pessoal->set_idCampo("idComissao");
            $campoNome = array('ocupanteAnterior', 'dtAtoNom');
            $campoValor = array($ocupanteAnteriorDigitado, $dtAtoNomDigitado);
            $pessoal->gravar($campoNome, $campoValor, $id);
            $data = date("Y-m-d H:i:s");

            # Grava o log das alterações caso tenha
            if (!is_null($alteracoes)) {
                $atividades .= 'Alterou: ' . $alteracoes;
                $tipoLog = 2;
                $intra->registraLog($idUsuario, $data, $atividades, "tbcomissao", $id, $tipoLog, $idServidorPesquisado);
            }


            # Exibe o relatório ou salva de acordo com o botão pressionado
            if ($botaoEscolhido == "imprimir") {
                if ($erro == 0) {
                    # Exibe o relatório
                    loadPage("../grhRelatorios/comissao.AtoNomeacao.php?id=$id", "_blank");
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

        case "termoPosse" :
            # Verifica se o campo ocupante anterior foi preenchido
            $comissao = $cargoComissao->get_dados($id);
            $publicacao = $comissao['dtPublicNom'];
            $dtAtoNom = $comissao['dtAtoNom'];
            $msgErro = null;

            # Verifica se tem ocupante anterior esta preenchido
            if (is_null($publicacao)) {
                $msgErro .= 'O campo da data de publicaçao da nomeaçao deve estar preenchido!\n';
                $erro = 1;
            }

            # Verifica se a data do ato do reitor de nomeaçao esta preenchido
            if (is_null($dtAtoNom)) {
                $msgErro .= 'A data do ato do reitor de nomeaçao deve estar preenchida!\n';
                $erro = 1;
            }

            # Verifica se tem algum erro
            if ($erro == 0) {

                loadPage('../grhRelatorios/comissao.TermodePosse.php?id=' . $id, '_blank');
            } else {
                alert($msgErro);
                back(1);
            }

            loadPage('?');
            break;

        ######################################

        case "atoExoneracao" :
            # Verifica se o campo ocupante anterior foi preenchido
            $comissao = $cargoComissao->get_dados($id);
            $dtExo = $comissao['dtExo'];
            $dtAtoExo = date_to_php($comissao['dtAtoExo']);
            $msgErro = null;

            # Verifica se tem ocupante anterior esta preenchido
            if (is_null($dtExo)) {
                $msgErro .= 'A data de exoneração esta em branco!!\n';
                $erro = 1;
            }

            # Verifica se a data do ato do reitor de nomeaçao esta preenchido
            if (is_null($dtAtoExo)) {
                $msgErro .= 'A data do ato do reitor de exoneraçao esta em branco!\n';
                $erro = 1;
            }

            # Verifica se tem algum erro
            if ($erro == 0) {
                loadPage('../grhRelatorios/comissao.AtoExoneracao.php?id=' . $id, '_blank');
            } else {
                alert($msgErro);
                back(1);
            }

            loadPage('?');
            break;

        ######################################

        case "vagas" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);

            botaoVoltar("?");
            titulo("Vagas dos Cargos em Comissão");

            Grh::quadroVagasCargoComissao();
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}