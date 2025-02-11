<?php

/**
 * Histórico de Lotações de um servidor
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
    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Ocorrências";
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
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Ocorrências');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista("SELECT data,
                                     ocorrencia,
                                     idOcorrencia
                                FROM tbocorrencia
                               WHERE idServidor = {$idServidorPesquisado}");

    # select do edita
    $objeto->set_selectEdita("SELECT data,
                                     ocorrencia,
                                     idServidor
                                FROM tbocorrencia
                               WHERE idOcorrencia = {$id}");

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["Data", "Ocorrência"]);
    $objeto->set_width([10, 80]);
    $objeto->set_align(["center", "left"]);
    $objeto->set_funcao(["date_to_php"]);

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbocorrencia');

    # Nome do campo id
    $objeto->set_idCampo('idOcorrencia');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'data',
            'label' => 'Data:',
            'tipo' => 'data',
            'size' => 20,
            'maxLength' => 20,
            'required' => true,
            'autofocus' => true,
            'col' => 3,
            'linha' => 1),
        array('linha' => 2,
            'col' => 12,
            'nome' => 'ocorrencia',
            'label' => 'Ocorrência:',
            'tipo' => 'textarea',
            'size' => array(80, 5)),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'idServidor',
            'linha' => 4)));

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório de Histórico de Lotação");
    $botaoRel->set_url("../grhRelatorios/servidorLotacao.php");
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
            $objeto->gravar($id);
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}