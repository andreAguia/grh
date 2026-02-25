<?php

/**
 * Histórico de Formação Escolar do Servidor
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
        $atividade = "Cadastro do servidor - Formação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica de onde veio
    $origem = get_session("origem");

    # botão de voltar da lista
    if (empty($origem)) {
        $voltar = 'servidorMenu.php';
    } else {
        $voltar = $origem;
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))) {
        $parametro = retiraAspas(get_session('sessionParametro'));
    } else {
        $parametro = post('parametro');
        set_session('sessionParametro', $parametro);
    }

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

    # Começa uma nova página
    $page = new Page();
    if ($fase == "upload") {
        $page->set_ready('$(document).ready(function(){
                                $("form input").change(function(){
                                    $("form p").text(this.files.length + " arquivo(s) selecionado");
                                });
                            });');
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    if ($fase <> "upload" AND $fase <> "uploadTerminado" AND $fase <> "apagaDocumento" AND $fase <> "relatorioPetec") {
        AreaServidor::cabecalho();
    }

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra(["get_DadosServidor", "exibeDadosPetec"]);
    $objeto->set_rotinaExtraParametro([$idServidorPesquisado, $idServidorPesquisado]);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro da Formação Escolar do Servidor');

    # botão de voltar da lista
    $objeto->set_voltarLista($voltar);

    # select da lista
    $selectFormacao = "SELECT anoTerm,
                              escolaridade,
                              idFormacao,
                              habilitacao,                              
                              instEnsino,
                              idFormacao,
                              idFormacao,
                              idFormacao,
                              idFormacao
                         FROM tbformacao LEFT JOIN tbescolaridade USING (idEscolaridade)
                                         LEFT JOIN tbformacaomarcador A ON (marcador1 = A.idFormacaoMarcador) 
                                         LEFT JOIN tbformacaomarcador B ON (marcador2 = B.idFormacaoMarcador) 
                                         LEFT JOIN tbformacaomarcador C ON (marcador3 = C.idFormacaoMarcador) 
                                         LEFT JOIN tbformacaomarcador D ON (marcador4 = D.idFormacaoMarcador) 
                        WHERE idPessoa={$idPessoa}";

    if (!empty($parametro)) {
        $selectFormacao .= " AND (escolaridade LIKE '%{$parametro}%' 
                              OR habilitacao LIKE '%{$parametro}%'
                              OR instEnsino LIKE '%{$parametro}%'
                              OR anoTerm LIKE '%{$parametro}%'
                              OR A.marcador LIKE '%{$parametro}%'
                              OR B.marcador LIKE '%{$parametro}%'
                              OR C.marcador LIKE '%{$parametro}%'
                              OR D.marcador LIKE '%{$parametro}%'
                              OR horas LIKE '%{$parametro}%')";
    }

    $selectFormacao .= " ORDER BY anoTerm desc, A.marcador desc, B.marcador desc, C.marcador desc, D.marcador desc";

    $objeto->set_selectLista($selectFormacao);

    # select do edita
    $objeto->set_selectEdita("SELECT idEscolaridade,
                                     habilitacao,
                                     instEnsino,                                     
                                     horas,
                                     minutos,
                                     anoTerm,
                                     marcador1,
                                     marcador2,
                                     marcador3,
                                     marcador4,
                                     obs,
                                     idPessoa
                                FROM tbformacao
                               WHERE idFormacao = {$id}");

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
    $objeto->set_label(["Ano de Término", "Nível do Curso", "Marcadores", "Curso", "Instituição", "Carga Horária", "Ver"]);
    $objeto->set_width([5, 10, 10, 30, 20, 10, 5]);
    $objeto->set_align(["center", "center", "center", "left", "left"]);

    #$objeto->set_funcao([null, null, null, null, null, "trataNulo"]);

    $objeto->set_classe([null, null, "Formacao", null, null, "Formacao", "Formacao"]);
    $objeto->set_metodo([null, null, "exibeMarcador", null, null, "exibeHora", "exibeCertificado"]);

    #$objeto->set_colunaSomatorio(5);

    $objeto->set_rowspan(0);
    $objeto->set_grupoCorColuna(0);

    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbformacao');

    # Nome do campo id
    $objeto->set_idCampo('idFormacao');

    # Pega os dados da combo escolaridade
    $result = $pessoal->select('SELECT idEscolaridade, 
                                            escolaridade
                                       FROM tbescolaridade
                                   ORDER BY idEscolaridade');
    array_unshift($result, array(null, null));

    # Pega os dados da datalist curso
    $cursos = $pessoal->select('SELECT distinct habilitacao
                                       FROM tbformacao
                                   ORDER BY habilitacao');
    array_unshift($cursos, array(null));

    # Pega os dados da datalist instEnsino
    $instEnsino = $pessoal->select('SELECT distinct instEnsino
                                       FROM tbformacao
                                   ORDER BY instEnsino');
    array_unshift($instEnsino, array(null));

    # Pega os dados da datalist marcador
    $formacao = new Formacao();
    $arrayMarcador = $formacao->get_arrayMarcadores();
    array_unshift($arrayMarcador, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'idEscolaridade',
            'label' => 'Nível do Curso:',
            'tipo' => 'combo',
            'array' => $result,
            'required' => true,
            'autofocus' => true,
            'size' => 20,
            'col' => 4,
            'title' => 'Nível do Curso.',
            'linha' => 1),
        array('nome' => 'habilitacao',
            'label' => 'Curso:',
            'tipo' => 'texto',
            'datalist' => $cursos,
            'plm' => true,
            'trim' => true,
            'size' => 250,
            'col' => 8,
            'required' => true,
            'title' => 'Nome do curso.',
            'linha' => 1),
        array('nome' => 'instEnsino',
            'label' => 'Instituição de Ensino:',
            'tipo' => 'texto',
            'datalist' => $instEnsino,
            'size' => 250,
            'plm' => true,
            'trim' => true,
            'col' => 6,
            'required' => true,
            'title' => 'Nome da Instituição de Ensino.',
            'linha' => 2),
        array('nome' => 'horas',
            'label' => 'Horas:',
            'tipo' => 'texto',
            'size' => 10,
            'col' => 1,
            'title' => 'Horas de curso.',
            'linha' => 2),
        array('nome' => 'minutos',
            'label' => 'Minutos:',
            'tipo' => 'numero',
            'padrao' => 0,
            'size' => 10,
            'col' => 1,
            'title' => 'minutos.',
            'linha' => 2),
        array('nome' => 'anoTerm',
            'label' => 'Ano de Término:',
            'tipo' => 'numero',
            'max' => date("Y"),
            'min' => year($pessoal->get_dataNascimento($idServidorPesquisado)),
            'size' => 5,
            'col' => 3,
            'title' => 'Nome da Instituição de Ensino.',
            'linha' => 2),
        array('nome' => 'marcador1',
            'label' => '',
            'fieldset' => 'Marcadores:',
            'tipo' => 'combo',
            'array' => $arrayMarcador,
            'size' => 50,
            'col' => 6,
            'title' => 'Marcador.',
            'linha' => 3),
        array('nome' => 'marcador2',
            'label' => '',
            'tipo' => 'combo',
            'array' => $arrayMarcador,
            'size' => 50,
            'col' => 6,
            'title' => 'Marcador.',
            'linha' => 3),
        array('nome' => 'marcador3',
            'label' => '',
            'tipo' => 'combo',
            'array' => $arrayMarcador,
            'size' => 50,
            'col' => 6,
            'title' => 'Marcador.',
            'linha' => 4),
        array('nome' => 'marcador4',
            'label' => '',
            'tipo' => 'combo',
            'array' => $arrayMarcador,
            'size' => 50,
            'col' => 6,
            'title' => 'Marcador.',
            'linha' => 4),
        array('linha' => 5,
            'nome' => 'obs',
            'fieldset' => 'fecha',
            'col' => 12,
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'size' => array(80, 5)),
        array('nome' => 'idPessoa',
            'label' => 'idPessoa:',
            'tipo' => 'hidden',
            'padrao' => $idPessoa,
            'size' => 6,
            'title' => 'idPessoa',
            'linha' => 5)));

    # Edita inscrição
    $botao1 = new Link("Editar Inscrição Petec", "servidorPetec.php");
    $botao1->set_class('success button');

    # Relatórios Petec
    $botaoPetec = new Link("Relatório do Petec", "?fase=relatorioPetec");
    $botaoPetec->set_class('button');
    $botaoPetec->set_target("_blank");

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Formação");
    $botaoRel->set_onClick("window.open('../grhRelatorios/servidorFormacao.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");

    # A princípio somente administradores cadastram a inscrição
    if (Verifica::acesso($idUsuario, 1)) {
        $objeto->set_botaoListarExtra(array($botaoRel, $botao1, $botaoPetec));
    } else {
        $objeto->set_botaoListarExtra(array($botaoRel, $botaoPetec));
    }

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Dados da rotina de Upload
    $pasta = PASTA_CERTIFICADO;
    $nome = "Certificado";
    $tabela = "tbformacao";
    $extensoes = ["pdf"];

    # Botão de Upload
    if (!empty($id)) {

        # Botão de Upload
        $botao = new Button("Upload {$nome}");
        $botao->set_url("servidorFormacaoUpload.php?fase=upload&id={$id}");
        $botao->set_title("Faz o Upload do {$nome}");
        $botao->set_target("_blank");

        $objeto->set_botaoEditarExtra([$botao]);
    }

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
        case "editar" :
            $objeto->$fase($id);
            break;

        case "gravar" :
            $objeto->gravar($id, "servidorFormacaoExtra.php");
            break;

        case "excluir" :
            # Verifica se tem arquivo vinculado
            if (file_exists("{$pasta}{$id}.pdf")) {

                # Verifica se existe a pasta dos arquivos apagados
                if (!file_exists("{$pasta}_apagados/") || !is_dir("{$pasta}_apagados/")) {
                    mkdir("{$pasta}_apagados/", 0755);
                }

                # Move o arquivo para a pasta dos arquivos apagados
                rename("{$pasta}{$id}.pdf", "{$pasta}_apagados/{$id}_" . $intra->get_usuario($idUsuario) . "_" . date("Y.m.d_H:i") . ".pdf");
            }


            # Exclui o registro
            $objeto->excluir($id);
            break;

        case "relatorioPetec":
            # Pega os dados das portarias
            $petec = new Petec();

            $formacao = new Formacao();
            $arrayMarcadores = $formacao->get_arrayMarcadores("Petec");
            $contadorMarcadores = count($arrayMarcadores);

            # Contador para o hr
            $contador = 0;

            # Começa uma nova página
            $page = new Page();
            $page->set_title("relatório do Petec");
            $page->iniciaPagina();

            # Pega o parâmetro (se tiver)
            $parametro = retiraAspas(get_session('sessionParametro'));

            if (!empty($parametro)) {
                $subTitulo = "Filtro: {$parametro}";
            }

            ######
            # Dados do Servidor
            Grh::listaDadosServidorRelatorio($idServidorPesquisado, 'Relatório do Petec');
            br();

            ################################################
            # Resumo dos certificados entregues
            $select = "SELECT tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor 
                    WHERE idServidor = {$idServidorPesquisado}";

            $pessoal = new Pessoal();
            $result2 = $pessoal->select($select);

            # Define as colunas
            $label = array();
            $align = array();
            $classe = array();
            $metodo = array();

            foreach ($arrayMarcadores as $item) {
                $label[] = $item[1];
                $align[] = "center";
                $classe[] = "Petec";
                $metodo[] = "somatorioHorasCompleto{$item[0]}"; // Gambiarra para fazer funcionar. Depois eu vejo um modo melhor de fazer isso...
            }

            $tabela = new Relatorio();
            #$tabela->set_subtitulo("Totalização dos Certificados Entregues");
            $tabela->set_conteudo($result2);

            $tabela->set_label($label);
            $tabela->set_align($align);

            $tabela->set_classe($classe);
            $tabela->set_metodo($metodo);
            $tabela->set_totalRegistro(false);
            $tabela->set_cabecalhoRelatorio(false);
            $tabela->set_menuRelatorio(false);

            $tabela->set_bordaInterna(false);
            $tabela->set_exibeLinhaFinal(false);
            $tabela->set_dataImpressao(false);

            $tabela->show();
            br();

            # Pega o idPessoa
            $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

            foreach ($arrayMarcadores as $item) {

                # Incrementa o contador
                $contador++;

                # Pega os dados do Petec
                $arrayPetec = $petec->get_arrayPetec($item[0]);

                p("Certificados Entregues - Portaria " . $arrayPetec[0], "pRelatorioSubtitulo");

                $selectFormacao = "SELECT anoTerm,
                              escolaridade,
                              idFormacao,
                              CONCAT(habilitacao,'<br/>', instEnsino),
                              horas,
                              idFormacao,
                              idFormacao,
                              idFormacao
                        FROM tbformacao LEFT JOIN tbescolaridade USING (idEscolaridade)
                                         LEFT JOIN tbformacaomarcador A ON (marcador1 = A.idFormacaoMarcador) 
                                         LEFT JOIN tbformacaomarcador B ON (marcador2 = B.idFormacaoMarcador) 
                                         LEFT JOIN tbformacaomarcador C ON (marcador3 = C.idFormacaoMarcador) 
                                         LEFT JOIN tbformacaomarcador D ON (marcador4 = D.idFormacaoMarcador) 
                        WHERE idPessoa={$idPessoa} 
                          AND (A.marcador LIKE '%{$arrayPetec[0]}%'
                              OR B.marcador LIKE '%{$arrayPetec[0]}%'
                              OR C.marcador LIKE '%{$arrayPetec[0]}%'
                              OR D.marcador LIKE '%{$arrayPetec[0]}%')
                         ORDER BY anoTerm desc, A.marcador desc, B.marcador desc, C.marcador desc, D.marcador desc";

                $result = $pessoal->select($selectFormacao);

                $relatorio = new Relatorio();
                $tabela->set_subtitulo("Detalhamento dos Certificados Entregues");
                $relatorio->set_cabecalhoRelatorio(false);
                $relatorio->set_menuRelatorio(false);
                $relatorio->set_subTotal(true);
                $relatorio->set_totalRegistro(false);
                $relatorio->set_label(["Ano de Término", "Nível do Curso", "Marcadores", "Curso / Instituição", "Carga Horária"]);
                $relatorio->set_width([10, 15, 20, 45, 10]);
                $relatorio->set_align(["center", "center", "center", "left"]);
                $relatorio->set_classe([null, null, "Formacao"]);
                $relatorio->set_metodo([null, null, "exibeMarcador"]);
                $relatorio->set_colunaSomatorio(4);

                $relatorio->set_conteudo($result);
                $relatorio->set_botaoVoltar(false);
                $relatorio->set_bordaInterna(false);
                $relatorio->set_exibeLinhaFinal(false);
                $relatorio->set_dataImpressao(false);
                $relatorio->set_logServidor($idServidorPesquisado);
                $relatorio->set_logDetalhe("Visualizou o Relatório da Lista de Contatos");
                $relatorio->show();

                if ($contador < $contadorMarcadores) {
                    hr();
                }
            }
            break;

        ################################################################
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}    