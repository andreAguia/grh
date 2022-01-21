<?php

/**
 * Licença 
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
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Histórico de licenças";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio da área de Redução
    $origem = get_session("origem");

    # botão de voltar da lista
    if (empty($origem)) {
        $voltar = 'servidorMenu.php';
    } else {
        $voltar = $origem;
    }

    # pega o idTpLicenca (se tiver)
    $idTpLicenca = soNumeros(get('idTpLicenca'));

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))) {     # Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro')); # passa o parametro da session para a variavel parametro retirando as aspas
    } else {
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro', $parametro);    # transfere para a session para poder recuperá-lo depois
    }

    # Ordem da tabela
    $orderCampo = get('orderCampo');
    $orderTipo = get('orderTipo');

    # Cria dinamicamente uma rotina em jquery para exibir ou não 
    # o input de acordo com as exgências na tbtipolicenca
    # Pega o id de cada tipo de licençca
    $selectTipo = $pessoal->select('SELECT idTpLicenca FROM tbtipolicenca ORDER BY idTpLicenca');

    # Início do código 
    $script = '<script type="text/javascript" language="javascript">
            
            $(document).ready(function(){';

    # Retira todos os controles
    $script .= '
                $("#alta").hide();
                $("#labelalta").hide();
                $("#dtInicioPeriodo").hide();
                $("#labeldtInicioPeriodo").hide();
                $("#dtFimPeriodo").hide();
                $("#labeldtFimPeriodo").hide();
                $("#processo").hide();
                $("#labelprocesso").hide();
                $("#dtPublicacao").hide();
                $("#labeldtPublicacao").hide();
                $("#dtPericia").hide();
                $("#labeldtPericia").hide();
                $("#num_Bim").hide();
                $("#labelnum_Bim").hide();
                
                ';

    # Verifica se é edição
    if (!is_null($id)) {
        # Pega o tipo de licença desse id
        $idTipo = $pessoal->get_tipoLicenca($id);

        # Exibe campos de tipo e alta se for Licença Médica (Inicial e Prorrogaçao) ou tratamento na familia
        if (($idTipo == 1) OR ($idTipo == 30) OR ($idTipo == 2)) {
            $script .= '$("#alta").show();
                        $("#labelalta").show();
                        ';
        }

        # Percorre o resultado
        foreach ($selectTipo as $tipo) {

            if ($idTipo == $tipo[0]) {

                # Exibe o período aquisitivo
                if ($pessoal->get_licencaPeriodo($tipo[0]) == "Sim") {
                    $script .= ' $("#dtInicioPeriodo").show();
                                     $("#labeldtInicioPeriodo").show();
                                     $("#dtFimPeriodo").show();
                                     $("#labeldtFimPeriodo").show();';
                }

                # Exibe o processo
                if ($pessoal->get_licencaProcesso($tipo[0]) == "Sim") {
                    $script .= ' $("#processo").show();
                                     $("#labelprocesso").show();';
                }

                # Exibe a publicação
                if ($pessoal->get_licencaPublicacao($tipo[0]) == "Sim") {
                    $script .= ' $("#dtPublicacao").show();
                                     $("#labeldtPublicacao").show();';
                }

                # Verifica se essa licença necessita de perícia
                if ($pessoal->get_licencaPericia($tipo[0]) == "Sim") {
                    $script .= ' $("#dtPericia").show();
                                     $("#labeldtPericia").show();
                                     $("#num_Bim").show();
                                     $("#labelnum_Bim").show();';
                }
            }
        }
    } else {
        # Se for inclusão a mudança se faz pelo que está na combo
        $script .= '
            // Guarda na variável id o valor alterado
            var id = $("#idTpLicenca option:selected").val();
            
                        if(id == 1 || id == 30 || id == 2) {
                            $("#alta").show();
                            $("#labelalta").show();
                        }else{
                            $("#alta").hide();
                            $("#labelalta").hide();
                        }
                        ';
    }

    $script .= '    
            // Executa rotina sempre que o valor do select mudar
            $("#idTpLicenca").change(function(){

            // Guarda na variável id o valor alterado
            var id = $("#idTpLicenca option:selected").val();
            ';

    # Licença Médica
    $script .= '
                        if(id == 1 || id == 30 || id == 2) {
                            $("#alta").show();
                            $("#labelalta").show();
                        }else{
                            $("#alta").hide();
                            $("#labelalta").hide();
                        }
                        ';

    # Percorre o resultado
    foreach ($selectTipo as $tipo) {
        $script .= '
            if(id == ' . $tipo[0] . '){';
        # Preenche o numero de dias 
        $numDias = $pessoal->get_licencaDias($tipo[0]);
        if ($numDias > 0) {
            $script .= ' $("#numDias").val(' . $numDias . ');';
        }

        # Exibe o período aquisitivo
        if ($pessoal->get_licencaPeriodo($tipo[0]) == "Sim") {
            $script .= ' $("#dtInicioPeriodo").show();
                                 $("#labeldtInicioPeriodo").show();
                                 $("#dtFimPeriodo").show();
                                 $("#labeldtFimPeriodo").show();';
        } else {
            $script .= ' $("#dtInicioPeriodo").hide();
                                 $("#labeldtInicioPeriodo").hide();
                                 $("#dtFimPeriodo").hide();
                                 $("#labeldtFimPeriodo").hide();';
        }

        # Exibe o processo
        if ($pessoal->get_licencaProcesso($tipo[0]) == "Sim") {
            $script .= ' $("#processo").show();
                                 $("#labelprocesso").show();';
        } else {
            $script .= ' $("#processo").hide();
                                 $("#labelprocesso").hide();';
        }

        # Exibe a publicação
        if ($pessoal->get_licencaPublicacao($tipo[0]) == "Sim") {
            $script .= ' $("#dtPublicacao").show();
                                 $("#labeldtPublicacao").show();';
        } else {
            $script .= ' $("#dtPublicacao").hide();
                                 $("#labeldtPublicacao").hide();';
        }

        # Verifica se essa licença necessita de perícia
        if ($pessoal->get_licencaPericia($tipo[0]) == "Sim") {
            $script .= ' $("#dtPericia").show();
                                 $("#labeldtPericia").show();
                                 $("#num_Bim").show();
                                 $("#labelnum_Bim").show();';
        } else {
            $script .= ' $("#dtPericia").hide();
                                 $("#labeldtPericia").hide();
                                 $("#num_Bim").hide();
                                 $("#labelnum_Bim").hide();';
        }

        $script .= '  
                       }
                       ';
    }

    $script .= '});
                     });</script>';

    $script .= '<script type="text/javascript" language="javascript">
        
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

    # Verifica se o Servidor tem direito a licença
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);

    if ($pessoal->get_perfilLicenca($idPerfil) == "Não") {
        $mensagem = 'Esse servidor está em um perfil que não pode ter licença !!';
        $alert = new Alert($mensagem);
        $alert->show();
        loadPage('servidorMenu.php');
    } else {

        # Abre um novo objeto Modelo
        $objeto = new Modelo();

        ################################################################
        # Exibe os dados do Servidor
        $objeto->set_rotinaExtra("get_DadosServidor");
        $objeto->set_rotinaExtraParametro($idServidorPesquisado);

        # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
        $objeto->set_nome('Afastamentos e Licenças');

        # botão de voltar da lista
        $objeto->set_voltarLista($voltar);

        # Pega os dados da combo licenca da pesquisa
        $result = $pessoal->select('SELECT distinct tblicenca.idTpLicenca, tbtipolicenca.nome
                                      FROM tblicenca LEFT JOIN tbtipolicenca ON tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca
                                     WHERE idServidor=' . $idServidorPesquisado . '                                         
                                  ORDER BY 2');
        array_unshift($result, array(null, '-- Todos --'));

        # controle de pesquisa
        $objeto->set_parametroLabel('Pesquisar');
        $objeto->set_parametroValue($parametro);
        $objeto->set_tipoCampoPesquisa("combo");
        $objeto->set_arrayPesquisa($result);

        # select da lista
        $selectLicença = '(SELECT tblicenca.idTpLicenca,
                                  tblicenca.idTpLicenca,
                                     CASE alta
                                        WHEN 1 THEN "Sim"
                                        WHEN 2 THEN "Não"
                                        end,
                                     dtInicial,
                                     numdias,
                                     ADDDATE(dtInicial,numDias-1),
                                     CONCAT(tblicenca.idTpLicenca,"&",idLicenca),
                                     dtPublicacao,
                                     CONCAT("tblicenca","&",idLicenca),
                                     idLicenca
                                FROM tblicenca LEFT JOIN tbtipolicenca ON tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca
                               WHERE idServidor=' . $idServidorPesquisado;
        if (!vazio($parametro)) {
            $selectLicença .= ' AND tbtipolicenca.idTpLicenca = ' . $parametro . ' ORDER BY 4 desc)';
        } else {
            $selectLicença .= ')
                               UNION
                               (SELECT 6,
                                       6,"",
                                       dtInicial,
                                       tblicencapremio.numdias,
                                       ADDDATE(dtInicial,tblicencapremio.numDias-1),
                                       CONCAT("6&",tblicencapremio.idServidor),
                                       tbpublicacaopremio.dtPublicacao,
                                       CONCAT("tblicencapremio","&",tblicencapremio.idServidor),
                                       "-"
                                  FROM tblicencapremio LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                                 WHERE tblicencapremio.idServidor = ' . $idServidorPesquisado . ')
                                     UNION
                               (SELECT tblicencasemvencimentos.idTpLicenca,
                                       tblicencasemvencimentos.idTpLicenca,"",
                                       tblicencasemvencimentos.dtInicial,
                                       tblicencasemvencimentos.numDias,
                                       CONCAT("tblicencasemvencimentos","&",idLicencaSemVencimentos),                                       
                                       CONCAT(tblicencasemvencimentos.idTpLicenca,"&",idLicencaSemVencimentos),
                                       tblicencasemvencimentos.dtPublicacao,
                                       CONCAT("tblicencasemvencimentos","&",idLicencaSemVencimentos),
                                       "-"
                                  FROM tblicencasemvencimentos LEFT JOIN tbtipolicenca USING (idTpLicenca)
                                 WHERE tblicencasemvencimentos.idServidor = ' . $idServidorPesquisado . ')
                              ORDER BY 4 desc';
        }

        $objeto->set_selectLista($selectLicença);

        # select do edita
        $objeto->set_selectEdita('SELECT idTpLicenca,
                                   alta,
                                   dtInicioPeriodo,
                                   dtFimPeriodo,
                                   dtInicial,
                                   numDias,
                                   dtTermino,
                                   processo,
                                   dtPublicacao,
                                   dtPericia,
                                   num_Bim,
                                   obs,
                                   idServidor
                              FROM tblicenca WHERE idLicenca = ' . $id);

        # Caminhos
        $objeto->set_linkEditar('?fase=editar');
        #$objeto->set_linkExcluir('?fase=excluir');
        $objeto->set_linkGravar('?fase=gravar');
        $objeto->set_linkListar('?fase=listar');
        $objeto->set_botaoEditar(false);

        # Cria uma string para usar na comparação 
        # do editar e excluir condicional de forma 
        # permitir edição somente das licenças que 
        # não forem licença premio.
        $nome = $pessoal->get_licencaNome(6);
        $lei = $pessoal->get_licencaLei(6);
        $stringComparacao = $nome . "<br/>" . $lei;

        # Editar e excluir condicional
        $objeto->set_editarCondicional('?fase=editar', '-', 9, "<>");
        $objeto->set_excluirCondicional('?fase=excluir', '-', 9, "<>");

        # Parametros da tabela
        $objeto->set_label(array("Licença ou Afastamento", "Doc.", "Alta", "Inicio", "Dias", "Término", "Processo", "Publicação", "Obs"));
        $objeto->set_width(array(30, 3, 3, 10, 5, 10, 15, 5, 4));
        $objeto->set_align(array("left"));
        $objeto->set_funcao(array(null, "exibeBotaoDocumentacaoLicenca", null, 'date_to_php', null, 'exibeDtTermino', 'exibeProcesso', 'date_to_php', "exibeObsLicenca"));
        $objeto->set_classe(array("Licenca"));
        $objeto->set_metodo(array("exibeNome"));
        $objeto->set_numeroOrdem(true);
        $objeto->set_numeroOrdemTipo("d");

        # Classe do banco de dados
        $objeto->set_classBd('pessoal');

        # Nome da tabela
        $objeto->set_tabela('tblicenca');

        # Nome do campo id
        $objeto->set_idCampo('idLicenca');

        # Tipo de label do formulário
        $objeto->set_formLabelTipo(1);

        # Pega os dados da combo licenca do formulário
        $result = $pessoal->select('SELECT idTpLicenca, tbtipolicenca.nome
                                      FROM tbtipolicenca
                                     WHERE idTpLicenca <> 6
                                       AND idTpLicenca <> 5
                                       AND idTpLicenca <> 8
                                       AND idTpLicenca <> 16
                                  ORDER BY 2');
        array_unshift($result, array('Inicial', ' -- Selecione o Tipo de Afastamento ou Licença --')); # Adiciona o valor de nulo
        # Habilita ou não os controles de acordo com a licença
        # Campos para o formulario
        $objeto->set_campos(array(
            array('nome' => 'idTpLicenca',
                'label' => 'Tipo de Afastamento ou Licença:',
                'tipo' => 'combo',
                'size' => 50,
                'array' => $result,
                'required' => true,
                'autofocus' => true,
                'title' => 'Tipo do Adastamento/Licença.',
                'col' => 12,
                'linha' => 1),
            array('nome' => 'alta',
                'label' => 'Alta: *',
                'tipo' => 'combo',
                'size' => 20,
                'array' => array(array(null, null),
                    array(2, "Não"),
                    array(1, "Sim")),
                'col' => 2,
                'linha' => 2),
            array('nome' => 'dtInicioPeriodo',
                'label' => 'Período Aquisitivo Início:',
                'tipo' => 'data',
                'size' => 20,
                'title' => 'Data de início do período aquisitivo',
                'col' => 3,
                'linha' => 3),
            array('nome' => 'dtFimPeriodo',
                'label' => 'Período Aquisitivo Término:',
                'tipo' => 'data',
                'size' => 20,
                'col' => 3,
                'title' => 'Data de término do período aquisitivo',
                'linha' => 3),
            array('nome' => 'dtInicial',
                'label' => 'Data Inicial:',
                'tipo' => 'data',
                'required' => true,
                'size' => 20,
                'col' => 3,
                'title' => 'Data do início.',
                'linha' => 4),
            array('nome' => 'numDias',
                'label' => 'Dias:',
                'tipo' => 'numero',
                'size' => 5,
                'title' => 'Número de dias.',
                'col' => 2,
                'linha' => 4),
            array('nome' => 'dtTermino',
                'label' => 'Data de Termino (opcional):',
                'tipo' => 'data',
                'size' => 20,
                'col' => 3,
                'title' => 'Data de Termino.',
                'linha' => 4),
            array('nome' => 'processo',
                'label' => 'Processo:',
                'tipo' => 'processo',
                'size' => 30,
                'col' => 5,
                'title' => 'Número do Processo',
                'linha' => 5),
            array('nome' => 'dtPublicacao',
                'label' => 'Data da Pub. no DOERJ:',
                'tipo' => 'data',
                'size' => 20,
                'title' => 'Data da Publicação no DOERJ.',
                'col' => 3,
                'linha' => 6),
            array('nome' => 'dtPericia',
                'label' => 'Data da Perícia:',
                'tipo' => 'data',
                'size' => 20,
                'title' => 'Data da Perícia.',
                'col' => 3,
                'linha' => 7),
            array('nome' => 'num_Bim',
                'label' => 'Número da Bim:',
                'tipo' => 'texto',
                'size' => 30,
                'col' => 4,
                'title' => 'Número da Bim',
                'linha' => 7),
            array('linha' => 8,
                'nome' => 'obs',
                'label' => 'Observação:',
                'tipo' => 'textarea',
                'size' => array(80, 3)),
            array('nome' => 'idServidor',
                'label' => 'idServidor:',
                'tipo' => 'hidden',
                'padrao' => $idServidorPesquisado,
                'size' => 5,
                'linha' => 9)));

        # Log
        $objeto->set_idUsuario($idUsuario);
        $objeto->set_idServidorPesquisado($idServidorPesquisado);

        # Publicação de Licença Prêmio
        $botaoPremio = new Button($pessoal->get_licencaNome(6));
        $botaoPremio->set_title("Acessa o Cadastro de Licença Prêmio");
        $botaoPremio->set_url('servidorLicencaPremio.php');
        $botaoPremio->set_accessKey('L');

        $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
        $botaoRel = new Button();
        $botaoRel->set_imagem($imagem);
        $botaoRel->set_title("Relatório de Licença");
        $botaoRel->set_url("../grhRelatorios/servidorLicenca.php?parametro=" . $parametro);
        $botaoRel->set_target("_blank");

        $objeto->set_botaoListarExtra(array($botaoRel));

        ################################################################

        switch ($fase) {
            case "" :
            case "listar" :
            case "editar" :
            case "excluir" :
                $objeto->$fase($id);
                break;

            case "gravar" :
                $objeto->gravar($id, "servidorLicencaExtra.php");
                break;

            case "documentacao" :
                $grid = new Grid();
                $grid->abreColuna(12);

                botaoVoltar("?");
                exibeDocumentacaoLicenca($idTpLicenca);

                $grid->fechaColuna();
                $grid->fechaGrid();
                break;
        }
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}