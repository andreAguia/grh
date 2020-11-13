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
$acesso = Verifica::acesso($idUsuario, 2);

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

    # Começa uma nova página
    $page = new Page();
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
    $objeto->set_nome('Cadastro de Acumulações de Cargos Públicos');

    $origem = get_session("origem");
    if (is_null($origem)) {
        $caminhoVolta = 'servidorMenu.php';
    } else {
        $caminhoVolta = $origem;
    }

    # botão de voltar da lista
    $objeto->set_voltarLista($caminhoVolta);

    # select da lista
    $objeto->set_selectLista('SELECT CASE conclusao
                                        WHEN 1 THEN "Pendente"
                                        WHEN 2 THEN "Resolvido"
                                        ELSE "--"
                                      END,
                                     idAcumulacao,                                     
                                     dtPublicacao,
                                     idAcumulacao,    
                                     idAcumulacao
                                FROM tbacumulacao
                               WHERE idServidor = ' . $idServidorPesquisado . '
                            ORDER BY dtProcesso');

    # select do edita
    $objeto->set_selectEdita('SELECT processo,
                                     dtProcesso,
                                     origemProcesso,
                                     dtEnvio,
                                     instituicao,
                                     cargo,                                     
                                     matricula,
                                     dtAdmissao,
                                     resultado,
                                     dtPublicacao,
                                     pgPublicacao,
                                     conclusao,
                                     resultado1,
                                     dtPublicacao1,
                                     pgPublicacao1,
                                     resultado2,
                                     dtPublicacao2,
                                     pgPublicacao2,
                                     resultado3,
                                     dtPublicacao3,
                                     pgPublicacao3,
                                     obs,
                                     idServidor
                                FROM tbacumulacao
                               WHERE idAcumulacao = ' . $id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    $objeto->set_formatacaoCondicional(array(array('coluna'   => 0,
            'valor'    => 'Resolvido',
            'operador' => '=',
            'id'       => 'emAberto'),
        array('coluna'   => 0,
            'valor'    => 'Pendente',
            'operador' => '=',
            'id'       => 'alerta')
    ));

    # Parametros da tabela
    $objeto->set_label(array("Conclusão", "Resultado", "Data da<br/>Publicação", "Processo", "Dados do Cargo Acumulado"));
    $objeto->set_align(array("center", "center", "center", "center", "left"));
    $objeto->set_funcao(array(null, null, "date_to_php"));
    $objeto->set_classe(array(null, "Acumulacao", null, "Acumulacao","Acumulacao"));
    $objeto->set_metodo(array(null, "get_resultado", null, "exibeProcesso","exibeDadosOutroVinculo"));

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbacumulacao');

    # Nome do campo id
    $objeto->set_idCampo('idAcumulacao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(array('nome'      => 'processo',
            'label'     => 'Processo:',
            'tipo'      => 'texto',
            'size'      => 30,
            'col'       => 3,
            'title'     => 'Número do Processo',
            'autofocus' => true,
            'linha'     => 1),
        array('nome'  => 'dtProcesso',
            'label' => 'Data do Processo:',
            'tipo'  => 'data',
            'size'  => 20,
            'col'   => 3,
            'title' => 'Data de entrada do processo.',
            'linha' => 1),
        array('nome'  => 'origemProcesso',
            'label' => 'Processo aberto por:',
            'tipo'  => 'texto',
            'size'  => 200,
            'valor' => null,
            'col'   => 3,
            'title' => 'Órgão que abriu o processo.',
            'linha' => 1),
        array('nome'  => 'dtEnvio',
            'label' => 'Data do Envio à COCPP/SECCG:',
            'tipo'  => 'data',
            'size'  => 20,
            'col'   => 3,
            'title' => 'Data do Envio ao Rio.',
            'linha' => 1),
        array('nome'     => 'instituicao',
            'fieldset' => 'Outro Vínculo:',
            'label'    => 'Instituição:',
            'tipo'     => 'texto',
            'size'     => 200,
            'col'      => 6,
            'title'    => 'Instituição Pública.',
            'linha'    => 2),
        array('nome'  => 'cargo',
            'label' => 'Cargo:',
            'tipo'  => 'texto',
            'size'  => 200,
            'col'   => 6,
            'title' => 'Cargo na outra Instituição.',
            'linha' => 2),
        array('nome'  => 'matricula',
            'label' => 'Matrícula:',
            'tipo'  => 'texto',
            'size'  => 20,
            'col'   => 2,
            'title' => 'Matrícula da outra instituição.',
            'linha' => 3),
        array('nome'  => 'dtAdmissao',
            'label' => 'Data de Admissão:',
            'tipo'  => 'data',
            'size'  => 20,
            'col'   => 3,
            'title' => 'Data de admissão da outra instituição',
            'linha' => 3),
        array('nome'     => 'resultado',
            'fieldset' => 'fecha',
            'label'    => 'Resultado:',
            'tipo'     => 'combo',
            'array'    => array(array(null, null),
                array(1, "Lícito"),
                array(2, "Ilícito")),
            'size'     => 2,
            'valor'    => null,
            'col'      => 2,
            'title'    => 'Resultado.',
            'linha'    => 4),
        array('nome'  => 'dtPublicacao',
            'label' => 'Data da Publicação:',
            'tipo'  => 'data',
            'size'  => 20,
            'col'   => 3,
            'title' => 'Data da publicação.',
            'linha' => 4),
        array('nome'  => 'pgPublicacao',
            'label' => 'Página:',
            'tipo'  => 'numero',
            'min'   => 1,
            'max'   => 9999,
            'size'  => 5,
            'col'   => 2,
            'title' => 'A página da Publicação no DOERJ.',
            'linha' => 4),
        array('nome'     => 'conclusao',
            'label'    => 'Conclusão:',
            'tipo'     => 'combo',
            'array'    => array(array(null, null),
                array(1, "Pendente"),
                array(2, "Resolvido")),
            'size'     => 2,
            'required' => true,
            'valor'    => null,
            'col'      => 2,
            'title'    => 'Conclusão.',
            'linha'    => 4),
        array('nome'     => 'resultado1',
            'fieldset' => 'Recursos:',
            'label'    => 'Recurso 1:',
            'tipo'     => 'combo',
            'array'    => array(array(null, null),
                array(1, "Lícito"),
                array(2, "Ilícito")),
            'size'     => 2,
            'valor'    => null,
            'col'      => 2,
            'title'    => 'Resultado.',
            'linha'    => 5),
        array('nome'  => 'dtPublicacao1',
            'label' => 'Data da Publicação:',
            'tipo'  => 'data',
            'size'  => 20,
            'col'   => 3,
            'title' => 'Data da publicação.',
            'linha' => 5),
        array('nome'  => 'pgPublicacao1',
            'label' => 'Página:',
            'tipo'  => 'numero',
            'size'  => 5,
            'col'   => 2,
            'min'   => 1,
            'max'   => 9999,
            'title' => 'A página da Publicação no DOERJ.',
            'linha' => 5),
        array('nome'  => 'resultado2',
            'label' => 'Recurso 2:',
            'tipo'  => 'combo',
            'array' => array(array(null, null),
                array(1, "Lícito"),
                array(2, "Ilícito")),
            'size'  => 2,
            'valor' => null,
            'col'   => 2,
            'title' => 'Resultado.',
            'linha' => 6),
        array('nome'  => 'dtPublicacao2',
            'label' => 'Data da Publicação:',
            'tipo'  => 'data',
            'size'  => 20,
            'col'   => 3,
            'title' => 'Data da publicação.',
            'linha' => 6),
        array('nome'  => 'pgPublicacao2',
            'label' => 'Página:',
            'tipo'  => 'numero',
            'min'   => 1,
            'max'   => 9999,
            'size'  => 5,
            'col'   => 2,
            'title' => 'A página da Publicação no DOERJ.',
            'linha' => 6),
        array('nome'  => 'resultado3',
            'label' => 'Recurso 3:',
            'tipo'  => 'combo',
            'array' => array(array(null, null),
                array(1, "Lícito"),
                array(2, "Ilícito")),
            'size'  => 2,
            'valor' => null,
            'col'   => 2,
            'title' => 'Resultado.',
            'linha' => 7),
        array('nome'  => 'dtPublicacao3',
            'label' => 'Data da Publicação:',
            'tipo'  => 'data',
            'size'  => 20,
            'col'   => 3,
            'title' => 'Data da publicação.',
            'linha' => 7),
        array('nome'  => 'pgPublicacao3',
            'label' => 'Página:',
            'tipo'  => 'numero',
            'min'   => 1,
            'max'   => 9999,
            'size'  => 5,
            'col'   => 2,
            'title' => 'A página da Publicação no DOERJ.',
            'linha' => 7),
        array('linha'    => 8,
            'fieldset' => 'fecha',
            'col'      => 12,
            'nome'     => 'obs',
            'label'    => 'Observação:',
            'tipo'     => 'textarea',
            'size'     => array(80, 5)),
        array('nome'   => 'idServidor',
            'label'  => 'idServidor:',
            'tipo'   => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size'   => 5,
            'title'  => 'Matrícula',
            'linha'  => 5)));

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Acumulação de Cargo");
    $botaoRel->set_url("../grhRelatorios/servidorAcumulacao.php");
    $botaoRel->set_target("_blank");

    # Normas
    $botao2 = new Button("Regras", "?fase=regras");
    $botao2->set_title("Exibe as regras da acumulação");
    #$botao2->set_url("../grhRelatorios/servidorGratificacao.php");
    $botao2->set_target("_blank");

    $objeto->set_botaoListarExtra(array($botaoRel, $botao2));

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
            $objeto->$fase($id);
            break;

        case "regras" :
            $regra = new Procedimento();
            $regra->exibeProcedimento(24, $idUsuario);
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}