<?php

/**
 * Informa se retira o servidor a cobrança do Sispatri
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
    $fase = get('fase', 'editar');

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $intra = new Intra();
        $atividade = "Cadastro do servidor - Retira Cobrança do Sispatri";
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
    $objeto->set_nome('Retira Nome da Lista do Sispatri?');

    # select do edita
    $objeto->set_selectEdita("SELECT retiraSispatri
                                FROM tbservidor
                               WHERE idServidor = {$idServidorPesquisado}");

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('servidorMenu.php');
    $objeto->set_voltarForm('servidorMenu.php');

    # retira o botão incluir
    $objeto->set_botaoIncluir(false);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbservidor');

    # Nome do campo id
    $objeto->set_idCampo('idServidor');

    # Campos para o formulario
    $objeto->set_campos(array(
        array('linha' => 1,
            'nome' => 'retiraSispatri',
            'autofocus' => true,
            'label' => 'Retira Nome da Lista do Sispatri',
            'size' => 10,
            'col' => 3,
            'tipo' => 'simnao3')));
    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Mensagem
    $sispatri = new Sispatri();
    if($sispatri->entregouSispatri($idServidorPesquisado)){
        $mensagem = "Servidor já Entregou Sispatri";
    }else{
        $mensagem = "Servidor ainda NÃO Entregou Sispatri";
    }
    $objeto->set_rotinaExtraEditar("callout");
    $objeto->set_rotinaExtraEditarParametro($mensagem);

    ################################################################
    switch ($fase) {
        case "editar" :
        case "gravar" :
            $objeto->$fase($idServidorPesquisado);
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}
