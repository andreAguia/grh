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
        $atividade = "Cadastro do servidor - Histórico de Alterações do Nome";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Verifica se houve alteração de nome e salva o primeiro nome
    if (empty($pessoal->get_ultimoNome($idPessoa))) {

        # grava o nome atual como inicial
        $campos = ["data", "nome", "motivo", "idPessoa"];
        $valores = [date_to_bd($pessoal->get_dtAdmissao($idServidorPesquisado)), $pessoal->get_nome($idServidorPesquisado), "Nome Inicial", $idPessoa];
        $pessoal->gravar($campos, $valores, null, "tbhistoriconome", "idHistoricoNome");
    }

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Histórico de Alterações do Nome');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista("SELECT data,
                                     nome,
                                     motivo,
                                     idHistoricoNome
                                FROM tbhistoriconome
                          WHERE idPessoa = {$idPessoa}
                       ORDER BY data desc");

    # select do edita
    $objeto->set_selectEdita("SELECT data,
                                     nome,
                                     motivo,
                                     idPessoa,
                                     idHistoricoNome
                                FROM tbhistoriconome
                               WHERE idHistoricoNome = {$id}");

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    # Caminhos
    #$objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(["Data", "Nome", "Motivo"]);
    $objeto->set_width([10, 30, 55]);
    $objeto->set_align(["center", "left", "left"]);
    $objeto->set_funcao(["date_to_php"]);

    # excluir condicional
    $objeto->set_excluirCondicional('?fase=excluir', $pessoal->get_dtAdmissao($idServidorPesquisado), 0, "<>");

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbhistoriconome');

    # Nome do campo id
    $objeto->set_idCampo('idHistoricoNome');

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
            'title' => 'Data da Alteração.',
            'linha' => 1),
        array('nome' => 'nome',
            'label' => 'Novo Nome:',
            'tipo' => 'texto',
            'required' => true,
            'size' => 250,
            'col' => 12,
            'title' => 'O novo nome',
            'linha' => 2),
        array('nome' => 'motivo',
            'label' => 'Motivo:',
            'required' => true,
            'tipo' => 'texto',
            'size' => 250,
            'col' => 12,
            'title' => 'Motivo da mudança de nome.',
            'linha' => 2),
        array('nome' => 'idPessoa',
            'label' => 'idPessoa:',
            'tipo' => 'hidden',
            'padrao' => $idPessoa,
            'size' => 5,
            'title' => 'idServidor',
            'linha' => 4)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    # Procedimentos
    $botaoProcedimentos = new Link("Procedimentos", "?fase=procedimentos");
    $botaoProcedimentos->set_class('button');
    $botaoProcedimentos->set_title('Exibe os procedimentos');
    $botaoProcedimentos->set_target("_blank");
    
    $objeto->set_botaoListarExtra([$botaoProcedimentos]);

    ################################################################

    switch ($fase) {
        case "" :
        case "listar" :
        case "editar" :
            $objeto->$fase($id);
            break;

        case "excluir" :
            $objeto->excluir($id);

            # Atualizando o nome
            $pessoal->gravar(["nome"], $pessoal->get_ultimoNome($idPessoa), $idPessoa, "tbpessoa", "idPessoa");
            break;

        case "gravar" :
            #$objeto->gravar($id, 'servidorNomeExtra.php');
            $objeto->gravar($id);

            # Atualizando o nome
            $pessoal->gravar(["nome"], $pessoal->get_ultimoNome($idPessoa), $idPessoa, "tbpessoa", "idPessoa");
            break;

        ############################################################################

        case "procedimentos" :

            br();
            $procedimento = new Procedimento();
            $procedimento->exibeProcedimento(97);
            break;

        ############################################################################   
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}