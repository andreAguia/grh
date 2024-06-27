<?php

/**
 * Histórico de Folgas
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
    $intra = new Intra();
    $pessoal = new Pessoal();

    # Grava no log a atividade
    $grh = get('grh', false);
    if ($grh) {
        $atividade = "Cadastro do servidor - Cadastro de Folgas Fruídas do TRE";
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
    $objeto->set_nome('Cadastro de Folgas Fruídas  do TRE');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorTre.php?fase=folgas');

    # select da lista
    $objeto->set_selectLista('SELECT data,
                                     ADDDATE(data,dias-1),                                 
                                     dias,
                                     obs,
                                     idFolga
                                FROM tbfolga
                          WHERE idServidor=' . $idServidorPesquisado . '
                       ORDER BY data desc');

    # select do edita
    $objeto->set_selectEdita('SELECT data,
                                     dias,
                                     obs,
                                     idServidor
                                FROM tbfolga
                               WHERE idFolga = ' . $id);

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
    $objeto->set_label(["Data do Início", "Data do Término", "Folgas Fruídas", "Observação"]);
    $objeto->set_width([10, 10, 10, 60]);
    $objeto->set_align(["center", "center", "center", "left"]);
    $objeto->set_funcao(["date_to_php", "date_to_php", null]);

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbfolga');

    # Nome do campo id
    $objeto->set_idCampo('idFolga');
    
    # Campos para o formulario
    $objeto->set_campos(array(array('nome' => 'data',
            'label' => 'Data do Início da Folga:',
            'tipo' => 'data',
            'size' => 20,
            'required' => true,
            'autofocus' => true,
            'title' => 'Data da Fola ou do início da folga.',
            'col' => 3,
            'linha' => 1),
        array('nome' => 'dias',
            'label' => 'Dias:',
            'tipo' => 'numero',
            'size' => 5,
            'col' => 3,
            'required' => true,
            'title' => 'Quantidade de dias folgados.',
            'linha' => 1),
        array('linha' => 2,
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
            'title' => 'Matrícula',
            'linha' => 4)));

    # Relatório
    $imagem = new Imagem(PASTA_FIGURAS . 'print.png', null, 15, 15);
    $botaoRel = new Button();
    $botaoRel->set_imagem($imagem);
    $botaoRel->set_title("Imprimir Relatório");
    $botaoRel->set_onClick("window.open('../grhRelatorios/servidorTreFolga.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");

    #$objeto->set_botaoListarExtra(array($botaoRel));
    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Libera Inclusao, ediçao e exclusao somente para servidores autorizados na regra 6
    if (Verifica::acesso($idUsuario, [1, 6])) {
        $objeto->set_botaoIncluir(true);
        $objeto->set_botaoEditar(true);
        $objeto->set_botaoExcluir(true);
    } else {
        $objeto->set_botaoIncluir(false);
        $objeto->set_botaoEditar(false);
        $objeto->set_botaoExcluir(false);
    }

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            Grh::listaFolgasTre($idServidorPesquisado);
            $objeto->listar();
            break;

        case "editar" :
            Grh::listaFolgasTre($idServidorPesquisado);
        case "excluir" :
            if (Verifica::acesso($idUsuario, [1, 6])) {
                $objeto->$fase($id);
            } else {
                $objeto->listar();
            }
            break;

        case "gravar" :
            if (Verifica::acesso($idUsuario, [1, 6])) {
                $objeto->gravar($id, "servidorTreFolgaExtra.php");
            } else {
                $objeto->listar();
            }
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}