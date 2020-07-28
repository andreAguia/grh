<?php

/**
 * Cadastro de Elogios e Advertências do Servidor
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
    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    $intra = new Intra();

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Penalidades";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

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
    $objeto->set_nome('Cadastro de Penalidades');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT data,
                                     penalidade,
                                     processo,
                                     dtPublicacao,
                                     pgPublicacao,
                                     descricao,                           
                                     idPenalidade
                                FROM tbpenalidade JOIN tbtipopenalidade USING (idTipoPenalidade)
                          WHERE idServidor=' . $idServidorPesquisado . '
                       ORDER BY data desc');

    # select do edita
    $objeto->set_selectEdita('SELECT data,
                                     idTipoPenalidade,
                                     processo,
                                     dtPublicacao,
                                     pgPublicacao,
                                     descricao,
                                     idServidor
                                FROM tbpenalidade
                               WHERE idPenalidade = ' . $id);

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
    $objeto->set_label(array("Data", "Tipo", "Processo","Publicação","Pag","Descrição"));
    $objeto->set_width(array(10, 10, 15,15,5,35));
    $objeto->set_align(array("center", "center","center","center","center", "left"));
    $objeto->set_funcao(array("date_to_php",null,null,"date_to_php"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbpenalidade');

    # Nome do campo id
    $objeto->set_idCampo('idPenalidade');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);
    
    # Pega os dados da combo tipo de penalidade
    $parentesco = new Pessoal();
    $result = $parentesco->select('SELECT idTipoPenalidade, 
                                          penalidade
                                     FROM tbtipopenalidade
                                 ORDER BY penalidade');
    array_push($result, array(null, null)); # Adiciona o valor de nulo

    # Campos para o formulario
    $objeto->set_campos(array(array('nome' => 'data',
            'label' => 'Data:',
            'tipo' => 'data',
            'size' => 20,
            'maxLength' => 20,
            'required' => true,
            'autofocus' => true,
            'col' => 3,
            'title' => 'Data da Penalidade.',
            'linha' => 1),
        array('nome' => 'idTipoPenalidade',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'array' => $result,
            'required' => true,            
            'size' => 20,
            'title' => 'Qual o tipo de penalidade',
            'col' => 4,
            'linha' => 1),
        array('nome' => 'processo',
            'label' => 'Processo:',
            'tipo' => 'texto',
            'size' => 30,
            'col' => 3,
            'title' => 'Número do Processo',
            'linha' => 2),
        array('nome' => 'dtPublicacao',
            'label' => 'Data da Publicação:',
            'tipo' => 'data',
            'size' => 10,
            'col' => 3,
            'title' => 'A Data da Publicação.',
            'linha' => 2),
        array('nome' => 'pgPublicacao',
            'label' => 'Página:',
            'tipo' => 'texto',
            'size' => 5,
            'col' => 2,
            'title' => 'A página da Publicação no DOERJ.',
            'linha' => 2),
        array('nome' => 'descricao',
            'label' => 'Descrição:',
            'tipo' => 'textarea',
            'size' => array(80, 5),
            'col' => 12,
            'required' => true,
            'title' => 'Descrição do Elogio ou Advertência.',
            'linha' => 3),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 4)));

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
            $objeto->gravar($id, 'servidorPenalidadeExtra.php');
            break;
    }

    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}