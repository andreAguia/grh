<?php

/**
 * Histórico de Lotações de um servidor
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
    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Histórico de lotação";
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
    $objeto->set_nome('Histórico de Lotações');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT data,
                                     lotacao,
                                     motivo,
                                     idHistLot
                                FROM tbhistlot
                          WHERE idServidor=' . $idServidorPesquisado . '
                       ORDER BY data desc');

    # select do edita
    $objeto->set_selectEdita('SELECT data,
                                     lotacao,
                                     motivo,
                                     obs,
                                     idServidor
                                FROM tbhistlot
                               WHERE idHistLot = ' . $id);

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
    $objeto->set_label(["Data", "Lotação", "Motivo"]);
    $objeto->set_width([10, 30, 50]);
    $objeto->set_align(["center", "left", "left"]);
    $objeto->set_funcao(["date_to_php"]);
    $objeto->set_classe([null, "pessoal"]);
    $objeto->set_metodo([null, "get_nomelotacao"]);

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbhistlot');

    # Nome do campo id
    $objeto->set_idCampo('idHistLot');

    # Pega os dados da combo lotacao
    $selectLotacao = 'SELECT idlotacao, 
                             concat(IFnull(tblotacao.UADM,"")," - ",IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) as lotacao,
                             tblotacao.DIR 
                        FROM tblotacao ORDER BY ativo desc, lotacao';

    $result = $pessoal->select($selectLotacao);
    array_unshift($result, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(array('nome' => 'data',
            'label' => 'Data:',
            'tipo' => 'data',
            'size' => 20,
            'maxLength' => 20,
            'required' => true,
            'autofocus' => true,
            'col' => 3,
            'title' => 'Data do início da exibição da notícia.',
            'linha' => 1),
        array('nome' => 'lotacao',
            'label' => 'Lotacão:',
            'tipo' => 'combo',
            'optgroup' => true,
            'required' => true,
            'array' => $result,
            'size' => 20,
            'col' => 9,
            'title' => 'Em qual setor o servidor está lotado',
            'linha' => 1),
        array('nome' => 'motivo',
            'label' => 'Motivo:',
            'tipo' => 'texto',
            'size' => 100,
            'col' => 12,
            'title' => 'Motivo da mudança de lotação.',
            'linha' => 2),
        array('linha' => 2,
            'col' => 12,
            'nome' => 'obs',
            'label' => 'Observação:',
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
    
    $mensagem = "Atenção!<br/>Toda alteração de lotação de servidor deverá ser informada aos administradores do SEI na Uenf através do e-mail sei@uenf.br";

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            $objeto->set_rotinaExtraListar("callout");
            $objeto->set_rotinaExtraListarParametro($mensagem);
            $objeto->listar();
            break;

        case "editar" :            
            $objeto->set_rotinaExtraEditar("callout");
            $objeto->set_rotinaExtraEditarParametro($mensagem);
            $objeto->editar($id);
            break;
            
        case "excluir" :            
            $objeto->excluir($id);            
            break;

        case "gravar" :
            $objeto->gravar($id, 'servidorLotacaoExtra.php');
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}