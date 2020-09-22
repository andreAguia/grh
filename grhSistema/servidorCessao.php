<?php

/**
 * Cadastro de Cassão de Servidor Estatutário
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("_config.php");

# Zera a sessão da frequencia de cedido
set_session('idHistCessao');

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Conecta ao Banco de Dados
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Histórico de cessão";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

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
    $objeto->set_rotinaExtra("get_DadosServidorCessao");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);

    # Verifica se tem mais de uma cessão vigente
    $cessao = new Cessao();
    if ($cessao->getNumCessaoVigente($idServidorPesquisado) > 1) {
        $objeto->set_rotinaExtraListar("callout");
        $objeto->set_rotinaExtraListarParametro("<span class='label alert' title='Problema Encontrado !!'>Problema</span>&nbsp;&nbsp;&nbsp;&nbsp;Servidor com mais de 1 cessão vigente!");
    }


    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Cadastro de Cessão');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista("SELECT idHistCessao,
                                     dtInicio,
                                     dtFim,
                                     orgao,
                                     processo,
                                     obs,
                                     idHistCessao,
                                     idHistCessao,
                                     idHistCessao
                                FROM tbhistcessao
                          WHERE idServidor = {$idServidorPesquisado}
                       ORDER BY dtInicio desc");

    # select do edita
    $objeto->set_selectEdita("SELECT dtInicio,
                                     dtFim,
                                     processo,                                 
                                     dtPublicacao,
                                     orgao,
                                     obs,
                                     idServidor
                                FROM tbhistcessao
                               WHERE idHistCessao = {$id}");

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["Status", "Data Inicial", "Data Término", "Órgão Cessionário", "Processo", "Obs", "Frequência"]);
    $objeto->set_width([8, 8, 8, 10, 15, 38, 5]);
    $objeto->set_align(["center", "center", "center", "center",  "center", "left"]);
    $objeto->set_funcao([null, "date_to_php", "date_to_php"]);
    $objeto->set_classe(["Cessao",null,null,null,null,null,"Cessao"]);
    $objeto->set_metodo(["getStatus",null,null,null,null,null,"getNumLancamentosFrequencia"]);

    $objeto->set_formatacaoCondicional(array(
        array('coluna' => 0,
            'valor' => "Vigente",
            'operador' => '=',
            'id' => 'cessaoVigente'),
        array('coluna' => 0,
            'valor' => "Terminada",
            'operador' => '=',
            'id' => 'cessaoTerminada')
    ));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbhistcessao');

    # Nome do campo id
    $objeto->set_idCampo('idHistCessao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(array('nome' => 'dtInicio',
            'label' => 'Data Inicial:',
            'tipo' => 'data',
            'size' => 20,
            'required' => true,
            'autofocus' => true,
            'title' => 'Data do início da cessão.',
            'col' => 3,
            'linha' => 1),
        array('nome' => 'dtFim',
            'label' => 'Data Final:',
            'tipo' => 'data',
            'size' => 20,
            'title' => 'Data do término da cessão.',
            'col' => 3,
            'linha' => 1),
        array('nome' => 'processo',
            'label' => 'Número do Processo de Cessão:',
            'tipo' => 'texto',
            'size' => 50,
            'col' => 3,
            'title' => 'O Número do processo de cessao',
            'linha' => 1),
        array('nome' => 'dtPublicacao',
            'label' => 'Data da Pub. no DOERJ:',
            'tipo' => 'data',
            'size' => 20,
            'col' => 3,
            'title' => 'Data da Publicação no DOERJ.',
            'linha' => 1),
        array('nome' => 'orgao',
            'label' => 'Órgão Cessionário:',
            'tipo' => 'texto',
            'required' => true,
            'size' => 100,
            'col' => 12,
            'title' => 'O órgão cessionário',
            'linha' => 2),
        array('linha' => 3,
            'nome' => 'obs',
            'label' => 'Observação:',
            'tipo' => 'textarea',
            'col' => 12,
            'size' => array(80, 5)),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'idServidor',
            'linha' => 3)));

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Cessão");
    $botaoRel->set_url("../grhRelatorios/servidorCessao.php");
    $botaoRel->set_target("_blank");

    $objeto->set_botaoListarExtra(array($botaoRel));

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
            $objeto->gravar($id, 'servidorCessaoExtra.php');
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}