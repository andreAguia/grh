<?php

/**
 * Controle de Vascina do servidor
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

    # Verifica a fase do programa
    $fase = get('fase', 'listar');

    # Verifica de onde veio
    $origem = get_session("origem");

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Controle de Vacina";
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
    $objeto->set_nome('Controle de Vacinas');

    # botão de voltar da lista
    if (vazio($origem)) {
        $objeto->set_voltarLista('servidorMenu.php');
    } else {
        $objeto->set_voltarLista($origem);
    }

    # select da lista
    $objeto->set_selectLista("SELECT IFNULL(YEAR(data),'---') ano,
                                     data,               
                                     tbtipovacina.nome,
                                     tbvacina.obs,
                                     idVacina
                                FROM tbvacina JOIN tbtipovacina USING (idTipoVacina)
                          WHERE idServidor = {$idServidorPesquisado}
                       ORDER BY data desc");

    # select do edita
    $objeto->set_selectEdita("SELECT data,
                                     idTipoVacina,
                                     obs,
                                     idServidor
                                FROM tbvacina
                               WHERE idVacina = {$id}");

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
    $objeto->set_label(["Ano", "Data", "Tipo", "Obs"]);
    $objeto->set_width([10, 10, 15, 55]);
    $objeto->set_align(["center", "center", "center", "left"]);
    $objeto->set_funcao([null, "date_to_php"]);
    $objeto->set_rowspan(0);
    $objeto->set_grupoCorColuna(0);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbvacina');

    # Nome do campo id
    $objeto->set_idCampo('idVacina');

    # Pega os dados da combo cargo
    $tipoVacina = $pessoal->select('SELECT idTipoVacina,
                                           nome
                                      FROM tbtipovacina
                                  ORDER BY nome');
    array_unshift($tipoVacina, array(null, null));

    # Campos para o formulario
    $objeto->set_campos(array(
        array('nome' => 'data',
            'label' => 'Data:',
            'tipo' => 'data',
            'size' => 20,
            'autofocus' => true,
            'col' => 3,
            'title' => 'Data da aplicação da vacina',
            'linha' => 1),
        array('nome' => 'idTipoVacina',
            'label' => 'Tipo:',
            'tipo' => 'combo',
            'required' => true,
            'array' => $tipoVacina,
            'title' => 'Tipo da vacina',
            'col' => 3,
            'size' => 50,
            'linha' => 1),
        array('nome' => 'obs',
            'label' => 'Observações:',
            'tipo' => 'textarea',
            'size' => array(80, 5),
            'col' => 12,
            'linha' => 2),
        array('nome' => 'idServidor',
            'label' => 'idServidor:',
            'tipo' => 'hidden',
            'padrao' => $idServidorPesquisado,
            'size' => 5,
            'title' => 'Matrícula',
            'linha' => 3)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Ressalva Vacina    
    if (Verifica::acesso($idUsuario, [1, 2])) {
        $botaoObs = new Button("Cadastra Isenção", "servidorVacinaRessalva.php");
        $botaoObs->set_title("Insere / edita justificativa para a não vacinação");

        $objeto->set_botaoListarExtra([$botaoObs]);
    }
    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
            # Função para acrescentar a rotina extra

            function exibeObs($idServidor) {
                $vacinaObj = new Vacina();
                $vacinaObj->exibeJustificativa($idServidor);
            }

            # Acrescenta as rotinas
            $objeto->set_rotinaExtraListar("exibeObs");
            $objeto->set_rotinaExtraListarParametro($idServidorPesquisado);

            $objeto->listar();
            break;

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