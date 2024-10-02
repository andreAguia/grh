<?php

/**
 * Dados Gerais do servidor
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
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica a fase do programa
    if (Verifica::acesso($idUsuario, 12)) {
        $fase = get('fase', 'editar');
    } else {
        $fase = get('fase', 'ver');
    }

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Horário";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica de onde veio
    $origem = get_session("origem");

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
    $objeto->set_nome('Horário');

    # select do edita
    $selectEdita = "SELECT horarioInicial,
                           horarioFinal,
                           almoco
                      FROM tbservidor
                     WHERE idServidor = {$idServidorPesquisado}";

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    $objeto->set_selectEdita($selectEdita);

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

    # combo de horário
    $arrayHorario = ["07:00", "08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00", "20:00", "21:00", "22:00"];

    # Campos para o formulario
    $campos = array(
        array('linha' => 1,
            'nome' => 'horarioInicial',
            'label' => 'Entrada:',
            'tipo' => 'combo',
            'autofocus' => true,
            'required' => true,
            'size' => 50,
            'array' => $arrayHorario,
            'col' => 2,
            'title' => 'Horário do Servidor.'),
        array('linha' => 1,
            'nome' => 'horarioFinal',
            'label' => 'Saída:',
            'tipo' => 'combo',
            'autofocus' => true,
            'required' => true,
            'size' => 50,
            'array' => $arrayHorario,
            'col' => 2,
            'title' => 'Horário do Servidor.'),
        array('linha' => 1,
            'nome' => 'almoco',
            'label' => 'Tempo para Almoço:',
            'tipo' => 'combo',
            'required' => true,
            'array' => ["2h", "1h"],
            'col' => 2,
            'size' => 50)
    );

    $objeto->set_campos($campos);

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);
    ################################################################

    switch ($fase) {
        case "ver" :
        case "editar" :
        case "gravar" :
            $objeto->$fase($idServidorPesquisado);
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}